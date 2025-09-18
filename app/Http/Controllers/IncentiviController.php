<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incentivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class IncentiviController extends Controller
{
    /**
     * Crea un nuovo incentivo
     */
    public function createIncentive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'periodoBolletta' => 'required|string|max:255',
            'prezzoMedioKwh' => 'required|numeric|min:0',
            'spesaBollettaMensile' => 'required|numeric|min:0',
            'hasPanels' => 'required|string|max:255',
            'citta' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nominativo' => 'required|string|max:255',
            'numeroDiTelefono' => 'required|string|max:255',
            'privacyAccepted' => 'required|boolean',
            'provincia' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errori di validazione',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $incentivo = Incentivo::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Incentivo creato con successo',
                'data' => $incentivo
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'incentivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
