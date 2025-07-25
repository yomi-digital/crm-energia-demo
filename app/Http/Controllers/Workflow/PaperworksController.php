<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\PaperworkTrait;

class PaperworksController extends Controller
{
    use PaperworkTrait;

    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $paperworks = \App\Models\Paperwork::with(['customer', 'user', 'mandate', 'product', 'product.brand']);

        if ($request->filled('customer_id')) {
            $paperworks = $paperworks->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('user_id')) {
            $paperworks = $paperworks->where('user_id', $request->get('user_id'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $paperworks = $paperworks->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('tax_id_code', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        // If the looged in user has role 'agente', filter for only his paperworks
        if ($request->user()->hasRole('agente')) {
            $paperworks = $paperworks->where('user_id', $request->user()->id);
        } elseif ($request->user()->hasRole('struttura')) {
            $relationships = \App\Models\UserRelationship::where('user_id', $request->user()->id)->get(['related_id']);
            $ids = $relationships->pluck('related_id')->merge([$request->user()->id]);
            $paperworks = $paperworks->whereIn('user_id', $ids);
        } elseif ($request->user()->hasRole('backoffice')) {
            $paperworks = $paperworks->whereHas('product', function ($query) use ($request) {
                $query->whereHas('brand', function ($query) use ($request) {
                    $query->whereIn('id', $request->user()->brands->pluck('id'));
                });
            });
        }

        if ($request->get('sortBy')) {
            $paperworks = $paperworks->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $paperworks = $paperworks->orderBy('created_at', 'desc');
        }

        $paperworks = $paperworks->paginate($perPage);

        return response()->json([
            'paperworks' => $paperworks->getCollection(),
            'totalPages' => $paperworks->lastPage(),
            'totalPaperworks' => $paperworks->total(),
            'page' => $paperworks->currentPage()
        ]);
    }

    public function show(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::with(['user', 'customer', 'customer.paperworks', 'mandate', 'product', 'documents', 'tickets', 'tickets.createdBy', 'createdByUser', 'confirmedByUser', 'events', 'events.user'])->whereId($id);
        
        // Aggiungi il conteggio degli allegati per ogni ticket
        $paperwork = $paperwork->with(['tickets' => function($query) {
            $query->withCount('attachments')->with('attachments');
        }]);

        if ($request->user()->hasRole('agente')) {
            $paperwork = $paperwork->where('user_id', $request->user()->id);
        } elseif ($request->user()->hasRole('struttura')) {
            $relationships = \App\Models\UserRelationship::where('user_id', $request->user()->id)->get(['related_id']);
            $ids = $relationships->pluck('related_id')->merge([$request->user()->id]);
            $paperwork = $paperwork->whereIn('user_id', $ids);
        }

        $paperwork = $paperwork->first();

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->payout = $this->calculatePaperworkPayout($paperwork);

        return response()->json($paperwork);
    }

    public function store(Request $request)
    {
        $paperwork = new \App\Models\Paperwork;

        if ($request->user()->hasRole('agent')) {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'appointment_id' => 'exists:calendar,id|nullable',
                'product_id' => 'required|exists:products,id',
                'account_pod_pdr' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->category !== 'ALLACCIO' && $request->energy_type !== 'MOBILE') {
                            // Controlla se il valore è vuoto, null o solo spazi
                            if (empty($value) || trim($value) === '') {
                                $fail('Account POD/PDR è obbligatorio per i contratti di energia che non siano ALLACCIO.');
                            }
                        }
                    },
                ],
                'annual_consumption' => 'nullable',
                'contract_type' => 'required',
                'category' => 'required',
                'type' => 'required',
                'energy_type' => 'required',
                'mobile_type' => 'required_if:energy_type,MOBILE|nullable',
                'coverage' => 'nullable',
                'previous_provider' => 'nullable',
                'notes' => 'nullable',
            ]);
            $fields = $request->only([
                'customer_id',
                'appointment_id',
                'product_id',
                'account_pod_pdr',
                'annual_consumption',
                'contract_type',
                'category',
                'type',
                'energy_type',
                'mobile_type',
                'coverage',
                'previous_provider',
                'notes',
            ]);
        } else {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'customer_id' => 'required|exists:customers,id',
                'appointment_id' => 'exists:calendar,id|nullable',
                'product_id' => 'required|exists:products,id',
                'account_pod_pdr' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->category !== 'ALLACCIO' && $request->energy_type !== 'MOBILE') {
                            // Controlla se il valore è vuoto, null o solo spazi
                            if (empty($value) || trim($value) === '') {
                                $fail('Account POD/PDR è obbligatorio per i contratti di energia che non siano ALLACCIO.');
                            }
                        }
                    },
                ],
                'annual_consumption' => 'nullable',
                'contract_type' => 'required',
                'category' => 'required',
                'type' => 'required',
                'energy_type' => 'required',
                'mobile_type' => 'required_if:energy_type,MOBILE|nullable',
                'coverage' => 'nullable',
                'previous_provider' => 'nullable',
                'notes' => 'nullable',
            ]);
            $fields = $request->all();
        }

        $paperwork->fill($fields);
        // $paperwork->order_status = 'CARICATO';

        if ($request->user()->hasRole('agent')) {
            $agent = $request->user();
        } else {
            $agent = \App\Models\User::find($request->get('user_id'));
        }
        $paperwork->manager_id = $agent->manager_id;
        $paperwork->created_by = $request->user()->id;

        $paperwork->save();

        return response()->json($paperwork, 201);
    }

    public function duplicate(Request $request)
    {
        // Valida che praticheIds sia presente e sia un array
        $request->validate([
            'praticheIds' => 'required|array|min:1',
            'praticheIds.*' => 'integer|exists:paperworks,id'
        ]);

        $results = [];
        $overrideData = $request->except('praticheIds'); // Dati da applicare a tutte le duplicazioni

        foreach ($request->praticheIds as $id) {
            try {
                // Trova la pratica originale
                $originalPaperwork = \App\Models\Paperwork::find($id);
                
                if (!$originalPaperwork) {
                    $results[] = [
                        'id' => $id,
                        'duplication' => 'error',
                        'message' => 'Pratica non trovata'
                    ];
                    continue;
                }

                // Duplica solo i campi che sono gestiti dal metodo store
                $allowedFields = [
                    'user_id',
                    'customer_id', 
                    'appointment_id',
                    'product_id',
                    'account_pod_pdr',
                    'annual_consumption',
                    'contract_type',
                    'category',
                    'type',
                    'energy_type',
                    'mobile_type',
                    'coverage',
                    'previous_provider',
                    'notes',
                    'owner_notes'
                ];
                
                $dataToDuplicate = [];
                foreach ($allowedFields as $field) {
                    // Copia sempre il campo, anche se è null
                    $dataToDuplicate[$field] = $originalPaperwork->$field;
                }

                // Applica eventuali override globali
                if (!empty($overrideData)) {
                    $dataToDuplicate = array_merge($dataToDuplicate, $overrideData);
                }

                // Crea request compatibile con le validazioni del store in base al ruolo utente
                if ($request->user()->hasRole('agent')) {
                    // Per agenti: usa solo i campi che store si aspetta
                    $storeCompatibleData = [];
                    $agentFields = [
                        'customer_id',
                        'appointment_id',
                        'product_id',
                        'account_pod_pdr',
                        'annual_consumption',
                        'contract_type',
                        'category',
                        'type',
                        'energy_type',
                        'mobile_type',
                        'coverage',
                        'previous_provider',
                        'notes',
                    ];
                    
                    foreach ($agentFields as $field) {
                        if (isset($dataToDuplicate[$field])) {
                            $storeCompatibleData[$field] = $dataToDuplicate[$field];
                        }
                    }
                } else {
                    // Per non-agenti: include user_id obbligatorio e tutti i campi
                    $storeCompatibleData = $dataToDuplicate;
                    // Se non specificato nell'override, mantieni l'agente originale
                    if (!isset($storeCompatibleData['user_id'])) {
                        $storeCompatibleData['user_id'] = $originalPaperwork->user_id;
                    }
                }


                // Riutilizza la logica del metodo store
                $duplicateRequest = new Request($storeCompatibleData);
                $duplicateRequest->setMethod('POST'); // Ensure it's a POST request
                $duplicateRequest->headers->set('Content-Type', 'application/json');
                $duplicateRequest->headers->set('Accept', 'application/json');
                $duplicateRequest->setUserResolver(function() use ($request) {
                    return $request->user();
                });

                $storeResponse = $this->store($duplicateRequest);
                
                if ($storeResponse->getStatusCode() === 201) {
                    $newPaperwork = json_decode($storeResponse->getContent(), true);
                    $results[] = [
                        'id' => $id,
                        'duplication' => 'success',
                        'message' => 'ok',
                        'new_id' => $newPaperwork['id']
                    ];
                } else {
                    $errorData = json_decode($storeResponse->getContent(), true);
                    $results[] = [
                        'id' => $id,
                        'duplication' => 'error',
                        'message' => $errorData['message'] ?? 'Errore durante la duplicazione'
                    ];
                }

            } catch (\Exception $e) {
                $results[] = [
                    'id' => $id,
                    'duplication' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json(['result' => $results]);
    }

    public function update(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->fill($request->all());

        if ($request->get('partner_outcome') && ! $paperwork->partner_outcome) {
            $paperwork->partner_outcome_at = now()->format('Y-m-d H:i:s');
        }
        if ($request->get('partner_outcome_at')) {
            // Check if the format is d/m/Y, then convert it to Y-m-d
            if (\Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_outcome_at'))->format('Y-m-d') !== $paperwork->partner_outcome_at) {
                $paperwork->partner_outcome_at = \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_outcome_at'))->format('Y-m-d');
            }
        }

        if ($request->get('order_status') && $request->get('order_status') === 'INSERITO' && ! $paperwork->partner_sent_at) {
            $paperwork->confirmed_at = now()->format('Y-m-d H:i:s');
            $paperwork->confirmed_by = $request->user()->id;
            $paperwork->partner_sent_at = now()->format('Y-m-d H:i:s');
        }
        if ($request->get('partner_sent_at')) {
            try {
                $partnerSentAt = \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_sent_at'))->format('Y-m-d');
            } catch (\Exception $e) {
                $partnerSentAt = \Carbon\Carbon::parse($request->get('partner_sent_at'))->format('Y-m-d');
            }
            if ($partnerSentAt !== $paperwork->partner_sent_at) {
                $paperwork->partner_sent_at = $partnerSentAt;
            }
        }

        if ($request->get('send_notification')) {
            $ticket = new \App\Models\Ticket;
            $ticket->title = 'Cambio sottostato pratica: ' . $request->get('order_substatus');
            $ticket->description = 'La pratica è stata aggiornata con il seguente sottostato: ' . $request->get('order_substatus');
            $ticket->paperwork_id = $paperwork->id;
            $ticket->created_by = $request->user()->id;
            $ticket->status = 1;
            $ticket->save();
        }

        $paperwork->save();

        return response()->json($paperwork);
    }

    public function documents(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        foreach ($request->get('documents') as $document) {
            if (! isset($document['path'])) {
                continue;
            }
            $doc = new \App\Models\PaperworkDocument;
            $doc->paperwork_id = $paperwork->id;
            $doc->name = basename($document['path']);
            $doc->url = $document['path'];
            $doc->save();
        }

        return response()->json(null, 201);
    }

    public function confirm(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->order_status = 'CONFERMATO';
        $paperwork->confirmed_by = $request->user()->id;
        $paperwork->confirmed_at = now()->format('Y-m-d H:i:s');

        $paperwork->save();

        return response()->json($paperwork);
    }

    public function confirmPartnerSent(Request $request, $id)
    {
        $request->validate([
            'order_code' => 'required',
        ]);
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $paperwork->order_status = 'INSERITO';

        $paperwork->order_code = $request->get('order_code');
        $paperwork->partner_sent_at = now()->format('Y-m-d H:i:s');

        $paperwork->save();

        return response()->json($paperwork);
    }

    public function calculatePayout(Request $request, $id)
    {
        if (! $request->user()->hasRole('gestione')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $payout = $this->calculatePaperworkPayout($paperwork);

        return response()->json(['payout' => $payout]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:paperworks,id',
            'order_status' => 'nullable|string',
            'order_substatus' => 'nullable|string',
            'partner_outcome' => 'nullable|string',
        ]);

        // Only update fields that are not empty. if the field is --- RIMUOVI ---, set it to null
        $fields = [];
        foreach ($request->only(['order_status', 'order_substatus', 'partner_outcome']) as $key => $value) {
            if ($value === '--- RIMUOVI ---') {
                $fields[$key] = null;
            } elseif ($value === '--- MANTIENI ---') {
                continue;
            } else {
                $fields[$key] = $value;
            }
        }

        // Get all paperworks first so we can trigger update events
        $paperworks = \App\Models\Paperwork::whereIn('id', $request->get('ids'))->get();
        foreach ($paperworks as $paperwork) {
            $paperwork->update($fields);
        }

        return response()->json(['message' => 'Paperworks updated successfully']);
    }

    public function downloadDocument(Request $request, $id, $documentId)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        $document = \App\Models\PaperworkDocument::find($documentId);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        return \Storage::disk('do')->download($document->url);
    }

    public function destroy(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::find($id);

        if (!$paperwork) {
            return response()->json(['error' => 'Paperwork not found'], 404);
        }

        // Delete all documents associated with the paperwork
        $paperwork->documents()->delete();

        // Delete all tickets associated with the paperwork
        $paperwork->tickets()->delete();

        // Delete all events associated with the paperwork
        $paperwork->events()->delete();

        \App\Models\ReportEntry::where('paperwork_id', $id)->delete();

        $paperwork->delete();

        return response()->json(['message' => 'Paperwork deleted successfully']);
    }
}
