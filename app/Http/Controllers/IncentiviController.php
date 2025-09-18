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
            'kwhSpesi' => 'required|numeric|min:0',
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
            // Calcolo dell'incentivo
            $periodoBolletta = $request->input('periodoBolletta');
            $kwhSpesi = $request->input('kwhSpesi');
            $spesaBollettaMensile = $request->input('spesaBollettaMensile');
            $hasPanels = $request->input('hasPanels');
            
            // Determinare z in base al periodo bolletta
            $z = (strtolower($periodoBolletta) === 'mensile') ? 12 : 6;
            
            // Calcolo x = (kwhSpesi * z)/2
            $x = ($kwhSpesi * $z) / 2;
            
            // Calcolo y = x * 20
            $y = $x * 20;
            
            // Determinare w in base alla presenza di pannelli
            $w = (strtolower($hasPanels) === 'has') ? 0.10 : 0.1057;
            
            // Calcolo finale incentivo = y * w
            $incentivoCalcolato = $y * $w;
            
            // Preparare i dati per il salvataggio includendo l'incentivo calcolato
            $datiIncentivo = $request->all();
            $datiIncentivo['incentivo'] = round($incentivoCalcolato, 2); // Arrotondato a 2 decimali
            
            $incentivo = Incentivo::create($datiIncentivo);

            return response()->json([
                'success' => true,
                'message' => 'Incentivo creato con successo',
                'data' => $incentivo,
                'calcolo' => [
                    'kwh_spesi_periodo' => round($kwhSpesi, 2),
                    'z' => $z,
                    'x' => round($x, 2),
                    'y' => round($y, 2),
                    'w' => $w,
                    'incentivo_finale' => round($incentivoCalcolato, 2)
                ]
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
