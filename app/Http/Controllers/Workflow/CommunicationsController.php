<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommunicationsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $communications = new \App\Models\Communication();

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
            'documents.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        $communication = new \App\Models\Communication;

        $communication->fill($request->all());

        $communication->save();

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
            
            // Ottieni tutti gli utenti con le comunicazioni abilitate
            $users = \App\Models\User::where('communications_enabled', 1)->get();
            
            if ($users->count() > 0) {
                // Prendi il primo utente come destinatario principale
                $primaryUser = $users->first();
                $bccUsers = $users->slice(1)->pluck('email')->toArray();
                
                // Invia una sola email con BCC a tutti gli altri
                $mail = \Mail::to($primaryUser->email);
                
                if (!empty($bccUsers)) {
                    $mail->bcc($bccUsers);
                }
                
                $mail->send(new \App\Mail\CommunicationEmail($communication));
                
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
        $communication = \App\Models\Communication::findOrFail($id);

        if (!$communication) {
            return response()->json(['error' => 'Communication not found'], 404);
        }

        return response()->json($communication);
    }

    public function update(Request $request, $id)
    {
        $communication = \App\Models\Communication::findOrFail($id);

        $communication->fill($request->all());

        $communication->save();

        return response()->json($communication);
    }
}
