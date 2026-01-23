<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommunicationsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $communications = \App\Models\Communication::with(['documents', 'brands']);

        if ($request->get('sortBy')) {
            $communications = $communications->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $communications = $communications->orderBy('created_at', 'desc');
        }

        $communications = $communications->paginate($perPage);

        return response()->json([
            'communications' => $communications->getCollection(),
            'totalPages' => $communications->lastPage(),
            'totalCommunications' => $communications->total(),
            'page' => $communications->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        // Validazione
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'send_email' => 'nullable|in:true,false,1,0',
            // I brand arrivano come brand_ids[] dal frontend
            'brand_ids' => 'required|array|min:1',
            'brand_ids.*' => 'integer|exists:brands,id',
            'documents.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        $communication = new \App\Models\Communication;

        // Non vogliamo fare fill di brand_ids perché non è una colonna della tabella communications
        $communication->fill($request->except(['brand_ids']));

        $communication->save();

        // Salva i brand nella tabella pivot communication_brands
        $brandIds = $request->input('brand_ids', []);
        foreach ($brandIds as $brandId) {
            \App\Models\CommunicationBrand::create([
                'id_communication' => $communication->id,
                'id_brand' => $brandId,
            ]);
        }

        // Gestione upload documenti
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    // Upload file su Digital Ocean Spaces
                    $scope = 'communications/' . $communication->id;
                    $path = $file->storeAs($scope, $file->getClientOriginalName(), [
                        'disk' => 'do',
                        'visibility' => 'private'
                    ]);

                    // Salva il documento nella tabella communications_documents
                    $document = new \App\Models\CommunicationDocument;
                    $document->communication_id = $communication->id;
                    $document->name = $file->getClientOriginalName();
                    $document->url = $path;
                    $document->save();
                } catch (\Exception $e) {
                    // Log dell'errore ma continua con gli altri file
                    \Log::error('Error uploading communication document: ' . $e->getMessage());
                }
            }
        }

        // Converti send_email in booleano (form-data invia come stringa)
        $sendEmail = filter_var($request->get('send_email'), FILTER_VALIDATE_BOOLEAN);
        
        if ($sendEmail) {
            // Carica i documenti prima di inviare le email
            $communication->load('documents');
            
            // Ottieni i brand associati alla comunicazione
            $communicationBrandIds = $brandIds; // Usa i brand_ids già salvati
            
            // Trova gli utenti che hanno:
            // 1. communications_enabled = 1
            // 2. E che hanno almeno uno dei brand associati alla comunicazione
            $users = \App\Models\User::where('communications_enabled', 1)
                ->whereHas('brands', function ($query) use ($communicationBrandIds) {
                    $query->whereIn('brands.id', $communicationBrandIds);
                })
                ->get();
            
            if ($users->count() > 0) {
                // Limite di destinatari per email (50 totali: 1 TO + 49 BCC)
                $maxRecipientsPerEmail = 50;
                
                // Dividi gli utenti in batch di 50
                $userChunks = $users->chunk($maxRecipientsPerEmail);
                
                foreach ($userChunks as $chunk) {
                    $chunkUsers = $chunk->values();
                    $primaryUser = $chunkUsers->first();
                    $bccUsers = $chunkUsers->slice(1)->pluck('email')->toArray();
                    
                    try {
                        $mail = \Mail::to($primaryUser->email);
                        
                        if (!empty($bccUsers)) {
                            $mail->bcc($bccUsers);
                        }
                        
                        $mail->send(new \App\Mail\CommunicationEmail($communication));
                    } catch (\Exception $e) {
                        // Log dell'errore ma continua con gli altri batch
                        \Log::error('Error sending communication email batch: ' . $e->getMessage());
                    }
                }
                
                $communication->sent_at = now();
                $communication->save();
            }
        }

        // Carica i documenti nella risposta
        $communication->load('documents');

        return response()->json($communication, 201);
    }

    public function show(Request $request, $id)
    {
        $communication = \App\Models\Communication::with(['documents', 'brands'])->findOrFail($id);

        if (!$communication) {
            return response()->json(['error' => 'Communication not found'], 404);
        }

        return response()->json($communication);
    }

    public function downloadDocument(Request $request, $id, $documentId)
    {
        $communication = \App\Models\Communication::find($id);

        if (!$communication) {
            return response()->json(['error' => 'Communication not found'], 404);
        }

        $document = \App\Models\CommunicationDocument::find($documentId);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Verifica che il documento appartenga alla comunicazione
        if ($document->communication_id != $communication->id) {
            return response()->json(['error' => 'Document does not belong to this communication'], 403);
        }

        // Genera URL temporaneo valido per 24 ore
        $expirationMinutes = 60 * 24; // 24 ore
        $temporaryUrl = \Storage::disk('do')->temporaryUrl(
            $document->url,
            now()->addMinutes($expirationMinutes)
        );

        return response()->json([
            'downloadUrl' => $temporaryUrl,
            'fileName' => $document->name,
            'expiresAt' => now()->addMinutes($expirationMinutes)->toIso8601String(),
            'expiresInMinutes' => $expirationMinutes,
        ]);
    }

    public function downloadAllDocuments(Request $request, $id)
    {
        $communication = \App\Models\Communication::with('documents')->find($id);

        if (!$communication) {
            return response()->json(['error' => 'Communication not found'], 404);
        }

        if ($communication->documents->count() === 0) {
            return response()->json(['error' => 'No documents found for this communication'], 404);
        }

        // Genera URL temporanei per tutti i documenti (validi 24 ore)
        $expirationMinutes = 60 * 24; // 24 ore
        $documents = [];

        foreach ($communication->documents as $document) {
            try {
                $temporaryUrl = \Storage::disk('do')->temporaryUrl(
                    $document->url,
                    now()->addMinutes($expirationMinutes)
                );

                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'downloadUrl' => $temporaryUrl,
                ];
            } catch (\Exception $e) {
                \Log::error('Error generating temporary URL for document: ' . $e->getMessage());
            }
        }

        return response()->json([
            'documents' => $documents,
            'count' => count($documents),
            'expiresAt' => now()->addMinutes($expirationMinutes)->toIso8601String(),
            'expiresInMinutes' => $expirationMinutes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $communication = \App\Models\Communication::findOrFail($id);

        // Validazione solo dei campi eventualmente passati
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'subject' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'send_email' => 'nullable|in:true,false,1,0',
            // I brand arrivano come brand_ids[] dal frontend
            'brand_ids' => 'sometimes|required|array|min:1',
            'brand_ids.*' => 'integer|exists:brands,id',
        ]);

        // Evitiamo di fare fill di brand_ids perché non è una colonna della tabella communications
        $communication->fill($request->except(['brand_ids']));

        $communication->save();

        // Se arrivano brand_ids, aggiorniamo le relazioni nella tabella communication_brands
        if ($request->has('brand_ids')) {
            $brandIds = $request->input('brand_ids', []);

            // Cancella le associazioni esistenti per questa comunicazione
            \App\Models\CommunicationBrand::where('id_communication', $communication->id)->delete();

            // Crea le nuove associazioni
            foreach ($brandIds as $brandId) {
                \App\Models\CommunicationBrand::create([
                    'id_communication' => $communication->id,
                    'id_brand' => $brandId,
                ]);
            }
        } else {
            // Se non sono stati inviati brand_ids, recupera quelli già associati
            $brandIds = \App\Models\CommunicationBrand::where('id_communication', $communication->id)
                ->pluck('id_brand')
                ->toArray();
        }

        // Gestione invio email anche in update
        $sendEmail = filter_var($request->get('send_email'), FILTER_VALIDATE_BOOLEAN);

        if ($sendEmail && !empty($brandIds)) {
            // Carica i documenti prima di inviare le email
            $communication->load('documents');

            // Trova gli utenti che hanno:
            // 1. communications_enabled = 1
            // 2. E che hanno almeno uno dei brand associati alla comunicazione
            $users = \App\Models\User::where('communications_enabled', 1)
                ->whereHas('brands', function ($query) use ($brandIds) {
                    $query->whereIn('brands.id', $brandIds);
                })
                ->get();

            if ($users->count() > 0) {
                // Limite di destinatari per email (50 totali: 1 TO + 49 BCC)
                $maxRecipientsPerEmail = 50;
                
                // Dividi gli utenti in batch di 50
                $userChunks = $users->chunk($maxRecipientsPerEmail);
                
                foreach ($userChunks as $chunk) {
                    $chunkUsers = $chunk->values();
                    $primaryUser = $chunkUsers->first();
                    $bccUsers = $chunkUsers->slice(1)->pluck('email')->toArray();
                    
                    try {
                        $mail = \Mail::to($primaryUser->email);
                        
                        if (!empty($bccUsers)) {
                            $mail->bcc($bccUsers);
                        }
                        
                        $mail->send(new \App\Mail\CommunicationEmail($communication));
                    } catch (\Exception $e) {
                        // Log dell'errore ma continua con gli altri batch
                        \Log::error('Error sending communication email batch: ' . $e->getMessage());
                    }
                }

                $communication->sent_at = now();
                $communication->save();
            }
        }

        return response()->json($communication);
    }
}
