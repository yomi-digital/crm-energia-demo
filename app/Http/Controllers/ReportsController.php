<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\PaperworkTrait;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

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
        $totalPayoutConfirmed = $entries->getCollection()->sum('payout_confirmed');

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

        $paperworks = \App\Models\Paperwork::with(['user', 'product', 'product.brand', 'customer'])
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
                $transformedPaperwork = $this->transformPaperworkAdmin($paperwork, $user);
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

        $paperworks = \App\Models\Paperwork::with(['user', 'product', 'product.brand'])
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
            $paperworks = $paperworks->whereHas('product', function ($query) use ($request) {
                $query->where('brand_id', $request->get('brand_id'));
            });
        }

        if ($request->filled('product_id')) {
            $paperworks = $paperworks->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('agent_id')) {
            $paperworks = $paperworks->where('user_id', $request->get('agent_id'));
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

    private function transformPaperworkAdmin($paperwork, $user)
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
            'agent' => implode(' ', array_filter([$paperwork->user->name, $paperwork->user->last_name])),
            'customer_id' => $paperwork->customer_id,
            'customer' => $paperwork->customer->name ?: $paperwork->customer->business_name,
            'tax_id_code' => $paperwork->customer->tax_id_code,
            'vat_number' => $paperwork->customer->vat_number,
            'brand_id' => $paperwork->product->brand_id,
            'brand' => $paperwork->product->brand->name,
            'product_id' => $paperwork->product_id,
            'product' => $paperwork->product->name,
            'order_code' => $paperwork->order_code,
            'account_pod_pdr' => $paperwork->account_pod_pdr,
            'paperwork_id' => $paperwork->id,
            'inserted_at' => $paperwork->partner_sent_at,
            'activated_at' => $paperwork->partner_outcome_at,
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
            'agent' => implode(' ', array_filter([$paperwork->user->name, $paperwork->user->last_name])),
            'brand_id' => $paperwork->product->brand_id,
            'brand' => $paperwork->product->brand->name,
            'product_id' => $paperwork->product_id,
            'product' => $paperwork->product->name,
            'order_code' => $paperwork->order_code,
            'paperwork_id' => $paperwork->id,
            'inserted_at' => $paperwork->partner_sent_at ? \Carbon\Carbon::parse($paperwork->partner_sent_at)->format(config('app.date_format')) : null,
            'status' => $paperwork->partner_outcome ?: $paperwork->order_status,
            'order_status' => $paperwork->order_status,
            'category' => $paperwork->category,
            'has_appointment' => $paperwork->appointment_id ? 'SI' : 'NO',
        ];
    }

    private function transformPaperworksToCSV($paperworks, $user)
    {
        $headers = [
            'Struttura',
            'Agente',
            'Brand',
            'Prodotto',
            'Pratica',
            'Insertita',
            'Stato',
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
                $data['brand'],
                $data['product'],
                $data['order_code'],
                $data['inserted_at'],
                $data['status'],
            ]);
        }

        fclose($fp);

        return $csvPath;
    }

    private function transformEntriesToCSV($entries, $report)
    {
        $headers = [
            'Struttura',
            'Agente',
            'Brand',
            'Prodotto',
            'Pratica',
            'Data Inserimento',
            'Data Attivazione',
            'Stato',
            'Compenso',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $entry) {
            fputcsv($fp, [
                $entry->parent,
                $entry->agent,
                $entry->brand,
                $entry->product,
                $entry->order_code,
                $entry->inserted_at,
                $entry->activated_at,
                $entry->status,
                $entry->payout_confirmed,
            ]);
        }

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
        $report->name = $request->get('name');
        $report->status = $request->get('status');
        $report->save();

        return response()->json([
            'message' => 'Nome aggiornato con successo',
        ]);
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
