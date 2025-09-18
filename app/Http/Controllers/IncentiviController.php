<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incentivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

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

    /**
     * Recupera tutti gli incentivi con paginazione e filtri
     */
    public function getIncentive(Request $request)
    {
        // Controllo che l'utente sia admin
        if (!$request->user()->hasRole(['gestione', 'backoffice', 'amministrazione'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('itemsPerPage', 10);
        
        $incentivi = Incentivo::query();

        // Filtro per tipo
        if ($request->filled('type')) {
            $type = $request->get('type');
            
            switch ($type) {
                case 'Producer':
                    $incentivi->where('hasPanels', 'has');
                    break;
                case 'Consumer':
                    $incentivi->where('hasPanels', 'wants');
                    break;
                case 'all':
                default:
                    // Nessun filtro, mostra tutti
                    break;
            }
        }

        // Filtro per nome
        if ($request->filled('nome')) {
            $incentivi->where('nominativo', 'like', '%' . $request->get('nome') . '%');
        }

        // Filtro per email
        if ($request->filled('email')) {
            $incentivi->where('email', 'like', '%' . $request->get('email') . '%');
        }

        // Filtro per telefono
        if ($request->filled('telefono')) {
            $incentivi->where('numeroDiTelefono', 'like', '%' . $request->get('telefono') . '%');
        }

        // Filtro per città
        if ($request->filled('citta')) {
            $incentivi->where('citta', 'like', '%' . $request->get('citta') . '%');
        }

        // Filtro per provincia
        if ($request->filled('provincia')) {
            $incentivi->where('provincia', 'like', '%' . $request->get('provincia') . '%');
        }

        // Filtro per tipo (mantengo la logica esistente per compatibilità)
        if ($request->filled('tipo')) {
            $incentivi->where('hasPanels', $request->get('tipo'));
        }

        // Filtro per range incentivo
        if ($request->filled('incentivo_min')) {
            $incentivi->where('incentivo', '>=', $request->get('incentivo_min'));
        }

        if ($request->filled('incentivo_max')) {
            $incentivi->where('incentivo', '<=', $request->get('incentivo_max'));
        }

        // Ricerca per nominativo, email o città (mantengo per compatibilità)
        if ($request->filled('q')) {
            $search = $request->get('q');
            $incentivi->where(function ($query) use ($search) {
                $query->where('nominativo', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('citta', 'like', "%{$search}%");
            });
        }

        // Ordinamento
        if ($request->get('sortBy')) {
            $incentivi->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        } else {
            $incentivi->orderBy('created_at', 'desc');
        }

        // Export Excel se richiesto
        if ($request->has('export')) {
            $allIncentivi = $incentivi->get();
            $csvPath = $this->transformIncentiviToCSV($allIncentivi);

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
            }, 'alfacom_solar_lead_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        }

        $incentivi = $incentivi->paginate($perPage);

        return response()->json([
            'incentivi' => $incentivi->getCollection(),
            'totalPages' => $incentivi->lastPage(),
            'totalIncentivi' => $incentivi->total(),
            'page' => $incentivi->currentPage()
        ]);
    }

    /**
     * Trasforma gli incentivi in CSV
     */
    private function transformIncentiviToCSV($incentivi)
    {
        $headers = [
            'ID',
            'Nominativo',
            'Email',
            'Telefono',
            'Città',
            'Provincia',
            'Periodo Bolletta',
            'kWh Spesi',
            'Spesa Bolletta Mensile',
            'Ha Pannelli',
            'Tipo',
            'Incentivo (€)',
            'Privacy Accettata',
            'Data Creazione',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'incentivi_csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($incentivi as $incentivo) {
            fputcsv($fp, [
                $incentivo->id,
                $incentivo->nominativo,
                $incentivo->email,
                $incentivo->numeroDiTelefono,
                $incentivo->citta,
                $incentivo->provincia,
                $incentivo->periodoBolletta,
                $incentivo->kwhSpesi,
                $incentivo->spesaBollettaMensile,
                $incentivo->hasPanels === 'has' ? 'Ha Pannelli' : 'Vuole Pannelli',
                $incentivo->hasPanels === 'has' ? 'Producer' : 'Consumer',
                number_format($incentivo->incentivo, 2, ',', '.'),
                $incentivo->privacyAccepted ? 'Sì' : 'No',
                $incentivo->created_at->format('d/m/Y H:i'),
            ]);
        }
        fclose($fp);

        return $csvPath;
    }

    /**
     * Elimina un incentivo
     */
    public function deleteIncentive(Request $request, $id): JsonResponse
    {
        // Controllo che l'utente sia admin
        if (!$request->user()->hasRole(['gestione', 'amministrazione'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $incentivo = Incentivo::findOrFail($id);
            $incentivo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incentivo eliminato con successo'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Incentivo non trovato'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'incentivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
