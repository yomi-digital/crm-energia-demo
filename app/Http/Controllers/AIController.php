<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AIPaperwork;
use App\Services\ContractProcessingService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AIController extends Controller
{
    protected $contractProcessingService;

    public function __construct(ContractProcessingService $contractProcessingService)
    {
        $this->contractProcessingService = $contractProcessingService;
    }

    public function paperworks(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $aiPaperworks = AIPaperwork::with('user');

        if ($request->get('id')) {
            $aiPaperworks = $aiPaperworks->where('id', $request->get('id'));
        }

        if ($request->filled('user_id')) {
            $aiPaperworks = $aiPaperworks->where('user_id', $request->get('user_id'));
        }

        if ($request->filled('status')) {
            $aiPaperworks = $aiPaperworks->where('status', $request->get('status'));
        }

        // Filtro per escludere le pratiche confermate (status = 5)
        $aiPaperworks = $aiPaperworks->where('status', '!=', 5);

        // Filtro per backoffice: vedere solo AI paperworks con brand_id assegnati
        if ($request->user()->hasRole('backoffice')) {
            $userBrands = $request->user()->brands->pluck('id');
            if ($userBrands->isNotEmpty()) {
                $aiPaperworks = $aiPaperworks->whereIn('brand_id', $userBrands);
            } else {
                // Se il backoffice non ha brand assegnati, non vede nessuna AI paperwork
                $aiPaperworks = $aiPaperworks->whereRaw('1 = 0');
            }
        }

        if ($request->get('sortBy')) {
            $aiPaperworks = $aiPaperworks->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $aiPaperworks = $aiPaperworks->orderBy('created_at', 'desc');
        }

        $aiPaperworks = $aiPaperworks->paginate($perPage);

        return response()->json([
            'entries' => $aiPaperworks->getCollection(),
            'totalPages' => $aiPaperworks->lastPage(),
            'totalEntries' => $aiPaperworks->total(),
            'page' => $aiPaperworks->currentPage(),
        ]);
    }

    public function process(Request $request, $id)
    {
        try {
            $aiPaperwork = AIPaperwork::findOrFail($id);
            
            // Reset status a 0 per permettere allo scheduler di processarlo
            $aiPaperwork->status = 0;
            $aiPaperwork->save();

            return response()->json([
                'message' => 'Document reset to pending status. The scheduler will process it within 1 minute.',
                'ai_paperwork' => $aiPaperwork
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to reset document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $aiPaperwork = AIPaperwork::with('user')->findOrFail($id);

        return response()->json([
            'id' => $aiPaperwork->id,
            'user_id' => $aiPaperwork->user_id,
            'brand_id' => $aiPaperwork->brand_id,
            'filepath' => $aiPaperwork->filepath,
            'original_filename' => $aiPaperwork->original_filename,
            'status' => $aiPaperwork->status,
            'extracted_text' => $aiPaperwork->extracted_text,
            'ai_extracted_customer' => $aiPaperwork->ai_extracted_customer,
            'ai_extracted_paperwork' => $aiPaperwork->ai_extracted_paperwork,
            'prompt_output' => $aiPaperwork->prompt_output,
            'transfers_history' => $aiPaperwork->transfers_history,
            'created_at' => $aiPaperwork->created_at->toISOString(),
            'updated_at' => $aiPaperwork->updated_at->toISOString(),
            'user' => $aiPaperwork->user
        ]);
    }

    public function update(Request $request, $id)
    {
        $aiPaperwork = AIPaperwork::findOrFail($id);

        // Validazione telefono e cellulare se i dati cliente sono stati modificati
        if ($request->has('ai_extracted_customer')) {
            $customerData = $request->ai_extracted_customer;
            $errors = [];
            
            // Ottieni il customerId se presente (da ai_extracted_paperwork)
            $customerId = null;
            if ($request->has('ai_extracted_paperwork')) {
                $paperworkData = $request->ai_extracted_paperwork;
                $customerId = $paperworkData['customer_id'] ?? null;
            }
            
            // Se ci sono errori, restituisci errore 422
            if (!empty($errors)) {
                return response()->json([
                    'message' => 'I dati forniti non sono validi.',
                    'errors' => $errors
                ], 422);
            }
        }

        // Update the extracted data
        if ($request->has('ai_extracted_customer')) {
            $aiPaperwork->ai_extracted_customer = json_encode($request->ai_extracted_customer);
        }
        
        if ($request->has('ai_extracted_paperwork')) {
            $paperworkData = $request->ai_extracted_paperwork;
            
            // Se non è da appuntamento, forza appointment_id a null
            if (isset($paperworkData['is_from_appointment']) && !$paperworkData['is_from_appointment']) {
                $paperworkData['appointment_id'] = null;
            }
            
            // Rimuovi i campi del match AI quando l'utente salva le modifiche
            // L'utente ha verificato i dati, quindi non servono più
            unset($paperworkData['brand_override']);
            unset($paperworkData['matched_product']);
            unset($paperworkData['matched_brand']);
            unset($paperworkData['original_brand_id']);
            
            $aiPaperwork->ai_extracted_paperwork = json_encode($paperworkData);
        }

        // Prima gestiamo il brand_id esplicito solo se non c'è un product_id
        $paperworkData = $request->ai_extracted_paperwork ?? [];
        $hasProductId = isset($paperworkData['product_id']) && $paperworkData['product_id'];
        
        // Salva il brand_id corrente prima delle modifiche
        $previousBrandId = $aiPaperwork->brand_id;
        
        // Se NON c'è product_id e c'è brand_id esplicito, usa quello
        if (!$hasProductId && $request->has('brand_id') && $request->brand_id) {
            $aiPaperwork->brand_id = $request->brand_id;
        }
        
        // Il prodotto ha SEMPRE la priorità: se c'è product_id, forza il brand_id
        if ($hasProductId) {
            $product = \App\Models\Product::find($paperworkData['product_id']);
            if ($product && $product->brand_id) {
                $aiPaperwork->brand_id = $product->brand_id;
            }
        }
        
        // Se il brand è cambiato, aggiungi alla cronologia dei trasferimenti
        if ($previousBrandId !== null && $aiPaperwork->brand_id !== null && $previousBrandId != $aiPaperwork->brand_id) {
            $transfersHistory = $aiPaperwork->transfers_history ?? [];
            
            $transfersHistory[] = [
                'from_brand_id' => $previousBrandId,
                'to_brand_id' => $aiPaperwork->brand_id,
                'transferred_at' => now()->toDateTimeString(),
                'transferred_by' => $request->user()->id ?? null,
            ];
            
            $aiPaperwork->transfers_history = $transfersHistory;
        }

        $aiPaperwork->save();

        return response()->json($aiPaperwork);
    }

    /**
     * Conferma una pratica AI e crea il cliente e la pratica definitivi
     * 
     * MATRICE GESTIONE CLIENTI E TIPOLOGIE CONTRATTO:
     * 
     * ┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
     * │ CLIENTE NUOVO (id: null)                                                                               │
     * ├─────────────────────────────┬──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Residenziale│ → category: 'Residenziale', business_name: null                         │
     * │                             │ → ✅ Cliente Residenziale creato                                         │
     * ├─────────────────────────────┼──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Business    │ → category: null (Ditta), business_name: obbligatoria                   │
     * │                             │ → ✅ Cliente Ditta Individuale creato con ragione sociale                │
     * └─────────────────────────────┴──────────────────────────────────────────────────────────────────────────┘
     * 
     * ┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
     * │ CLIENTE ESISTENTE - RESIDENZIALE (category: 'Residenziale')                                            │
     * ├─────────────────────────────┬──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Residenziale│ → category: 'Residenziale', business_name: null                         │
     * │                             │ → ✅ Resta Residenziale                                                  │
     * ├─────────────────────────────┼──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Business    │ → category: null (Ditta), business_name: aggiornata                     │
     * │                             │ → ✅ Passa da Residenziale a Ditta Individuale                           │
     * └─────────────────────────────┴──────────────────────────────────────────────────────────────────────────┘
     * 
     * ┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
     * │ CLIENTE ESISTENTE - BUSINESS (category: 'Business')                                                    │
     * ├─────────────────────────────┬──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Residenziale│ → ❌ ERRORE: "Non si può assegnare pratica residenziale a cliente       │
     * │                             │              Business"                                                   │
     * ├─────────────────────────────┼──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Business    │ → category: 'Business', business_name: aggiornata                       │
     * │                             │ → ✅ Resta Business, ragione sociale aggiornata                          │
     * └─────────────────────────────┴──────────────────────────────────────────────────────────────────────────┘
     * 
     * ┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
     * │ CLIENTE ESISTENTE - DITTA INDIVIDUALE (category: null)                                                 │
     * ├─────────────────────────────┬──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Residenziale│ → category: null (Ditta), business_name: null                           │
     * │                             │ → ✅ Resta Ditta Individuale, può avere pratiche Residenziali            │
     * ├─────────────────────────────┼──────────────────────────────────────────────────────────────────────────┤
     * │ Tipo Contratto: Business    │ → category: null (Ditta), business_name: aggiornata                     │
     * │                             │ → ✅ Resta Ditta Individuale, ragione sociale aggiornata                 │
     * └─────────────────────────────┴──────────────────────────────────────────────────────────────────────────┘
     * 
     * NOTA: Tutti i campi anagrafica (nome, email, indirizzo, ecc.) hanno priorità ai dati forniti,
     *       altrimenti mantengono i valori esistenti del cliente (nessun dato viene perso).
     */
    public function confirm(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $aiPaperwork = AIPaperwork::findOrFail($id);
        
        // Validazione telefono e cellulare prima della conferma
        if ($request->has('ai_extracted_customer')) {
            $customerData = $request->ai_extracted_customer;
            $errors = [];
            
            // Ottieni il customerId se presente (da ai_extracted_paperwork)
            $customerId = null;
            if ($request->has('ai_extracted_paperwork')) {
                $paperworkData = $request->ai_extracted_paperwork;
                $customerId = $paperworkData['customer_id'] ?? null;
            }
            
            // Se ci sono errori, restituisci errore 422
            if (!empty($errors)) {
                return response()->json([
                    'message' => 'I dati forniti non sono validi.',
                    'errors' => $errors
                ], 422);
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Aggiorna l'AI paperwork con gli ultimi dati se forniti
            if ($request->has('ai_extracted_customer')) {
                $aiPaperwork->ai_extracted_customer = json_encode($request->ai_extracted_customer);
            }
            
            if ($request->has('ai_extracted_paperwork')) {
                $paperworkData = $request->ai_extracted_paperwork;
                
                // Se non è da appuntamento, forza appointment_id a null
                if (isset($paperworkData['is_from_appointment']) && !$paperworkData['is_from_appointment']) {
                    $paperworkData['appointment_id'] = null;
                }
                
                $aiPaperwork->ai_extracted_paperwork = json_encode($paperworkData);
            }
            
            // Salva l'AI paperwork aggiornato
            $aiPaperwork->save();
            
            // Extract data (ora aggiornati)
            $customerData = json_decode($aiPaperwork->ai_extracted_customer, true) ?: [];
            $paperworkData = json_decode($aiPaperwork->ai_extracted_paperwork, true) ?: [];

            // Ricerca intelligente cliente basata su CF (Residenziale) o P.IVA (Business)
            // NO fallback email - solo identificatori fiscali univoci
            $contractType = $paperworkData['contract_type'] ?? null;
            $foundCustomer = null;
            
            if (!empty($customerData['vat_number'])) {
                $foundCustomer = \App\Models\Customer::whereRaw('LOWER(vat_number) = ?', [strtolower(trim($customerData['vat_number']))])->first();
            } elseif (!empty($customerData['tax_id_code'])) {
                $foundCustomer = \App\Models\Customer::whereRaw('LOWER(tax_id_code) = ?', [strtolower(trim($customerData['tax_id_code']))])->first();
            }
            
            if ($foundCustomer) {
                $customerData['id'] = $foundCustomer->id;
            } else {
                // Cliente non trovato - sarà creato un nuovo cliente
                unset($customerData['id']);
            }

            // Create or update customer
            $customer = null;
            if (!empty($customerData['id'])) {
                $customer = \App\Models\Customer::find($customerData['id']);
            }

            if (!$customer) {
                $customer = new \App\Models\Customer;
                $customer->added_at = now()->format('Y-m-d');
                $customer->added_by = $request->user()->id;
            }

            // Validazione condizionale: se contract_type è Business, business_name è obbligatoria
            $contractType = $paperworkData['contract_type'] ?? null;
            if ($contractType === 'Business' && empty($customerData['business_name'])) {
                DB::rollBack();
                return response()->json([
                    'message' => 'La ragione sociale è obbligatoria per i contratti Business.',
                    'errors' => [
                        'business_name' => 'La ragione sociale è obbligatoria per i contratti Business.'
                    ]
                ], 422);
            }

            $newCustomerCategory = $customer->category ?? null;
            $newBusinessName = $customerData['business_name'] ?? null;

            if($contractType === 'Business') {
                //CASO DI CONTRATTO BUSINESS
                if($customer->category === 'Residenziale') {
                    $newCustomerCategory = null; //Cosi diventa dittà individuale
                }

            } else {
                //CASO DI CONTRATTO RESIDENZIALE
                $newBusinessName = null;
                if($customer->category === 'Business') {
                    // Solo i Business non possono diventare Residenziali
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Non si può assegnare una pratica residenziale ad un cliente Business',
                        'customer' => $customer
                    ], 500);
                } else {
                    // Se è un nuovo cliente, imposta come Residenziale
                    if (!$customer->id) {
                        $newCustomerCategory = 'Residenziale';
                    }
                    // Se è Ditta Individuale (category null), resta Ditta
                    // Se è già Residenziale, resta Residenziale
                }
                
            }

            // Parametro per controllare se sovrascrivere i dati del cliente esistente
            $overrideCustomer = $request->input('override_customer', true);
            
            // Se è un cliente esistente e override_customer è false, mantieni tutti i dati esistenti
            // Solo category e business_name vengono gestiti dalla logica sopra
            if ($customer->id && !$overrideCustomer) {
                // Cliente esistente, NON sovrascrivere - aggiorna solo category e business_name
                $customer->business_name = $newBusinessName;
                $customer->category = $newCustomerCategory;
            } else {
                // Nuovo cliente OPPURE override_customer=true - aggiorna tutti i campi forniti
                // Cerco tutti i clienti con lo stesso codice fiscale o partita iva e faccio fill per tutti
                $customersToRefill = collect([]);
                if (!empty($customerData['vat_number'])) {
                    $customersToRefill = \App\Models\Customer::whereRaw('LOWER(vat_number) = ?', [strtolower(trim($customerData['vat_number']))])->get();
                } elseif (!empty($customerData['tax_id_code'])) {
                    $customersToRefill = \App\Models\Customer::whereRaw('LOWER(tax_id_code) = ?', [strtolower(trim($customerData['tax_id_code']))])->get();
                }
                
                foreach ($customersToRefill as $customerToRefill) {
                    if($customerToRefill->id === $customer->id) {
                        continue;
                    }
                    $customerToRefill->fill([
                        'name' => $customerData['name'] ?? $customerToRefill->name,
                        'last_name' => $customerData['last_name'] ?? $customerToRefill->last_name,
                        'business_name' => $customerToRefill->business_name,
                        'email' => $customerData['email'] ?? $customerToRefill->email,
                        'phone' => $customerData['phone'] ?? $customerToRefill->phone,
                        'mobile' => $customerData['mobile'] ?? $customerToRefill->mobile,
                        'address' => $customerData['address'] ?? $customerToRefill->address,
                        'city' => $customerData['city'] ?? $customerToRefill->city,
                        'zip_code' => $customerData['zip_code'] ?? $customerToRefill->zip_code,
                        'province' => $customerData['province'] ?? $customerToRefill->province,
                        'region' => $customerData['region'] ?? $customerToRefill->region,
                        'tax_id_code' => $customerData['tax_id_code'] ?? $customerToRefill->tax_id_code,
                        'vat_number' => $customerData['vat_number'] ?? $customerToRefill->vat_number,
                        'category' => $customerToRefill->category
                    ]);
                    $customerToRefill->save();
                }
                $customer->fill([
                    'name' => $customerData['name'] ?? $customer->name,
                    'last_name' => $customerData['last_name'] ?? $customer->last_name,
                    'business_name' => $newBusinessName,
                    'email' => $customerData['email'] ?? $customer->email,
                    'phone' => $customerData['phone'] ?? $customer->phone,
                    'mobile' => $customerData['mobile'] ?? $customer->mobile,
                    'address' => $customerData['address'] ?? $customer->address,
                    'city' => $customerData['city'] ?? $customer->city,
                    'zip_code' => $customerData['zip_code'] ?? $customer->zip_code,
                    'province' => $customerData['province'] ?? $customer->province,
                    'region' => $customerData['region'] ?? $customer->region,
                    'tax_id_code' => $customerData['tax_id_code'] ?? $customer->tax_id_code,
                    'vat_number' => $customerData['vat_number'] ?? $customer->vat_number,
                    'category' => $newCustomerCategory
                ]);
            }

            $customer->save();

            // Create paperwork
            $paperwork = new \App\Models\Paperwork;
            $paperwork->fill([
                'customer_id' => $customer->id,
                'user_id' => $aiPaperwork->user_id,
                'product_id' => $request->product_id,
                'appointment_id' => (isset($paperworkData['is_from_appointment']) && $paperworkData['is_from_appointment']) 
                    ? ($paperworkData['appointment_id'] ?? null) 
                    : null,
                'account_pod_pdr' => $paperworkData['account_pod_pdr'] ?? null,
                'annual_consumption' => $paperworkData['annual_consumption'] ?? null,
                'contract_type' => $paperworkData['contract_type'] ?? null,
                'category' => $paperworkData['category'] ?? null,
                'type' => $paperworkData['type'] ?? null,
                'energy_type' => $paperworkData['energy_type'] ?? null,
                'mobile_type' => $paperworkData['mobile_type'] ?? null,
                'previous_provider' => $paperworkData['previous_provider'] ?? null,
                'mandate_id' => $request->mandate_id ?? null,
                'transfers_history' => $aiPaperwork->transfers_history ?? null,
            ]);

            $paperwork->created_by = $request->user()->id;
            $paperwork->save();

            // Link document
            $doc = new \App\Models\PaperworkDocument;
            $doc->paperwork_id = $paperwork->id;
            $doc->name = basename($aiPaperwork->filepath);
            $doc->url = $aiPaperwork->filepath;
            $doc->save();

            // Salva il brand_id corrente prima delle modifiche
            $previousBrandId = $aiPaperwork->brand_id;
            
            // Prima gestiamo il brand_id esplicito (ma sarà sempre sovrascritto dal prodotto)
            if ($request->has('brand_id') && $request->brand_id) {
                $aiPaperwork->brand_id = $request->brand_id;
            }
            
            // Il prodotto ha SEMPRE la priorità: il product_id è obbligatorio in confirm, quindi forza sempre il brand_id
            $product = \App\Models\Product::find($request->product_id);
            if ($product && $product->brand_id) {
                $aiPaperwork->brand_id = $product->brand_id;
            }
            
            // Se il brand è cambiato durante la conferma, aggiungi alla cronologia dei trasferimenti
            if ($previousBrandId !== null && $aiPaperwork->brand_id !== null && $previousBrandId != $aiPaperwork->brand_id) {
                $transfersHistory = $aiPaperwork->transfers_history ?? [];
                
                $transfersHistory[] = [
                    'from_brand_id' => $previousBrandId,
                    'to_brand_id' => $aiPaperwork->brand_id,
                    'transferred_at' => now()->toDateTimeString(),
                    'transferred_by' => $request->user()->id ?? null,
                ];
                
                $aiPaperwork->transfers_history = $transfersHistory;
                
                // Aggiorna anche la pratica normale con la cronologia aggiornata
                $paperwork->transfers_history = $transfersHistory;
                $paperwork->save();
            }
            
            // Update AI paperwork status to 5 (Confirmed)
            $aiPaperwork->status = 5;
            $aiPaperwork->save();

            DB::commit();

            return response()->json([
                'message' => 'Paperwork confirmed successfully',
                'customer' => $customer,
                'paperwork' => $paperwork,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to confirm paperwork: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $aiPaperwork = AIPaperwork::findOrFail($id);
        
        if (!Storage::disk('do')->exists($aiPaperwork->filepath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('do')->download($aiPaperwork->filepath, $aiPaperwork->original_filename);
    }

    public function cancel($id)
    {
        $aiPaperwork = AIPaperwork::findOrFail($id);
        
        // Update status to cancelled (8)
        $aiPaperwork->status = 8;
        $aiPaperwork->save();

        return response()->json($aiPaperwork);
    }

    public function updateEmail(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $aiPaperwork = AIPaperwork::findOrFail($id);
        
        // Sanitizza email (usando la stessa logica del ContractProcessingService)
        $sanitizedEmail = trim(strtolower($request->email));
        
        // Cerca cliente esistente con questa email
        $existingCustomer = \App\Models\Customer::where('email', $sanitizedEmail)
            ->whereNull('deleted_at')
            ->first();
        
        // Aggiorna i dati estratti
        $customerData = json_decode($aiPaperwork->ai_extracted_customer, true) ?: [];
        $customerData['email'] = $sanitizedEmail;
        
        // Se trova un cliente esistente, aggiorna anche l'ID
        if ($existingCustomer) {
            $customerData['id'] = $existingCustomer->id;
            
            // Aggiorna anche nei dati paperwork per i componenti telefono
            $paperworkData = json_decode($aiPaperwork->ai_extracted_paperwork, true) ?: [];
            $paperworkData['customer_id'] = $existingCustomer->id;
            $aiPaperwork->ai_extracted_paperwork = json_encode($paperworkData);
        } else {
            // Imposta ID a null se il cliente non esiste
            $customerData['id'] = null;
            
            $paperworkData = json_decode($aiPaperwork->ai_extracted_paperwork, true) ?: [];
            $paperworkData['customer_id'] = null;
            $aiPaperwork->ai_extracted_paperwork = json_encode($paperworkData);
        }
        
        $aiPaperwork->ai_extracted_customer = json_encode($customerData);
        $aiPaperwork->save();

        return response()->json([
            'message' => 'Email aggiornata con successo',
            'customer_found' => $existingCustomer ? true : false,
            'customer_id' => $existingCustomer ? $existingCustomer->id : null,
        ]);
    }

    public function transfer(Request $request, $id)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
        ]);

        $aiPaperwork = AIPaperwork::findOrFail($id);
        
        // Salva il brand precedente per il log
        $previousBrandId = $aiPaperwork->brand_id;
        
        // Aggiorna il brand_id (tutti possono trasferire verso qualsiasi brand)
        $aiPaperwork->brand_id = $request->brand_id;
        
        // Pulisci i campi del match AI perché non sono più validi con il nuovo brand
        if ($aiPaperwork->ai_extracted_paperwork) {
            // Decodifica il JSON in array
            $extractedPaperwork = json_decode($aiPaperwork->ai_extracted_paperwork, true);
            
            if ($extractedPaperwork && is_array($extractedPaperwork)) {
                // Rimuovi i campi del match AI
                unset($extractedPaperwork['brand_override']);
                unset($extractedPaperwork['matched_product']);
                unset($extractedPaperwork['matched_brand']);
                unset($extractedPaperwork['original_brand_id']);
                unset($extractedPaperwork['product_id']);
                
                // Aggiorna il brand_id nell'ai_extracted_paperwork con il nuovo brand
                $extractedPaperwork['brand_id'] = $request->brand_id;
                
                // Ricodifica in JSON
                $aiPaperwork->ai_extracted_paperwork = json_encode($extractedPaperwork);
            }
        }
        
        // Aggiorna la cronologia dei trasferimenti
        $transfersHistory = $aiPaperwork->transfers_history ?? [];
        
        // Aggiungi il nuovo trasferimento alla cronologia
        $transfersHistory[] = [
            'from_brand_id' => $previousBrandId,
            'to_brand_id' => $request->brand_id,
            'transferred_at' => now()->toDateTimeString(),
            'transferred_by' => $request->user()->id ?? null,
        ];
        
        $aiPaperwork->transfers_history = $transfersHistory;
        
        $aiPaperwork->save();

        return response()->json([
            'message' => 'AI Paperwork trasferita con successo',
            'previous_brand_id' => $previousBrandId,
            'new_brand_id' => $request->brand_id,
            'ai_paperwork' => $aiPaperwork
        ]);
    }
}
