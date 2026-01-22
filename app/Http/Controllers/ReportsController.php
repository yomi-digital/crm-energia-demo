<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\PaperworkTrait;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    use PaperworkTrait;

    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $reports = \App\Models\Report::with('user');

        if ($request->get('id')) {
            $reports = $reports->where('id', $request->get('id'));
        }

        // Filtro per area
        $area = $request->get('area', null);
        if ($area) {
            // Trova tutti gli utenti con quella area
            $usersInArea = \App\Models\User::where('area', $area)->pluck('id');
            $reports = $reports->whereIn('user_id', $usersInArea);
        }

        if ($request->filled('user_id')) {
            $reports = $reports->where('user_id', $request->get('user_id'));
        }

        if ($request->filled('status')) {
            $reports = $reports->where('status', $request->get('status'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $reports = $reports->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->get('sortBy')) {
            $reports = $reports->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $reports = $reports->orderBy('created_at', 'desc');
        }

        $reports = $reports->paginate($perPage);

        return response()->json([
            'entries' => $reports->getCollection(),
            'totalPages' => $reports->lastPage(),
            'totalEntries' => $reports->total(),
            'page' => $reports->currentPage(),
        ]);
    }

    public function delete(Request $request, $id)
    {
        $report = \App\Models\Report::find($id);
        
        if (!$report) {
            return response()->json([
                'error' => 'Report non trovato',
            ], 404);
        }
        
        // Impedisce l'eliminazione se lo status è 3 (Inviato)
        if ($report->status == 3) {
            return response()->json([
                'error' => 'Non è possibile eliminare un report con stato Inviato',
            ], 403);
        }
        
        $report->delete();
        return response()->json([
            'message' => 'Report eliminato con successo',
        ]);
    }

    public function entries(Request $request, $id)
    {
        $report = \App\Models\Report::find($id);
        if (! $report) {
            return response()->json([
                'error' => 'Report non trovato',
            ], 404);
        }

        $perPage = $request->get('itemsPerPage', 10);

        $entries = $report->entries();

        if ($request->get('sortBy')) {
            $entries = $entries->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $entries = $entries->orderBy('created_at', 'desc');
        }

        if ($request->has('export')) {
            $allEntries = $entries->get();
            $csvPath = $this->transformEntriesToCSV($allEntries, $report);

            // Transform csv to excel
            $data = array_map('str_getcsv', file($csvPath));

            return Excel::download(new class($data) implements FromCollection {
                private $data;
    
                public function __construct($data)
                {
                    $this->data = $data;
                }
    
                public function collection()
                {
                    return collect($this->data);
                }
            }, $report->name . '.xlsx');
            
            return response()->download($csvPath, $report->name . '.csv');
        }

        $entries = $entries->paginate($perPage);

        // Get total sum of payout_confirmed
        // Le pratiche con stato "STORNO" sono sottrattive
        $totalPayoutConfirmed = $entries->getCollection()->sum(function ($entry) {
            $payout = $entry->payout_confirmed ?? 0;
            // Se lo status è STORNO, sottrai invece di sommare (usa valore assoluto)
            if ($entry->status === 'STORNO') {
                return -abs($payout);
            }
            return $payout;
        });

        return response()->json([
            'report' => $report,
            'entries' => $entries->getCollection(),
            'totalPages' => $entries->lastPage(),
            'totalEntries' => $entries->total(),
            'page' => $entries->currentPage(),
            'totalPayoutConfirmed' => $totalPayoutConfirmed
        ]);
    }

    public function admin(Request $request)
    {
        if (! $request->has('from') || ! $request->has('to')) {
            return response()->json([
                'error' => 'Selezionare un periodo',
            ], 400);
        }

        $paperworks = \App\Models\Paperwork::with(['user', 'product', 'product.brand', 'mandate', 'customer'])
            ->whereBetween('partner_outcome_at', [
                $request->get('from') . ' 00:00:00',
                // '2023-01-01' . ' 00:00:00',
                $request->get('to') . ' 23:59:59'
            ])->whereIn('partner_outcome', ['ATTIVO', 'OK PAGABILE', 'STORNO']);

        $userId = $request->get('user_id', null);
        $user = null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            $agents = \App\Models\UserRelationship::where('user_id', $userId)->with('user')->get();
            $userIds = $agents->pluck('related_id');
            $userIds[] = $user->id;
            $paperworks = $paperworks->whereIn('user_id', $userIds);
        }

        if ($request->filled('brand_id')) {
            $paperworks = $paperworks->whereHas('product', function ($query) use ($request) {
                $query->where('brand_id', $request->get('brand_id'));
            });
        }

        if ($request->filled('product_id')) {
            $paperworks = $paperworks->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('agency_id')) {
            $paperworks = $paperworks->where('mandate_id', $request->get('agency_id'));
        }

        if ($request->filled('status')) {
            $paperworks = $paperworks->where('partner_outcome', $request->get('status'));
        }

        if ($request->filled('category')) {
            $paperworks = $paperworks->where('category', $request->get('category'));
        }

        $paperworks = $paperworks->orderBy('created_at', 'desc');

        if ($request->has('save')) {
            if (! $userId) {
                return response()->json([
                    'error' => 'Selezionare un account',
                ], 400);
            }
            $reportName = 'Report Amministrativo ' . implode(' ', array_filter([$user->name, $user->last_name]));
            if ($request->get('from')) {
                $reportName .= ' ' . $request->get('from');
                if ($request->get('to')) {
                    $reportName .= ' - ' . $request->get('to');
                }
            }
            $report = \App\Models\Report::create([
                'name' => $reportName,
                'user_id' => $userId,
            ]);

            $allPaperworks = $paperworks->get();
            foreach ($allPaperworks as $paperwork) {
                $transformedPaperwork = $this->transformPaperworkAdmin($paperwork, $user, false);
                $entry = new \App\Models\ReportEntry();
                $entry->fill($transformedPaperwork);
                $entry->payout_confirmed = $entry->payout;
                $entry->report_id = $report->id;
                $entry->save();
            }

            return response()->json([
                'message' => 'Report salvato con successo',
                'report_id' => $report->id,
            ]);
        }

        if ($request->has('export')) {
            $allPaperworks = $paperworks->get();
            $csvPath = $this->transformPaperworksAdminToCSV($allPaperworks, $user);

            // Transform csv to excel
            $data = array_map('str_getcsv', file($csvPath));

            return Excel::download(new class($data) implements FromCollection {
                private $data;
    
                public function __construct($data)
                {
                    $this->data = $data;
                }
    
                public function collection()
                {
                    return collect($this->data);
                }
            }, 'report_amministrativo.xlsx');
            
            return response()->download($csvPath, 'report_amministrativo.csv');
        }

        $perPage = $request->get('itemsPerPage', 500);
        $paperworks = $paperworks->paginate($perPage);

        $paperworks->getCollection()->transform(function ($paperwork) use ($user) {
            return $this->transformPaperworkAdmin($paperwork, $user);
        });

        return response()->json([
            'entries' => $paperworks->getCollection(),
            'totalPages' => $paperworks->lastPage(),
            'totalEntries' => $paperworks->total(),
            'page' => $paperworks->currentPage()
        ]);
    }

    public function production(Request $request)
    {
        if (! $request->has('from') || ! $request->has('to')) {
            return response()->json([
                'error' => 'Selezionare un periodo',
            ], 400);
        }

        $paperworks = \App\Models\Paperwork::with(['user', 'product', 'product.brand', 'mandate', 'customer'])
            ->whereBetween('partner_sent_at', [
                $request->get('from') . ' 00:00:00',
                $request->get('to') . ' 23:59:59'
            ]);

        // Filtro per area
        $area = $request->get('area', null);
        if ($area) {
            // Trova tutti gli utenti con quella area
            $usersInArea = \App\Models\User::where('area', $area)->pluck('id');
            $paperworks = $paperworks->whereIn('user_id', $usersInArea);
        } else {
            // Check if role agent, then should only take paperworks for the logged in user
            if ($request->user()->hasRole('agente')) {
                $paperworks = $paperworks->where('user_id', $request->user()->id);
            } elseif ($request->user()->hasRole('struttura')) {
                $relationships = \App\Models\UserRelationship::where('user_id', $request->user()->id)->with('user')->get();
                $ids = $relationships->pluck('related_id')->merge([$request->user()->id]);
                $paperworks = $paperworks->whereIn('user_id', $ids);
            }
        }

        $user = null;

        if ($request->filled('brand_id')) {
            // Split brand IDs by "-" delimiter
            $brandIds = explode('-', $request->input('brand_id'));
            $brandIds = array_filter($brandIds); // Remove empty values
            
            if (!empty($brandIds)) {
                $paperworks = $paperworks->whereHas('product', function ($query) use ($brandIds) {
                    $query->whereIn('brand_id', $brandIds);
                });
            }
        }

        if ($request->filled('product_id')) {
            // Split product IDs by "-" delimiter
            $productIds = explode('-', $request->input('product_id'));
            $productIds = array_filter($productIds); // Remove empty values
            
            if (!empty($productIds)) {
                $paperworks = $paperworks->whereIn('product_id', $productIds);
            }
        }

        if ($request->filled('agent_id')) {
            $paperworks = $paperworks->where('user_id', $request->get('agent_id'));
        }

        if ($request->filled('agency_id')) {
            $paperworks = $paperworks->where('mandate_id', $request->get('agency_id'));
        }

        if ($request->filled('mandate_id')) {
            $paperworks = $paperworks->where('mandate_id', $request->get('mandate_id'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            $paperworks = $paperworks->where(function ($query) use ($status) {
                $query->where('partner_outcome', $status)
                      ->orWhere(function ($q) use ($status) {
                          $q->whereNull('partner_outcome')
                            ->where('order_status', $status);
                      });
            });
        }

        if ($request->filled('category')) {
            $paperworks = $paperworks->where('category', $request->get('category'));
        }

        if ($request->filled('has_appointment')) {
            $hasAppointment = $request->get('has_appointment');
            if ($hasAppointment === '1' || $hasAppointment === 'SI') {
                $paperworks = $paperworks->whereNotNull('appointment_id');
            } elseif ($hasAppointment === '0' || $hasAppointment === 'NO') {
                $paperworks = $paperworks->whereNull('appointment_id');
            }
        }

        $paperworks = $paperworks->orderBy('partner_outcome_at', 'desc');

        if ($request->has('export')) {
            $allPaperworks = $paperworks->get();
            $csvPath = $this->transformPaperworksToCSV($allPaperworks, $user);

            // Transform csv to excel
            $data = array_map('str_getcsv', file($csvPath));

            return Excel::download(new class($data) implements FromCollection {
                private $data;
    
                public function __construct($data)
                {
                    $this->data = $data;
                }
    
                public function collection()
                {
                    return collect($this->data);
                }
            }, 'report_produzione.xlsx');
            
            return response()->download($csvPath, 'report_produzione.csv');
        }

        $perPage = $request->get('itemsPerPage', 500);
        $paperworks = $paperworks->paginate($perPage);

        $paperworks->getCollection()->transform(function ($paperwork) use ($user) {
            return $this->transformPaperwork($paperwork, $user);
        });

        return response()->json([
            'entries' => $paperworks->getCollection(),
            'totalPages' => $paperworks->lastPage(),
            'totalEntries' => $paperworks->total(),
            'page' => $paperworks->currentPage()
        ]);
    }

    public function appointments(Request $request)
    {
        dd('stop');
    }

    private function transformPaperworkAdmin($paperwork, $user, $formatDates = true)
    {
        if ($user) {
            $parent = $user;
        } else {
            $parent = \App\Models\UserRelationship::where('related_id', $paperwork->user_id)->first();
            if ($parent) {
                $parent = \App\Models\User::find($parent->user_id);
            }
        }

        //Fix del nome e cognome del cliente
        $customerName = "N/A";
        if ($paperwork->customer) {
            if (!empty($paperwork->customer->business_name)) {
                $customerName = $paperwork->customer->business_name;
            } else if (!empty($paperwork->customer->name) && !empty($paperwork->customer->last_name)) {
                $customerName = implode(' ', array_filter([$paperwork->customer->name, $paperwork->customer->last_name]));
            } else if (!empty($paperwork->customer->name)) {
                $customerName = $paperwork->customer->name;
            } else if (!empty($paperwork->customer->last_name)) {
                $customerName = $paperwork->customer->last_name;
            }
        }

        // Formatta le date solo se richiesto (per visualizzazione/esportazione, non per salvataggio)
        $insertedAt = $paperwork->partner_sent_at;
        $activatedAt = $paperwork->partner_outcome_at;
        
        if ($formatDates) {
            $insertedAt = $insertedAt ? \Carbon\Carbon::parse($insertedAt)->format(config('app.date_format')) : null;
            $activatedAt = $activatedAt ? \Carbon\Carbon::parse($activatedAt)->format(config('app.date_format')) : null;
        }

        // Determina il nome del cliente
        return [
            'parent_id' => $parent ? $parent->id : null,
            'parent' => $parent ? implode(' ', array_filter([$parent->name, $parent->last_name])) : null,
            'agent_id' => $paperwork->user_id,
            'agent' => $paperwork->user ? implode(' ', array_filter([$paperwork->user->name, $paperwork->user->last_name])) : 'N/A',
            'agency_id' => $paperwork->mandate_id,
            'agency' => $paperwork->mandate ? $paperwork->mandate->name : null,
            'customer_id' => $paperwork->customer_id,
            'customer' => $customerName,
            'tax_id_code' => $paperwork->customer ? ($paperwork->customer->tax_id_code ?? 'N/A') : 'N/A',
            'vat_number' => $paperwork->customer ? ($paperwork->customer->vat_number ?? 'N/A') : 'N/A',
            'brand_id' => $paperwork->product && $paperwork->product->brand ? $paperwork->product->brand_id : null,
            'brand' => $paperwork->product && $paperwork->product->brand ? $paperwork->product->brand->name : 'N/A',
            'product_id' => $paperwork->product_id,
            'product' => $paperwork->product ? $paperwork->product->name : 'N/A',
            'order_code' => $paperwork->order_code,
            'account_pod_pdr' => $paperwork->account_pod_pdr,
            'paperwork_id' => $paperwork->id,
            'inserted_at' => $insertedAt,
            'activated_at' => $activatedAt,
            'status' => $paperwork->partner_outcome ?: $paperwork->order_status,
            'order_status' => $paperwork->order_status,
            'payout' => $this->calculatePaperworkPayout($paperwork, $parent),
        ];
    }

    private function transformPaperwork($paperwork, $user)
    {
        if ($user) {
            $parent = $user;
        } else {
            $parent = \App\Models\UserRelationship::where('related_id', $paperwork->user_id)->first();
            if ($parent) {
                $parent = \App\Models\User::find($parent->user_id);
            }
        }

        return [
            'parent_id' => $parent ? $parent->id : null,
            'parent' => $parent ? implode(' ', array_filter([$parent->name, $parent->last_name])) : null,
            'agent_id' => $paperwork->user_id,
            'agent' => $paperwork->user ? implode(' ', array_filter([$paperwork->user->name, $paperwork->user->last_name])) : 'N/A',
            'agency_id' => $paperwork->mandate_id,
            'agency' => $paperwork->mandate ? $paperwork->mandate->name : null,
            'mandate_id' => $paperwork->mandate_id,
            'mandate' => $paperwork->mandate ? $paperwork->mandate->name : null,
            'customer_id' => $paperwork->customer_id,
            'customer' => $paperwork->customer ? implode(' ', array_filter([$paperwork->customer->name, $paperwork->customer->business_name])) : null,
            'brand_id' => $paperwork->product && $paperwork->product->brand ? $paperwork->product->brand_id : null,
            'brand' => $paperwork->product && $paperwork->product->brand ? $paperwork->product->brand->name : 'N/A',
            'product_id' => $paperwork->product_id,
            'product' => $paperwork->product ? $paperwork->product->name : 'N/A',
            'order_code' => $paperwork->order_code,
            'paperwork_id' => $paperwork->id,
            'inserted_at' => $paperwork->partner_sent_at ? \Carbon\Carbon::parse($paperwork->partner_sent_at)->format(config('app.date_format')) : null,
            'status' => $paperwork->partner_outcome ?: $paperwork->order_status,
            'order_status' => $paperwork->order_status,
            'category' => $paperwork->category,
            'has_appointment' => $paperwork->appointment_id ? 'SI' : 'NO',
            'notes' => $paperwork->notes,
        ];
    }

    /**
     * Pulisce le note da caratteri terminatori e spazi problematici per l'export CSV/Excel
     */
    private function sanitizeNotesForExport($notes)
    {
        if (empty($notes)) {
            return '';
        }

        // Rimuovi caratteri terminatori di riga (\n, \r, \r\n)
        $notes = str_replace(["\r\n", "\n", "\r"], ' ', $notes);
        
        // Rimuovi tab
        $notes = str_replace("\t", ' ', $notes);
        
        // Rimuovi spazi multipli e sostituiscili con uno spazio singolo
        $notes = preg_replace('/\s+/', ' ', $notes);
        
        // Rimuovi spazi all'inizio e alla fine
        $notes = trim($notes);
        
        return $notes;
    }

    private function transformPaperworksToCSV($paperworks, $user)
    {
        $headers = [
            'Struttura',
            'Agente',
            'Agenzia',
            'Brand',
            'Prodotto',
            'Pratica',
            'Insertita',
            'Stato',
            'Note',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($paperworks as $paperwork) {
            $data = $this->transformPaperwork($paperwork, $user);
            fputcsv($fp, [
                $data['parent'],
                $data['agent'],
                $data['agency'],
                $data['brand'],
                $data['product'],
                $data['order_code'],
                $data['inserted_at'],
                $data['status'],
                $this->sanitizeNotesForExport($data['notes'] ?? ''),
            ]);
        }

        fclose($fp);

        return $csvPath;
    }

    private function transformPaperworksAdminToCSV($paperworks, $user)
    {
        $headers = [
            'Pratica',
            'Account POD/PDR',
            'Struttura',
            'Collaboratore',
            'Cliente',
            'CF',
            'Partita IVA',
            'Brand',
            'Prodotto',
            'Stato',
            'Data Inserimento',
            'Data Esito Partner',
            'Compenso €',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        $totalPayout = 0;
        foreach ($paperworks as $paperwork) {
            $data = $this->transformPaperworkAdmin($paperwork, $user);
            $payout = $data['payout'] ?? 0;
            $totalPayout += $payout;
            
            fputcsv($fp, [
                $data['order_code'],
                $data['account_pod_pdr'],
                $data['parent'],
                $data['agent'],
                $data['customer'],
                $data['tax_id_code'],
                $data['vat_number'],
                $data['brand'],
                $data['product'],
                $data['status'],
                $data['inserted_at'] ?? '',
                $data['activated_at'] ?? '',
                $payout,
            ]);
        }

        // Aggiungi riga vuota e riga totale
        fputcsv($fp, []); // Riga vuota
        fputcsv($fp, [
            '', // Pratica
            '', // Account POD/PDR
            '', // Struttura
            '', // Collaboratore
            '', // Cliente
            '', // CF
            '', // Partita IVA
            '', // Brand
            '', // Prodotto
            'TOTALE', // Stato
            '', // Data Inserimento
            '', // Data Esito Partner
            $totalPayout, // Compenso €
        ]);

        fclose($fp);

        return $csvPath;
    }

    private function transformEntriesToCSV($entries, $report)
    {
        $headers = [
            'Agente',
            'Brand',
            'Prodotto',
            'Pratica',
            'Data Inserimento',
            'Data Attivazione',
            'Stato',
            'Compenso €',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        $totalPayout = 0;
        foreach ($entries as $entry) {
            $payout = $entry->payout_confirmed ?? 0;
            $totalPayout += $payout;
            
            fputcsv($fp, [
                $entry->agent,
                $entry->brand,
                $entry->product,
                $entry->order_code,
                $entry->inserted_at,
                $entry->activated_at,
                $entry->status,
                $payout,
            ]);
        }

        // Aggiungi riga vuota e riga totale
        fputcsv($fp, []); // Riga vuota
        fputcsv($fp, [
            '', // Agente
            '', // Brand
            '', // Prodotto
            '', // Pratica
            '', // Data Inserimento
            '', // Data Attivazione
            'TOTALE', // Stato
            $totalPayout, // Compenso €
        ]);

        fclose($fp);

        return $csvPath;
    }

    public function updatePayoutConfirmed(Request $request, $id, $entryId)
    {
        $entry = \App\Models\ReportEntry::findOrFail($entryId);
        $entry->payout_confirmed = $request->get('payout_confirmed');
        $entry->save();

        return response()->json([
            'message' => 'Compenso confermato aggiornato con successo',
        ]);
    }

    public function deleteEntry(Request $request, $id, $entryId)
    {
        $entry = \App\Models\ReportEntry::findOrFail($entryId);
        $entry->delete();

        return response()->json([
            'message' => 'Riga eliminata con successo',
        ]);
    }

    public function update(Request $request, $id)
    {
        $report = \App\Models\Report::findOrFail($id);
        $oldStatus = $report->status;
        $report->name = $request->get('name');
        $report->status = $request->get('status');
        $report->save();

        // Se lo status è stato cambiato a "Confermato" (2), genera PDF e invia email
        if ($oldStatus != 2 && $request->get('status') == 2) {
            // Carica tutte le entries del report con i dati necessari
            $entries = $report->entries()->get();
            
            // Prepara le entries con i dati del cliente e dell'account
            $preparedEntries = $this->prepareEntriesForPdf($entries);
            
            // Calcola il totale compenso confermato
            // Le pratiche con stato "STORNO" sono sottrattive
            $totalCompenso = $entries->sum(function ($entry) {
                $payout = $entry->payout_confirmed ?? 0;
                // Se lo status è STORNO, sottrai invece di sommare (usa valore assoluto)
                if ($entry->status === 'STORNO') {
                    return -abs($payout);
                }
                return $payout;
            });
            
            // Estrai il periodo di riferimento dal nome del report
            $periodoRiferimento = $this->extractPeriodoFromReportName($report->name);
            
            // Genera il PDF
            $pdf = Pdf::loadView('pdf.invito-fatturare', [
                'report' => $report,
                'entries' => $preparedEntries,
                'totalCompenso' => $totalCompenso,
                'periodoRiferimento' => $periodoRiferimento,
            ]);
            
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            
            // Genera il contenuto PDF
            $pdfContent = $pdf->output();
            
            // Genera percorso file: reports/invito-fatturare/report-[id_report].pdf
            $filename = 'reports/invito-fatturare/report-' . $report->id . '.pdf';
            
            // Salva su DigitalOcean Spaces come file privato
            Storage::disk('do')->put($filename, $pdfContent, 'private');
            
            // Aggiorna il report con il percorso del PDF
            $report->pdf_uri = $filename;
            $report->save();
            
            // Invia l'email all'utente associato al report con PDF allegato
            if ($report->user && $report->user->email) {
                \Mail::to($report->user->email)->send(
                    new \App\Mail\InvitoFatturareEmail($report, $preparedEntries, $totalCompenso, $periodoRiferimento, $filename)
                );
            }
        }

        return response()->json([
            'message' => 'Nome aggiornato con successo',
        ]);
    }

    /**
     * Estrae il periodo di riferimento dal nome del report
     * Esempio: "Report Amministrativo Mario Rossi 01/08/2025 - 30/09/2025" -> "01/08/2025 - 30/09/2025"
     */
    private function extractPeriodoFromReportName($name)
    {
        // Cerca pattern di date nel formato DD/MM/YYYY o YYYY-MM-DD
        // Pattern per DD/MM/YYYY - DD/MM/YYYY
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}\/\d{2}\/\d{4})/', $name, $matches)) {
            return $matches[1] . ' - ' . $matches[2];
        }
        
        // Pattern per YYYY-MM-DD - YYYY-MM-DD
        if (preg_match('/(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})/', $name, $matches)) {
            // Converti in formato DD/MM/YYYY
            $from = \Carbon\Carbon::parse($matches[1])->format('d/m/Y');
            $to = \Carbon\Carbon::parse($matches[2])->format('d/m/Y');
            return $from . ' - ' . $to;
        }
        
        // Pattern per una singola data YYYY-MM-DD
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $name, $matches)) {
            return \Carbon\Carbon::parse($matches[1])->format('d/m/Y');
        }
        
        return null;
    }

    /**
     * Prepara le entries con i dati del cliente e dell'account per il PDF
     */
    private function prepareEntriesForPdf($entries)
    {
        $preparedEntries = [];
        
        // Ottieni tutti gli ID dei paperwork per evitare query N+1
        $paperworkIds = $entries->pluck('paperwork_id')->filter()->unique()->toArray();
        
        // Carica tutti i paperwork con i loro clienti in una sola query
        $paperworks = \App\Models\Paperwork::with('customer')
            ->whereIn('id', $paperworkIds)
            ->get()
            ->keyBy('id');
        
        foreach ($entries as $entry) {
            $preparedEntry = [
                'id' => $entry->id,
                'customer' => 'N/A',
                'account' => 'N/A',
                'activated_at' => $entry->activated_at ?? 'N/A',
                'product' => $entry->product ?? 'N/A',
                'payout_confirmed' => $entry->payout_confirmed ?? 0,
                'status' => $entry->status ?? null,
            ];
            
            if ($entry->paperwork_id && isset($paperworks[$entry->paperwork_id])) {
                $paperwork = $paperworks[$entry->paperwork_id];
                
                // Cliente
                if ($paperwork->customer) {
                    if (!empty($paperwork->customer->business_name)) {
                        $preparedEntry['customer'] = $paperwork->customer->business_name;
                    } else if (!empty($paperwork->customer->name) && !empty($paperwork->customer->last_name)) {
                        $preparedEntry['customer'] = implode(' ', array_filter([$paperwork->customer->name, $paperwork->customer->last_name]));
                    } else if (!empty($paperwork->customer->name)) {
                        $preparedEntry['customer'] = $paperwork->customer->name;
                    } else if (!empty($paperwork->customer->last_name)) {
                        $preparedEntry['customer'] = $paperwork->customer->last_name;
                    }
                }
                
                // Account POD/PDR
                if ($paperwork->account_pod_pdr) {
                    $preparedEntry['account'] = $paperwork->account_pod_pdr;
                }
            }
            
            $preparedEntries[] = (object) $preparedEntry;
        }
        
        return $preparedEntries;
    }

    public function addEntry(Request $request, $id)
    {
        // Must select one entry of the report to clone
        $report = \App\Models\Report::findOrFail($id);

        $entry = $report->entries()->first();

        $newEntry = new \App\Models\ReportEntry();
        $newEntry->report_id = $report->id;
        $newEntry->agent_id = $entry->agent_id;
        $newEntry->agent = $entry->agent;
        $newEntry->brand = $request->get('description');
        $newEntry->product = $request->get('description');
        $newEntry->payout = $request->get('payout');
        $newEntry->payout_confirmed = $request->get('payout');

        $newEntry->save();

        return response()->json([
            'message' => 'Riga aggiunta con successo',
        ]);
    }
}
