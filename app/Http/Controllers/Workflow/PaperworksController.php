<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\PaperworkTrait;
use Illuminate\Support\Facades\DB;
use App\Notifications\PaperworkStatusUpdated;

class PaperworksController extends Controller
{
    use PaperworkTrait;

    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        // FASE 1 - Ottimizzazione: Lazy Loading Condizionale
        // Flag per tracciare quali JOIN sono necessari
        $needsCustomersJoin = false;
        $needsUsersJoin = false;
        $hasSearch = $request->filled('q');
        
        // Inizializza query base
        $paperworks = \App\Models\Paperwork::query();

        if ($request->filled('customer_id')) {
            $paperworks = $paperworks->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('user_id')) {
            $paperworks = $paperworks->where('user_id', $request->get('user_id'));
        }

        if ($request->filled('category')) {
            $paperworks = $paperworks->where('category', $request->get('category'));
        }

        if ($request->filled('date_from')) {
            $paperworks = $paperworks->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $paperworks = $paperworks->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // FASE 1 - Ottimizzazione: JOIN invece di whereHas per filtri customer
        if ($request->filled('phone')) {
            $phone = $request->get('phone');
            if (!$needsCustomersJoin) {
                $paperworks = $paperworks->leftJoin('customers', 'paperworks.customer_id', '=', 'customers.id');
                $needsCustomersJoin = true;
            }
            $paperworks = $paperworks->where(function ($query) use ($phone) {
                $query->where('customers.phone', 'like', "%{$phone}%")
                    ->orWhere('customers.mobile', 'like', "%{$phone}%");
            });
        }

        if ($request->filled('tax_id')) {
            $taxId = $request->get('tax_id');
            if (!$needsCustomersJoin) {
                $paperworks = $paperworks->leftJoin('customers', 'paperworks.customer_id', '=', 'customers.id');
                $needsCustomersJoin = true;
            }
            $paperworks = $paperworks->where('customers.tax_id_code', 'like', "%{$taxId}%");
        }

        if ($request->filled('email')) {
            $email = $request->get('email');
            if (!$needsCustomersJoin) {
                $paperworks = $paperworks->leftJoin('customers', 'paperworks.customer_id', '=', 'customers.id');
                $needsCustomersJoin = true;
            }
            $paperworks = $paperworks->where('customers.email', 'like', "%{$email}%");
        }

        if ($request->filled('pod_pdr')) {
            $podPdr = $request->get('pod_pdr');
            $paperworks = $paperworks->where('account_pod_pdr', 'like', "%{$podPdr}%");
        }

        if ($request->filled('order_code')) {
            $orderCode = $request->get('order_code');
            $paperworks = $paperworks->where('order_code', 'like', "%{$orderCode}%");
        }

        if ($request->filled('product_id')) {
            $paperworks = $paperworks->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('contract_type')) {
            $paperworks = $paperworks->where('contract_type', $request->get('contract_type'));
        }

        if ($request->filled('type')) {
            $paperworks = $paperworks->where('type', $request->get('type'));
        }

        // FASE 1 - Ottimizzazione: JOIN invece di Subquery per la ricerca
        if ($request->get('q')) {
            $search = trim($request->get('q'));
            
            // FASE 1 - Ottimizzazione: JOIN invece di whereExists
            // Aggiungi JOIN per customers se necessario
            if (!$needsCustomersJoin) {
                $paperworks = $paperworks->leftJoin('customers', 'paperworks.customer_id', '=', 'customers.id');
                $needsCustomersJoin = true;
            }
            // Aggiungi JOIN per users se necessario
            if (!$needsUsersJoin) {
                $paperworks = $paperworks->leftJoin('users', 'paperworks.user_id', '=', 'users.id');
                $needsUsersJoin = true;
            }
            
            $paperworks = $paperworks->where(function ($query) use ($search) {
                // Se la ricerca è numerica, cerca per ID o order_code
                if (is_numeric($search)) {
                    $query->where('paperworks.id', $search)
                        ->orWhere('paperworks.order_code', 'like', "%{$search}%");
                } else {
                    // Cerca nei campi della pratica
                    $query->where('paperworks.order_code', 'like', "%{$search}%")
                        ->orWhere('paperworks.account_pod_pdr', 'like', "%{$search}%");
                }
                
                // Cerca nei campi del cliente tramite JOIN
                $query->orWhere('customers.name', 'like', "%{$search}%")
                    ->orWhere('customers.last_name', 'like', "%{$search}%")
                    ->orWhere('customers.business_name', 'like', "%{$search}%")
                    // Cerca nel nome dell'agente tramite JOIN
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%");
            });
        }

        // Se abbiamo fatto JOIN, dobbiamo selezionare solo le colonne di paperworks e usare distinct
        if ($needsCustomersJoin || $needsUsersJoin) {
            $paperworks = $paperworks->select('paperworks.*')->distinct();
        }

        // If the looged in user has role 'agente', filter for only his paperworks
        $tablePrefix = ($needsCustomersJoin || $needsUsersJoin) ? 'paperworks.' : '';
        
        if ($request->user()->hasRole('agente')) {
            $paperworks = $paperworks->where($tablePrefix . 'user_id', $request->user()->id);
        } elseif ($request->user()->hasRole('struttura')) {
            // Ottimizzazione: usa subquery invece di get() per migliorare le performance
            $userIds = \App\Models\UserRelationship::where('user_id', $request->user()->id)
                ->pluck('related_id')
                ->push($request->user()->id)
                ->unique()
                ->values();
            $paperworks = $paperworks->whereIn($tablePrefix . 'user_id', $userIds);
        } elseif ($request->user()->hasRole('backoffice')) {
            // Filtro per brand (già esistente)
            $paperworks = $paperworks->whereHas('product', function ($query) use ($request) {
                $query->whereHas('brand', function ($query) use ($request) {
                    $query->whereIn('id', $request->user()->brands->pluck('id'));
                });
            });
            
            // Filtro per area: il backoffice vede pratiche create da utenti con la stessa area
            // O da utenti senza area (null o stringa vuota) - queste sono visibili a tutti i backoffice
            /* if ($request->user()->area) {
                $paperworks = $paperworks->whereHas('user', function ($query) use ($request) {
                    $query->where('area', $request->user()->area)
                          ->orWhereNull('area')
                          ->orWhere('area', '');
                });
            } */
        }

        if ($request->get('sortBy')) {
            $sortBy = $request->get('sortBy');
            // Se abbiamo JOIN, assicuriamoci che il campo di ordinamento sia qualificato
            if (($needsCustomersJoin || $needsUsersJoin) && !str_contains($sortBy, '.')) {
                $sortBy = 'paperworks.' . $sortBy;
            }
            $paperworks = $paperworks->orderBy($sortBy, $request->get('orderBy', 'desc'));
        } else {
            $orderBy = ($needsCustomersJoin || $needsUsersJoin) ? 'paperworks.created_at' : 'created_at';
            $paperworks = $paperworks->orderBy($orderBy, 'desc');
        }

        $paperworks = $paperworks->paginate($perPage);
        
        // FASE 1 - Ottimizzazione: Carica le relazioni solo dopo la paginazione
        // Questo riduce drasticamente il carico durante la ricerca
        if ($needsCustomersJoin || $needsUsersJoin || $hasSearch) {
            $paperworks->getCollection()->load(['customer', 'user', 'mandate', 'product', 'product.brand']);
        } else {
            // Se non abbiamo fatto JOIN, possiamo usare eager loading normale
            $paperworks->load(['customer', 'user', 'mandate', 'product', 'product.brand']);
        }

        return response()->json([
            'paperworks' => $paperworks->getCollection(),
            'totalPages' => $paperworks->lastPage(),
            'totalPaperworks' => $paperworks->total(),
            'page' => $paperworks->currentPage()
        ]);
    }

    public function show(Request $request, $id)
    {
        $paperwork = \App\Models\Paperwork::with(['user', 'customer', 'customer.paperworks', 'customer.confirmedByUser', 'customer.addedByUser', 'mandate', 'product', 'documents', 'tickets', 'tickets.createdBy', 'createdByUser', 'confirmedByUser', 'events', 'events.user'])->whereId($id);
        
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
        } elseif ($request->user()->hasRole('backoffice')) {
            // Filtro per area: il backoffice vede pratiche create da utenti con la stessa area
            // O da utenti senza area (null o stringa vuota) - queste sono visibili a tutti i backoffice
            /* if ($request->user()->area) {
                $paperwork = $paperwork->whereHas('user', function ($query) use ($request) {
                    $query->where('area', $request->user()->area)
                          ->orWhereNull('area')
                          ->orWhere('area', '');
                });
            } */
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

        // Se skipControl è presente, salta la validazione (utile per duplicazione di pratiche incomplete)
        $skipValidation = $request->has('skipControl') && $request->get('skipControl') == true;

        if (!$skipValidation && $request->user()->hasRole('agent')) {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'appointment_id' => 'exists:calendar,id|nullable',
                'product_id' => 'required|exists:products,id',
                'mandate_id' => 'nullable|exists:mandates,id',
                'account_pod_pdr' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        // POD obbligatorio solo per ENERGIA, non per TELEFONIA
                        if ($request->type === 'ENERGIA' && $request->category !== 'ALLACCIO' && $request->energy_type !== 'MOBILE') {
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
                'mandate_id',
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
        }
        
        if (!$skipValidation && !$request->user()->hasRole('agent')) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'customer_id' => 'required|exists:customers,id',
                'appointment_id' => 'exists:calendar,id|nullable',
                'product_id' => 'required|exists:products,id',
                'mandate_id' => 'nullable|exists:mandates,id',
                'account_pod_pdr' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        // POD obbligatorio solo per ENERGIA, non per TELEFONIA
                        if ($request->type === 'ENERGIA' && $request->category !== 'ALLACCIO' && $request->energy_type !== 'MOBILE') {
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
        }
        
        if ($request->user()->hasRole('agent')) {
            $fields = $request->only([
                'customer_id',
                'appointment_id',
                'product_id',
                'mandate_id',
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
            $fields = $request->all();
        }

        // Rimuovi skipControl dai campi da salvare (non è un campo del modello)
        unset($fields['skipControl']);

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

        // Salvataggio opzionale dei documenti collegati alla pratica
        if ($request->filled('documents') && is_array($request->documents)) {
            foreach ($request->documents as $document) {
                if (empty($document['path'])) {
                    continue;
                }

                $doc = new \App\Models\PaperworkDocument;
                $doc->paperwork_id = $paperwork->id;
                $doc->name = basename($document['path']);
                $doc->url = $document['path'];
                $doc->save();
            }
        }

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
                // Aggiungi skipControl per saltare la validazione (permette di duplicare pratiche incomplete)
                $storeCompatibleData['skipControl'] = true;
                
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

        // Gestisci i campi data prima di fill per evitare errori di formato
        $partnerOutcomeAt = null;
        if ($request->has('partner_outcome_at') && $request->get('partner_outcome_at')) {
            try {
                // Tenta di creare da d/m/Y (formato frontend) o usa il valore così com'è
                $partnerOutcomeAt = \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_outcome_at'));
            } catch (\Exception $e) {
                // Se fallisce, prova a interpretarlo come data qualsiasi
                try {
                    $partnerOutcomeAt = \Carbon\Carbon::parse($request->get('partner_outcome_at'));
                } catch (\Exception $e) {
                    // Se anche questo fallisce, ignora l'input per evitare l'errore
                    $partnerOutcomeAt = null;
                }
            }
        }
        
        $partnerSentAt = null;
        if ($request->has('partner_sent_at') && $request->get('partner_sent_at')) {
            try {
                $partnerSentAt = \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_sent_at'));
            } catch (\Exception $e) {
                try {
                    $partnerSentAt = \Carbon\Carbon::parse($request->get('partner_sent_at'));
                } catch (\Exception $e) {
                    $partnerSentAt = null;
                }
            }
        }

        // Rimuovi i campi data dalla request prima del fill() per evitare problemi
        $dataToFill = $request->except(['partner_outcome_at', 'partner_sent_at']);

        $paperwork->fill($dataToFill);

        // Assegna le date processate
        if ($partnerOutcomeAt) {
            $paperwork->partner_outcome_at = $partnerOutcomeAt->format('Y-m-d H:i:s');
        }
        if ($partnerSentAt) {
            $paperwork->partner_sent_at = $partnerSentAt->format('Y-m-d H:i:s');
        }

        // Logica esistente per l'aggiornamento di partner_outcome_at e partner_sent_at se non già presenti
        if ($request->get('partner_outcome') && ! $paperwork->partner_outcome) {
            $paperwork->partner_outcome_at = now()->format('Y-m-d H:i:s');
        }

        if ($request->get('order_status') && $request->get('order_status') === 'INSERITO' && ! $paperwork->partner_sent_at) {
            $paperwork->confirmed_at = now()->format('Y-m-d H:i:s');
            $paperwork->confirmed_by = $request->user()->id;
            $paperwork->partner_sent_at = now()->format('Y-m-d H:i:s');
        }

        $paperwork->save();

        // Se è stato richiesto di notificare l'agente, invia l'email all'utente assegnato alla pratica (user_id)
        if ($request->get('notify_agent') && $paperwork->user_id) {
            $agent = \App\Models\User::find($paperwork->user_id);
            if ($agent) {
                $agent->notify(new PaperworkStatusUpdated($paperwork));
            }
        }

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
            'partner_outcome_at' => 'nullable|string',
            'partner_sent_at' => 'nullable|string',
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

        // Handle partner_outcome_at separately due to date formatting
        if ($request->has('partner_outcome_at') && $request->get('partner_outcome_at')) {
            try {
                $partnerOutcomeAt = \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_outcome_at'))->format('Y-m-d');
                $fields['partner_outcome_at'] = $partnerOutcomeAt;
            } catch (\Exception $e) {
                
                // Return error response - stop the entire bulk update process
                return response()->json([
                    'error' => 'Formato data non valido per "Data Esito Partner". Utilizzare il formato DD/MM/YYYY.',
                    'invalid_date' => $request->get('partner_outcome_at')
                ], 422);
            }
        }

        // Handle partner_sent_at separately due to date formatting
        if ($request->has('partner_sent_at') && $request->get('partner_sent_at')) {
            try {
                $partnerSentAt = \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('partner_sent_at'))->format('Y-m-d');
                $fields['partner_sent_at'] = $partnerSentAt;
            } catch (\Exception $e) {
                
                // Return error response - stop the entire bulk update process
                return response()->json([
                    'error' => 'Formato data non valido per "Data invio partner". Utilizzare il formato DD/MM/YYYY.',
                    'invalid_date' => $request->get('partner_sent_at')
                ], 422);
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
        // Verifica se l'utente è un agente - gli agenti non possono eliminare pratiche
        if ($request->user()->hasRole('agente')) {
            return response()->json([
                'error' => 'Non hai i permessi per eliminare questa pratica'
            ], 403);
        }

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
