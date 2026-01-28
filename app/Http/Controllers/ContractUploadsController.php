<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AIPaperwork;
use App\Models\User;
use App\Services\AIPaperworkAssignment;
use Illuminate\Support\Facades\Storage;

class ContractUploadsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contract' => 'required|file|mimes:pdf|max:20480', // 20MB max
            'brand_id' => 'required|exists:brands,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $file = $request->file('contract');

            // Store file in Digital Ocean Spaces with original name
            $path = Storage::disk('do')->putFileAs(
                'ai_contracts',
                $file,
                $file->getClientOriginalName()
            );

            // Calcola il backoffice a cui assegnare la pratica AI appena caricata.
            // 1) Se chi carica è un backoffice, assegniamo direttamente a lui (accettata).
            // 2) Se è stato selezionato un utente con ruolo backoffice nel campo user_id,
            //    assegniamo staticamente quella pratica a quel backoffice (accepted).
            // 3) In tutti gli altri casi, usiamo la logica di bilanciamento per brand.
            if ($request->user() && $request->user()->hasRole('backoffice')) {
                $assignment = [
                    'assigned_backoffice_id' => $request->user()->id,
                    'assignment_status' => 'accept',
                    // Anche se per le pratiche già accettate il timeout non è strettamente necessario,
                    // lo valorizziamo per coerenza con il modello dati.
                    'assignment_expires_at' => now()->addMinutes(15),
                ];
            } elseif ($request->filled('user_id')) {
                $selectedUser = User::find($request->user_id);

                if ($selectedUser && $selectedUser->hasRole('backoffice')) {
                    // L'admin/gestione ha scelto esplicitamente un backoffice:
                    // la pratica viene assegnata in modo statico a lui e considerata già accettata.
                    $assignment = [
                        'assigned_backoffice_id' => $selectedUser->id,
                        'assignment_status' => 'accept',
                        'assignment_expires_at' => now()->addMinutes(15),
                    ];
                } else {
                    $assignment = AIPaperworkAssignment::assignToBackofficeByBrand($request->brand_id);
                }
            } else {
                $assignment = AIPaperworkAssignment::assignToBackofficeByBrand($request->brand_id);
            }

            // Create new AI Paperwork record
            // Usa l'agente selezionato nel form, altrimenti fallback all'utente corrente
            $aiPaperwork = AIPaperwork::create(array_merge([
                'user_id' => $request->user_id ?? auth()->id(),
                'brand_id' => $request->brand_id,
                'filepath' => $path,
                'original_filename' => basename($path),
                'status' => 0, // Pending
            ], $assignment));

            return response()->json([
                'message' => 'Contract uploaded successfully',
                'id' => $aiPaperwork->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload contract: ' . $e->getMessage()
            ], 500);
        }
    }
} 
