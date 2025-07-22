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
            
            if ($aiPaperwork->status === 1) {
                return response()->json([
                    'message' => 'This document has already been processed',
                    'ai_paperwork' => $aiPaperwork
                ], 400);
            }

            $processedAiPaperwork = $this->contractProcessingService->processContract($aiPaperwork);

            return response()->json([
                'message' => 'Document processed successfully',
                'ai_paperwork' => $processedAiPaperwork
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $aiPaperwork = AIPaperwork::with('user')->findOrFail($id);

        return response()->json([
            'id' => $aiPaperwork->id,
            'user_id' => $aiPaperwork->user_id,
            'filepath' => $aiPaperwork->filepath,
            'status' => $aiPaperwork->status,
            'extracted_text' => $aiPaperwork->extracted_text,
            'ai_extracted_customer' => $aiPaperwork->ai_extracted_customer,
            'ai_extracted_paperwork' => $aiPaperwork->ai_extracted_paperwork,
            'prompt_output' => $aiPaperwork->prompt_output,
            'created_at' => $aiPaperwork->created_at,
            'updated_at' => $aiPaperwork->updated_at,
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
            
            // Controllo telefono fisso
            if (!empty($customerData['phone'])) {
                $phoneNumber = $customerData['phone'];
                
                // Controllo se il numero esiste già come telefono fisso in altri clienti
                $existingPhone = \App\Models\Customer::where('phone', $phoneNumber)
                    ->whereNull('deleted_at');
                    
                // Se customerId è presente, escludilo dalla ricerca
                if ($customerId) {
                    $existingPhone = $existingPhone->where('id', '!=', $customerId);
                }
                
                $existingPhone = $existingPhone->first();
                    
                if ($existingPhone) {
                    $errors['phone'] = 'Questo telefono fisso è già associato a un altro cliente';
                }
                
                // Controllo incrociato: se il numero esiste già come cellulare in altri clienti
                $existingMobile = \App\Models\Customer::where('mobile', $phoneNumber)
                    ->whereNull('deleted_at');
                    
                // Se customerId è presente, escludilo dalla ricerca
                if ($customerId) {
                    $existingMobile = $existingMobile->where('id', '!=', $customerId);
                }
                
                $existingMobile = $existingMobile->first();
                    
                if ($existingMobile) {
                    $errors['phone'] = 'Questo numero è già registrato come cellulare di un altro cliente';
                }
            }
            
            // Controllo cellulare
            if (!empty($customerData['mobile'])) {
                $mobileNumber = $customerData['mobile'];
                
                // Controllo se il numero esiste già come cellulare in altri clienti
                $existingMobile = \App\Models\Customer::where('mobile', $mobileNumber)
                    ->whereNull('deleted_at');
                    
                // Se customerId è presente, escludilo dalla ricerca
                if ($customerId) {
                    $existingMobile = $existingMobile->where('id', '!=', $customerId);
                }
                
                $existingMobile = $existingMobile->first();
                    
                if ($existingMobile) {
                    $errors['mobile'] = 'Questo cellulare è già associato a un altro cliente';
                }
                
                // Controllo incrociato: se il numero esiste già come telefono fisso in altri clienti
                $existingPhone = \App\Models\Customer::where('phone', $mobileNumber)
                    ->whereNull('deleted_at');
                    
                // Se customerId è presente, escludilo dalla ricerca
                if ($customerId) {
                    $existingPhone = $existingPhone->where('id', '!=', $customerId);
                }
                
                $existingPhone = $existingPhone->first();
                    
                if ($existingPhone) {
                    $errors['mobile'] = 'Questo numero è già registrato come telefono fisso di un altro cliente';
                }
            }
            
            // Controllo che telefono e cellulare non siano uguali
            if (!empty($customerData['phone']) && !empty($customerData['mobile']) && $customerData['phone'] === $customerData['mobile']) {
                $errors['mobile'] = 'Telefono fisso e cellulare non possono essere uguali';
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
            $aiPaperwork->ai_extracted_paperwork = json_encode($request->ai_extracted_paperwork);
            
            // Se è stato modificato il product_id, facciamo un aggiornamento automatico del brand_id
            $paperworkData = $request->ai_extracted_paperwork;
            if (isset($paperworkData['product_id']) && $paperworkData['product_id']) {
                $product = \App\Models\Product::find($paperworkData['product_id']);
                if ($product && $product->brand_id) {
                    $aiPaperwork->brand_id = $product->brand_id;
                }
            }
        }

        $aiPaperwork->save();

        return response()->json($aiPaperwork);
    }

    public function confirm(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $aiPaperwork = AIPaperwork::findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            // Extract data
            $customerData = json_decode($aiPaperwork->ai_extracted_customer, true) ?: [];
            $paperworkData = json_decode($aiPaperwork->ai_extracted_paperwork, true) ?: [];

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

            // Map customer data
            $customer->fill([
                'name' => $customerData['name'] ?? null,
                'last_name' => $customerData['last_name'] ?? null,
                'email' => $customerData['email'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'mobile' => $customerData['mobile'] ?? null,
                'address' => $customerData['address'] ?? null,
                'city' => $customerData['city'] ?? null,
                'zip_code' => $customerData['zip_code'] ?? null,
                'province' => $customerData['province'] ?? null,
                'region' => $customerData['region'] ?? null,
                'tax_id_code' => $customerData['tax_id_code'] ?? null,
                'vat_number' => $customerData['vat_number'] ?? null,
            ]);

            $customer->save();

            // Create paperwork
            $paperwork = new \App\Models\Paperwork;
            $paperwork->fill([
                'customer_id' => $customer->id,
                'user_id' => $aiPaperwork->user_id,
                'product_id' => $request->product_id,
                'account_pod_pdr' => $paperworkData['account_pod_pdr'] ?? null,
                'annual_consumption' => $paperworkData['annual_consumption'] ?? null,
                'contract_type' => $paperworkData['contract_type'] ?? null,
                'category' => $paperworkData['category'] ?? null,
                'type' => $paperworkData['type'] ?? null,
                'previous_provider' => $paperworkData['previous_provider'] ?? null,
            ]);

            $paperwork->created_by = $request->user()->id;
            $paperwork->save();

            // Link document
            $doc = new \App\Models\PaperworkDocument;
            $doc->paperwork_id = $paperwork->id;
            $doc->name = basename($aiPaperwork->filepath);
            $doc->url = $aiPaperwork->filepath;
            $doc->save();

            // Get the brand_id from the selected product and update AI paperwork
            $product = \App\Models\Product::find($request->product_id);
            if ($product && $product->brand_id) {
                $aiPaperwork->brand_id = $product->brand_id;
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
}
