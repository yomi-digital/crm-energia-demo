<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rules\DettagliConsumoJsonRule;
use App\Rules\BusinessPlanAnnoSimulazioneRule;
use App\Rules\BonificoDataRule;
use App\Rules\FinanziamentoDataRule;
use App\Rules\MaintenanceCostRule;
use App\Rules\AssicurazioneCostRule;
use App\Rules\StrictPositiveNumberRule;
use App\Rules\StrictNonNegativeRule;
use Illuminate\Validation\Rule;
use Closure;
use App\Rules\StrictNumberRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Preventivo;
use App\Models\ConsumoPreventivo;
use App\Models\DettaglioProdottoPreventivo;
use App\Models\PreventivoVoceEconomica;
use App\Models\DettaglioBusinessPlan;
use App\Services\PreventivoPdfService;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

class PreventivoController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'itemsPerPage' => ['nullable', 'integer', 'min:1'],
                'stato' => ['nullable', 'string'],
                'customerId' => ['nullable', 'integer'],
                'agentId' => ['nullable', 'integer'],
                'q' => ['nullable', 'string'],
                'sortBy' => ['nullable', 'string', Rule::in(['data_preventivo', 'created_at', 'updated_at', 'numero_preventivo'])],
                'orderBy' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
                'dataFrom' => ['nullable', 'date'],
                'dataTo' => ['nullable', 'date'],
                'roof' => ['nullable', 'string'],
                'exposition' => ['nullable', 'string'],
                'geoArea' => ['nullable', 'string'],
                'paymentMode' => ['nullable', 'string', Rule::in(['bonifico', 'finanziamento', 'bonifico,finanziamento'])],
                'maintenanceOption' => ['nullable', 'string', Rule::in(['si', 'no'])],
                'insuranceOption' => ['nullable', 'string', Rule::in(['si', 'no'])],
                'maintenanceCostMin' => ['nullable', 'numeric', 'gte:0'],
                'maintenanceCostMax' => ['nullable', 'numeric', 'gte:0'],
                'insuranceCostMin' => ['nullable', 'numeric', 'gte:0'],
                'insuranceCostMax' => ['nullable', 'numeric', 'gte:0'],
                'productionEstimatedMin' => ['nullable', 'numeric', 'gte:0'],
                'productionEstimatedMax' => ['nullable', 'numeric', 'gte:0'],
                'selfConsumptionSavingMin' => ['nullable', 'numeric', 'gte:0'],
                'selfConsumptionSavingMax' => ['nullable', 'numeric', 'gte:0'],
                'excessSaleRidMin' => ['nullable', 'numeric', 'gte:0'],
                'excessSaleRidMax' => ['nullable', 'numeric', 'gte:0'],
                'cerIncentiveMin' => ['nullable', 'numeric', 'gte:0'],
                'cerIncentiveMax' => ['nullable', 'numeric', 'gte:0'],
                'taxDeductionMin' => ['nullable', 'numeric', 'gte:0'],
                'taxDeductionMax' => ['nullable', 'numeric', 'gte:0'],
            ],
            [
                'itemsPerPage.integer' => 'Il parametro itemsPerPage deve essere un numero intero.',
                'itemsPerPage.min' => 'Il parametro itemsPerPage deve essere almeno 1.',
                'stato.string' => 'Il parametro stato deve essere una stringa.',
                'customerId.integer' => 'Il parametro customerId deve essere numerico.',
                'agentId.integer' => 'Il parametro agentId deve essere numerico.',
                'q.string' => 'Il parametro q deve essere una stringa.',
                'sortBy.in' => 'Il parametro sortBy non è supportato.',
                'orderBy.in' => 'Il parametro orderBy deve essere asc o desc.',
                'dataFrom.date' => 'Il parametro dataFrom deve essere una data valida.',
                'dataTo.date' => 'Il parametro dataTo deve essere una data valida.',
                'roof.string' => 'Il parametro roof deve essere una stringa.',
                'exposition.string' => 'Il parametro exposition deve essere una stringa.',
                'geoArea.string' => 'Il parametro geoArea deve essere una stringa.',
                'paymentMode.in' => 'Il parametro paymentMode deve essere uno tra: bonifico, finanziamento, bonifico,finanziamento.',
                'maintenanceOption.in' => 'Il parametro maintenanceOption deve essere uno tra: si, no.',
                'insuranceOption.in' => 'Il parametro insuranceOption deve essere uno tra: si, no.',
                'maintenanceCostMin.numeric' => 'Il parametro maintenanceCostMin deve essere numerico.',
                'maintenanceCostMin.gte' => 'Il parametro maintenanceCostMin deve essere maggiore o uguale a 0.',
                'maintenanceCostMax.numeric' => 'Il parametro maintenanceCostMax deve essere numerico.',
                'maintenanceCostMax.gte' => 'Il parametro maintenanceCostMax deve essere maggiore o uguale a 0.',
                'insuranceCostMin.numeric' => 'Il parametro insuranceCostMin deve essere numerico.',
                'insuranceCostMin.gte' => 'Il parametro insuranceCostMin deve essere maggiore o uguale a 0.',
                'insuranceCostMax.numeric' => 'Il parametro insuranceCostMax deve essere numerico.',
                'insuranceCostMax.gte' => 'Il parametro insuranceCostMax deve essere maggiore o uguale a 0.',
                'productionEstimatedMin.numeric' => 'Il parametro productionEstimatedMin deve essere numerico.',
                'productionEstimatedMin.gte' => 'Il parametro productionEstimatedMin deve essere maggiore o uguale a 0.',
                'productionEstimatedMax.numeric' => 'Il parametro productionEstimatedMax deve essere numerico.',
                'productionEstimatedMax.gte' => 'Il parametro productionEstimatedMax deve essere maggiore o uguale a 0.',
                'selfConsumptionSavingMin.numeric' => 'Il parametro selfConsumptionSavingMin deve essere numerico.',
                'selfConsumptionSavingMin.gte' => 'Il parametro selfConsumptionSavingMin deve essere maggiore o uguale a 0.',
                'selfConsumptionSavingMax.numeric' => 'Il parametro selfConsumptionSavingMax deve essere numerico.',
                'selfConsumptionSavingMax.gte' => 'Il parametro selfConsumptionSavingMax deve essere maggiore o uguale a 0.',
                'excessSaleRidMin.numeric' => 'Il parametro excessSaleRidMin deve essere numerico.',
                'excessSaleRidMin.gte' => 'Il parametro excessSaleRidMin deve essere maggiore o uguale a 0.',
                'excessSaleRidMax.numeric' => 'Il parametro excessSaleRidMax deve essere numerico.',
                'excessSaleRidMax.gte' => 'Il parametro excessSaleRidMax deve essere maggiore o uguale a 0.',
                'cerIncentiveMin.numeric' => 'Il parametro cerIncentiveMin deve essere numerico.',
                'cerIncentiveMin.gte' => 'Il parametro cerIncentiveMin deve essere maggiore o uguale a 0.',
                'cerIncentiveMax.numeric' => 'Il parametro cerIncentiveMax deve essere numerico.',
                'cerIncentiveMax.gte' => 'Il parametro cerIncentiveMax deve essere maggiore o uguale a 0.',
                'taxDeductionMin.numeric' => 'Il parametro taxDeductionMin deve essere numerico.',
                'taxDeductionMin.gte' => 'Il parametro taxDeductionMin deve essere maggiore o uguale a 0.',
                'taxDeductionMax.numeric' => 'Il parametro taxDeductionMax deve essere numerico.',
                'taxDeductionMax.gte' => 'Il parametro taxDeductionMax deve essere maggiore o uguale a 0.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parametri non validi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->filled('maintenanceCostMin') && $request->filled('maintenanceCostMax')) {
            $min = (float) $request->get('maintenanceCostMin');
            $max = (float) $request->get('maintenanceCostMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'maintenanceCostMax' => ['Il parametro maintenanceCostMax deve essere maggiore o uguale a maintenanceCostMin.'],
                    ],
                ], 422);
            }
        }

        if ($request->filled('insuranceCostMin') && $request->filled('insuranceCostMax')) {
            $min = (float) $request->get('insuranceCostMin');
            $max = (float) $request->get('insuranceCostMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'insuranceCostMax' => ['Il parametro insuranceCostMax deve essere maggiore o uguale a insuranceCostMin.'],
                    ],
                ], 422);
            }
        }

        if ($request->filled('productionEstimatedMin') && $request->filled('productionEstimatedMax')) {
            $min = (float) $request->get('productionEstimatedMin');
            $max = (float) $request->get('productionEstimatedMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'productionEstimatedMax' => ['Il parametro productionEstimatedMax deve essere maggiore o uguale a productionEstimatedMin.'],
                    ],
                ], 422);
            }
        }

        if ($request->filled('selfConsumptionSavingMin') && $request->filled('selfConsumptionSavingMax')) {
            $min = (float) $request->get('selfConsumptionSavingMin');
            $max = (float) $request->get('selfConsumptionSavingMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'selfConsumptionSavingMax' => ['Il parametro selfConsumptionSavingMax deve essere maggiore o uguale a selfConsumptionSavingMin.'],
                    ],
                ], 422);
            }
        }

        if ($request->filled('excessSaleRidMin') && $request->filled('excessSaleRidMax')) {
            $min = (float) $request->get('excessSaleRidMin');
            $max = (float) $request->get('excessSaleRidMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'excessSaleRidMax' => ['Il parametro excessSaleRidMax deve essere maggiore o uguale a excessSaleRidMin.'],
                    ],
                ], 422);
            }
        }

        if ($request->filled('cerIncentiveMin') && $request->filled('cerIncentiveMax')) {
            $min = (float) $request->get('cerIncentiveMin');
            $max = (float) $request->get('cerIncentiveMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'cerIncentiveMax' => ['Il parametro cerIncentiveMax deve essere maggiore o uguale a cerIncentiveMin.'],
                    ],
                ], 422);
            }
        }

        if ($request->filled('taxDeductionMin') && $request->filled('taxDeductionMax')) {
            $min = (float) $request->get('taxDeductionMin');
            $max = (float) $request->get('taxDeductionMax');

            if ($min > $max) {
                return response()->json([
                    'message' => 'Parametri non validi.',
                    'errors' => [
                        'taxDeductionMax' => ['Il parametro taxDeductionMax deve essere maggiore o uguale a taxDeductionMin.'],
                    ],
                ], 422);
            }
        }

        $perPage = (int) $request->get('itemsPerPage', 10);

        $preventivi = Preventivo::query()->with(['cliente', 'agente']);

        if ($request->filled('stato')) {
            $preventivi->where('stato', $request->get('stato'));
        }

        if ($request->filled('customerId')) {
            $preventivi->where('fk_cliente', $request->get('customerId'));
        }

        if ($request->filled('agentId')) {
            $preventivi->where('fk_agente', $request->get('agentId'));
        }

        if ($request->filled('dataFrom')) {
            $preventivi->whereDate('data_preventivo', '>=', $request->get('dataFrom'));
        }

        if ($request->filled('dataTo')) {
            $preventivi->whereDate('data_preventivo', '<=', $request->get('dataTo'));
        }

        if ($request->filled('roof')) {
            $roof = $request->get('roof');
            $preventivi->where('tetto_salvato', 'like', '%' . $roof . '%');
        }

        if ($request->filled('exposition')) {
            $exposition = $request->get('exposition');
            $preventivi->where('esposizione_salvata', 'like', '%' . $exposition . '%');
        }

        if ($request->filled('geoArea')) {
            $geoArea = $request->get('geoArea');
            $preventivi->where('area_geografica_salvata', 'like', '%' . $geoArea . '%');
        }

        if ($request->filled('paymentMode')) {
            $preventivi->where('modalita_pagamento_salvata', $request->get('paymentMode'));
        }

        if ($request->filled('maintenanceOption')) {
            $preventivi->where('opzione_manutenzione_salvata', $request->get('maintenanceOption'));
        }

        if ($request->filled('insuranceOption')) {
            $preventivi->where('opzione_assicurazione_salvata', $request->get('insuranceOption'));
        }

        if ($request->filled('maintenanceCostMin')) {
            $preventivi->where('opzione_manutenzione_salvata', 'si')
                ->where('costo_annuo_manutenzione_salvato', '>=', (float) $request->get('maintenanceCostMin'));
        }

        if ($request->filled('maintenanceCostMax')) {
            $preventivi->where('opzione_manutenzione_salvata', 'si')
                ->where('costo_annuo_manutenzione_salvato', '<=', (float) $request->get('maintenanceCostMax'));
        }

        if ($request->filled('insuranceCostMin')) {
            $preventivi->where('opzione_assicurazione_salvata', 'si')
                ->where('costo_annuo_assicurazione_salvato', '>=', (float) $request->get('insuranceCostMin'));
        }

        if ($request->filled('insuranceCostMax')) {
            $preventivi->where('opzione_assicurazione_salvata', 'si')
                ->where('costo_annuo_assicurazione_salvato', '<=', (float) $request->get('insuranceCostMax'));
        }

        if ($request->filled('productionEstimatedMin')) {
            $preventivi->where('produzione_annua_stimata', '>=', (float) $request->get('productionEstimatedMin'));
        }

        if ($request->filled('productionEstimatedMax')) {
            $preventivi->where('produzione_annua_stimata', '<=', (float) $request->get('productionEstimatedMax'));
        }

        if ($request->filled('selfConsumptionSavingMin')) {
            $preventivi->where('risparmio_autoconsumo_annuo', '>=', (float) $request->get('selfConsumptionSavingMin'));
        }

        if ($request->filled('selfConsumptionSavingMax')) {
            $preventivi->where('risparmio_autoconsumo_annuo', '<=', (float) $request->get('selfConsumptionSavingMax'));
        }

        if ($request->filled('excessSaleRidMin')) {
            $preventivi->where('vendita_eccedenze_rid_annua', '>=', (float) $request->get('excessSaleRidMin'));
        }

        if ($request->filled('excessSaleRidMax')) {
            $preventivi->where('vendita_eccedenze_rid_annua', '<=', (float) $request->get('excessSaleRidMax'));
        }

        if ($request->filled('cerIncentiveMin')) {
            $preventivi->where('incentivo_cer_annuo', '>=', (float) $request->get('cerIncentiveMin'));
        }

        if ($request->filled('cerIncentiveMax')) {
            $preventivi->where('incentivo_cer_annuo', '<=', (float) $request->get('cerIncentiveMax'));
        }

        if ($request->filled('taxDeductionMin')) {
            $preventivi->where('detrazione_fiscale_annua', '>=', (float) $request->get('taxDeductionMin'));
        }

        if ($request->filled('taxDeductionMax')) {
            $preventivi->where('detrazione_fiscale_annua', '<=', (float) $request->get('taxDeductionMax'));
        }

        if ($request->filled('q')) {
            $search = $request->get('q');
            $preventivi->where(function ($query) use ($search) {
                $query->where('numero_preventivo', 'like', "%{$search}%")
                    ->orWhere('stato', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('business_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('agente', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('sortBy')) {
            $preventivi->orderBy(
                $request->get('sortBy'),
                $request->get('orderBy', 'desc')
            );
        } else {
            $preventivi->orderByDesc('created_at');
        }

        $preventivi = $preventivi->paginate($perPage);

        if ($request->has('export')) {
            $pageEntries = $preventivi->getCollection();
            $csvPath = $this->transformEntriesToCSV($pageEntries);

            $data = array_map('str_getcsv', file($csvPath));

            return Excel::download(new class($data) implements FromCollection {
                private array $data;

                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function collection()
                {
                    return collect($this->data);
                }
            }, 'preventivi_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        }

        return response()->json([
            'preventivi' => $preventivi->getCollection(),
            'totalPages' => $preventivi->lastPage(),
            'totalPreventivi' => $preventivi->total(),
            'page' => $preventivi->currentPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentYear = (int) date('Y');
        $maxYear = $currentYear + 10;

        // Sistemazione di alcuni valori.
        $request->merge([
            'PREVENTIVI.stato' => strtolower((string) $request->input('PREVENTIVI.stato', '')),
            'PREVENTIVI.modalita_pagamento_salvata' => strtolower((string) $request->input('PREVENTIVI.modalita_pagamento_salvata', '')),
            'PREVENTIVI.esposizione_salvata' => strtoupper(str_replace(['-', '_'], ' ', (string) $request->input('PREVENTIVI.esposizione_salvata', ''))),
            'CONSUMI_PREVENTIVO.tipologia_bolletta' => strtolower((string) $request->input('CONSUMI_PREVENTIVO.tipologia_bolletta', '')),
        ]);

        $preventiviVoceEconomiche = $request->input('PREVENTIVI_VOCE_ECONOMICHE');
        if (is_array($preventiviVoceEconomiche)) {
            foreach ($preventiviVoceEconomiche as $key => $voce) {
                if (isset($voce['tipo_voce_salvata']) && is_string($voce['tipo_voce_salvata'])) {
                    $preventiviVoceEconomiche[$key]['tipo_voce_salvata'] = strtolower($voce['tipo_voce_salvata']);
                }
            }

            $request->merge([
                'PREVENTIVI_VOCE_ECONOMICHE' => $preventiviVoceEconomiche,
            ]);
        }

        $validatedData = $request->validate([
            'PREVENTIVI' => 'required|array',
            'PREVENTIVI.fk_cliente' => ['required', new StrictPositiveNumberRule('PREVENTIVI.fk_cliente', true, 1, true)],
            'PREVENTIVI.fk_agente' => ['required', new StrictPositiveNumberRule('PREVENTIVI.fk_agente', true, 1, true)],
            'PREVENTIVI.stato' => 'required|string|in:protocollato',
            'PREVENTIVI.tetto_salvato' => 'required|string|max:255',
            'PREVENTIVI.area_geografica_salvata' => 'required|string|in:sud,nord,centro,isole',
            'PREVENTIVI.esposizione_salvata' => 'required|string|in:nord,nord est,nord ovest,sud,sud est,sud ovest,est,ovest',
            'PREVENTIVI.produzione_annua_stimata' => ['required', new StrictPositiveNumberRule('PREVENTIVI.produzione_annua_stimata', false, 0, true)],
            'PREVENTIVI.risparmio_autoconsumo_annuo' => ['required', new StrictPositiveNumberRule('PREVENTIVI.risparmio_autoconsumo_annuo', false, 0, true)],
            'PREVENTIVI.vendita_eccedenze_rid_annua' => ['required', new StrictPositiveNumberRule('PREVENTIVI.vendita_eccedenze_rid_annua', false, 0, true)],
            'PREVENTIVI.incentivo_cer_annuo' => ['required', new StrictPositiveNumberRule('PREVENTIVI.incentivo_cer_annuo', false, 0, true)],
            'PREVENTIVI.detrazione_fiscale_annua' => ['nullable', new StrictPositiveNumberRule('PREVENTIVI.detrazione_fiscale_annua', false, 0, true)],
            'PREVENTIVI.modalita_pagamento_salvata' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    $allowedValues = ['bonifico', 'finanziamento', 'bonifico,finanziamento'];
                    if (!in_array(strtolower($value), $allowedValues)) {
                        $fail('Il campo PREVENTIVI.modalita_pagamento_salvata deve essere uno tra: bonifico, finanziamento, bonifico,finanziamento.');
                    }
                },
            ],
            'PREVENTIVI.bonifico_data_json' => [
                'nullable',
                'array',
                new BonificoDataRule(),
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $modalitaPagamento = strtolower($request->input('PREVENTIVI.modalita_pagamento_salvata', ''));
                    $preventiviData = $request->input('PREVENTIVI', []);
                    $hasBonificoData = isset($preventiviData['bonifico_data_json']) && !empty($preventiviData['bonifico_data_json']);
                    
                    // Se la modalità è solo "finanziamento", non deve essere presente bonifico_data_json
                    if ($modalitaPagamento === 'finanziamento' && $hasBonificoData) {
                        $fail('Il campo PREVENTIVI.bonifico_data_json non può essere presente quando la modalità di pagamento è solo finanziamento.');
                    }
                    
                    // Se la modalità è solo "bonifico", bonifico_data_json è obbligatorio
                    if ($modalitaPagamento === 'bonifico' && !$hasBonificoData) {
                        $fail('Il campo PREVENTIVI.bonifico_data_json è obbligatorio quando la modalità di pagamento è bonifico.');
                    }
                    
                    // Se la modalità è "bonifico,finanziamento", bonifico_data_json è obbligatorio
                    if ($modalitaPagamento === 'bonifico,finanziamento' && !$hasBonificoData) {
                        $fail('Il campo PREVENTIVI.bonifico_data_json è obbligatorio quando la modalità di pagamento è bonifico,finanziamento. Entrambi i dati JSON sono richiesti.');
                    }
                },
            ],
            'PREVENTIVI.finanziamento_data_json' => [
                'nullable',
                'array',
                new FinanziamentoDataRule(),
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $modalitaPagamento = strtolower($request->input('PREVENTIVI.modalita_pagamento_salvata', ''));
                    $preventiviData = $request->input('PREVENTIVI', []);
                    $hasFinanziamentoData = isset($preventiviData['finanziamento_data_json']) && !empty($preventiviData['finanziamento_data_json']);
                    
                    // Se la modalità è solo "bonifico", non deve essere presente finanziamento_data_json
                    if ($modalitaPagamento === 'bonifico' && $hasFinanziamentoData) {
                        $fail('Il campo PREVENTIVI.finanziamento_data_json non può essere presente quando la modalità di pagamento è solo bonifico.');
                    }
                    
                    // Se la modalità è solo "finanziamento", finanziamento_data_json è obbligatorio
                    if ($modalitaPagamento === 'finanziamento' && !$hasFinanziamentoData) {
                        $fail('Il campo PREVENTIVI.finanziamento_data_json è obbligatorio quando la modalità di pagamento è finanziamento.');
                    }
                    
                    // Se la modalità è "bonifico,finanziamento", finanziamento_data_json è obbligatorio
                    if ($modalitaPagamento === 'bonifico,finanziamento' && !$hasFinanziamentoData) {
                        $fail('Il campo PREVENTIVI.finanziamento_data_json è obbligatorio quando la modalità di pagamento è bonifico,finanziamento. Entrambi i dati JSON sono richiesti.');
                    }
                },
            ],
            'PREVENTIVI.opzione_manutenzione_salvata' => 'required|string|in:si,no',
            'PREVENTIVI.costo_annuo_manutenzione_salvato' => [
                'nullable',
                'required_if:PREVENTIVI.opzione_manutenzione_salvata,si',
                new MaintenanceCostRule(),
            ],
            'PREVENTIVI.opzione_assicurazione_salvata' => 'required|string|in:si,no',
            'PREVENTIVI.costo_annuo_assicurazione_salvato' => [
                'nullable',
                'required_if:PREVENTIVI.opzione_assicurazione_salvata,si',
                new AssicurazioneCostRule(),
            ],

            'CONSUMI_PREVENTIVO' => 'required|array',
            'CONSUMI_PREVENTIVO.anno_partenza' => [
                'required',
                new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.anno_partenza', true, $currentYear, true, $maxYear, true),
            ],
            'CONSUMI_PREVENTIVO.mese_partenza' => [
                'required',
                new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.mese_partenza', true, 1, true, 12, true),
            ],
            'CONSUMI_PREVENTIVO.costo_kwh_bolletta_medio' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.costo_kwh_bolletta_medio')],
            'CONSUMI_PREVENTIVO.costo_kwh_bolletta_totale' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.costo_kwh_bolletta_totale')],
            'CONSUMI_PREVENTIVO.totale_consumo_annuo' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.totale_consumo_annuo')],
            'CONSUMI_PREVENTIVO.totale_costi_annui' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.totale_costi_annui')],
            'CONSUMI_PREVENTIVO.tipologia_bolletta' => 'required|string|in:mensile,bimestrale',
            'CONSUMI_PREVENTIVO.dettagli_consumo_json' => ['required', 'array', new DettagliConsumoJsonRule()],
            'CONSUMI_PREVENTIVO.consumo_diurno_annuo' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.consumo_diurno_annuo')],
            'CONSUMI_PREVENTIVO.consumo_notturno_annuo' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.consumo_notturno_annuo')],
            'CONSUMI_PREVENTIVO.capacita_batteria_consigliata' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.capacita_batteria_consigliata')],
            'CONSUMI_PREVENTIVO.potenza_impianto_consigliata' => ['required', new StrictPositiveNumberRule('CONSUMI_PREVENTIVO.potenza_impianto_consigliata')],

            'DETTAGLI_PRODOTTO_PREVENTIVO' => 'required|array',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.nome_prodotto_salvato' => 'required|string|max:255',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.categoria_prodotto_salvata' => 'required|string|max:255',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.quantita' => ['required', new StrictPositiveNumberRule('DETTAGLI_PRODOTTO_PREVENTIVO.*.quantita', true, 1, true)],
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.prezzo_unitario_salvato' => ['required', new StrictPositiveNumberRule('DETTAGLI_PRODOTTO_PREVENTIVO.*.prezzo_unitario_salvato', false, 0, true)],
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.capacita_batteria_salvata' => 'nullable|numeric|min:0',
            'DETTAGLI_PRODOTTO_PREVENTIVO.*.kWp_salvato' => ['required', new StrictPositiveNumberRule('DETTAGLI_PRODOTTO_PREVENTIVO.*.kWp_salvato', false, 0, true)],

            'PREVENTIVI_VOCE_ECONOMICHE' => 'nullable|array',
            'PREVENTIVI_VOCE_ECONOMICHE.*.nome_voce_salvato' => 'required|string|max:255',
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_voce_salvata' => ['required', 'string', Rule::in(['incentivo', 'sconto', 'costo'])],
            'PREVENTIVI_VOCE_ECONOMICHE.*.valore_applicato' => ['required', new StrictPositiveNumberRule('PREVENTIVI_VOCE_ECONOMICHE.*.valore_applicato', false, 0, true)],
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_valore_salvato' => 'required|string|in:%,€',
            'PREVENTIVI_VOCE_ECONOMICHE.*.anno_inizio_salvato' => ['required', new StrictNonNegativeRule('PREVENTIVI_VOCE_ECONOMICHE.*.anno_inizio_salvato')],
            'PREVENTIVI_VOCE_ECONOMICHE.*.anno_fine_salvato' => ['required', new StrictNonNegativeRule('PREVENTIVI_VOCE_ECONOMICHE.*.anno_fine_salvato')],

            'DETTAGLIO_BUSINESS_PLAN' => [
                'nullable',
                'array',
                new BusinessPlanAnnoSimulazioneRule(),
            ],
            'DETTAGLIO_BUSINESS_PLAN.*.anno_simulazione' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.anno_simulazione', true, 1, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_investimento' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_investimento', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_assicurazione' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_assicurazione', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_manutenzione' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.costo_annuo_manutenzione', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_risparmio_bolletta' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_risparmio_bolletta', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_vendita_eccedenze' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_vendita_eccedenze', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_incentivo_cer' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_incentivo_cer', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.ricavo_fondo_perduto' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.ricavo_fondo_perduto', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.incentivo_pnnr' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.incentivo_pnnr', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.detrazione_fiscale' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.detrazione_fiscale', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.sconto' => ['required', new StrictPositiveNumberRule('DETTAGLIO_BUSINESS_PLAN.*.sconto', false, 0, true)],
            'DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_annuo' => ['required', new StrictNumberRule('DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_annuo')],
            'DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_cumulato' => ['required', new StrictNumberRule('DETTAGLIO_BUSINESS_PLAN.*.flusso_cassa_cumulato')],
        ], [
            'PREVENTIVI.esposizione_salvata.in' => "Il campo PREVENTIVI.esposizione_salvata deve essere uno tra: nord, nord est, nord ovest, sud, sud est, sud ovest, est, ovest.",
            'PREVENTIVI.area_geografica_salvata.in' => "Il campo PREVENTIVI.area_geografica_salvata deve essere uno tra: sud, nord, centro, isole.",
            'CONSUMI_PREVENTIVO.tipologia_bolletta' => "Il campo CONSUMI_PREVENTIVO.tipologia_bolletta deve essere uno tra: mensile, bimestrale.",
            'PREVENTIVI.modalita_pagamento_salvata' => "Il campo PREVENTIVI.modalita_pagamento_salvata deve essere uno tra: bonifico, finanziamento, bonifico,finanziamento.",
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_voce_salvata.in' => "Il campo PREVENTIVI_VOCE_ECONOMICHE.*.tipo_voce_salvata deve essere uno tra: incentivo, sconto, costo.",
            'PREVENTIVI_VOCE_ECONOMICHE.*.tipo_valore_salvato.in' => "Il campo PREVENTIVI_VOCE_ECONOMICHE.*.tipo_valore_salvato deve essere uno tra: %, €. % per gli incentivi e € per le altre tipologie di voce.",
        ]);

        // Validazione aggiuntiva per garantire che quando modalita_pagamento_salvata è "bonifico,finanziamento", entrambi i dati JSON siano presenti
        $modalitaPagamento = strtolower($request->input('PREVENTIVI.modalita_pagamento_salvata', ''));
        $preventiviData = $request->input('PREVENTIVI', []);
        
        if ($modalitaPagamento === 'bonifico,finanziamento') {
            $hasBonificoData = isset($preventiviData['bonifico_data_json']) && !empty($preventiviData['bonifico_data_json']);
            $hasFinanziamentoData = isset($preventiviData['finanziamento_data_json']) && !empty($preventiviData['finanziamento_data_json']);
            
            if (!$hasBonificoData || !$hasFinanziamentoData) {
                $errors = [];
                if (!$hasBonificoData) {
                    $errors['PREVENTIVI.bonifico_data_json'] = ['Il campo PREVENTIVI.bonifico_data_json è obbligatorio quando la modalità di pagamento è bonifico,finanziamento. Entrambi i dati JSON sono richiesti.'];
                }
                if (!$hasFinanziamentoData) {
                    $errors['PREVENTIVI.finanziamento_data_json'] = ['Il campo PREVENTIVI.finanziamento_data_json è obbligatorio quando la modalità di pagamento è bonifico,finanziamento. Entrambi i dati JSON sono richiesti.'];
                }
                
                return response()->json([
                    'message' => 'Errore di validazione.',
                    'errors' => $errors,
                ], 422);
            }
        }


        //Transaction per aggiungere il preventivo a database
        try {
            $preventivoId = DB::transaction(function () use ($validatedData) {
                // 1. Prepara i dati del preventivo convertendo i JSON in stringhe
                $preventivoData = $validatedData['PREVENTIVI'];
                if (isset($preventivoData['bonifico_data_json']) && is_array($preventivoData['bonifico_data_json'])) {
                    $preventivoData['bonifico_data_json'] = json_encode($preventivoData['bonifico_data_json']);
                }
                if (isset($preventivoData['finanziamento_data_json']) && is_array($preventivoData['finanziamento_data_json'])) {
                    $preventivoData['finanziamento_data_json'] = json_encode($preventivoData['finanziamento_data_json']);
                }

                // 2. Crea il preventivo
                $preventivo = Preventivo::create($preventivoData);
                $preventivoId = $preventivo->id_preventivo;

                // 3. Crea il consumo_preventivo con fk_preventivo e dettagli_consumo_json come JSON string
                $consumoData = $validatedData['CONSUMI_PREVENTIVO'];
                $consumoData['fk_preventivo'] = $preventivoId;
                // Converti anno_partenza e mese_partenza in stringhe come richiesto dalla tabella
                $consumoData['anno_partenza'] = (string) $consumoData['anno_partenza'];
                $consumoData['mese_partenza'] = (string) $consumoData['mese_partenza'];
                if (isset($consumoData['dettagli_consumo_json']) && is_array($consumoData['dettagli_consumo_json'])) {
                    $consumoData['dettagli_consumo_json'] = json_encode($consumoData['dettagli_consumo_json']);
                }
                ConsumoPreventivo::create($consumoData);

                // 4. Crea i dettagli_prodotto_preventivo (array) con fk_preventivo
                foreach ($validatedData['DETTAGLI_PRODOTTO_PREVENTIVO'] as $prodottoData) {
                    $prodottoData['fk_preventivo'] = $preventivoId;
                    DettaglioProdottoPreventivo::create($prodottoData);
                }

                // 5. Crea le preventivo_voci_economiche (array opzionale) con fk_preventivo
                if (!empty($validatedData['PREVENTIVI_VOCE_ECONOMICHE'])) {
                    foreach ($validatedData['PREVENTIVI_VOCE_ECONOMICHE'] as $voceData) {
                        $voceData['fk_preventivo'] = $preventivoId;
                        PreventivoVoceEconomica::create($voceData);
                    }
                }

                // 6. Crea i dettaglio_business_plan (array opzionale) con fk_preventivo
                if (!empty($validatedData['DETTAGLIO_BUSINESS_PLAN'])) {
                    foreach ($validatedData['DETTAGLIO_BUSINESS_PLAN'] as $businessPlanData) {
                        $businessPlanData['fk_preventivo'] = $preventivoId;
                        DettaglioBusinessPlan::create($businessPlanData);
                    }
                }

                // 7. Genera il PDF e salvalo su DigitalOcean Spaces
                $preventivoWithRelations = Preventivo::with([
                    'cliente',
                    'dettagliProdotti',
                    'vociEconomiche',
                    'dettagliBusinessPlan',
                    'consumi'
                ])->find($preventivoId);

                $pdfService = new PreventivoPdfService();
                $pdfPath = $pdfService->generateAndSavePdf($preventivoWithRelations);

                // 8. Aggiorna il preventivo con il percorso del PDF (file privato)
                $preventivoWithRelations->update(['pdf_url' => $pdfPath]);

                // 9. Genera URL temporaneo firmato per il PDF (valido per 60 minuti)
                $pdfTemporaryUrl = $pdfService->getTemporaryUrl($preventivoWithRelations, 60);

                return [
                    'preventivo_id' => $preventivoId,
                    'pdf_temporary_url' => $pdfTemporaryUrl,
                ];
            });

            return response()->json([
                'message' => 'Preventivo creato con successo!',
                'data' => [
                    'id_preventivo' => $preventivoId['preventivo_id'],
                    'pdf_temporary_url' => $preventivoId['pdf_temporary_url'],
                    'validated_data' => $validatedData,
                ],
            ], 201);
            
        } catch (\Exception $e) {
            // Se la transaction fallisce, Laravel esegue automaticamente il rollback
            // Restituiamo una risposta di errore appropriata
            Log::error('Errore nella creazione del preventivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData,
            ]);

            return response()->json([
                'message' => 'Errore durante la creazione del preventivo.',
                'error' => config('app.debug') ? $e->getMessage() : 'Si è verificato un errore durante il salvataggio dei dati.',
            ], 500);
        }
    }

    public function download(Request $request, $id)
    {
        $preventivo = Preventivo::find($id);

        if (! $preventivo) {
            return response()->json([
                'message' => 'Preventivo non trovato',
            ], 404);
        }

        if (! $preventivo->pdf_url) {
            return response()->json([
                'message' => 'Nessun PDF disponibile per questo preventivo',
            ], 404);
        }

        if (! Storage::disk('do')->exists($preventivo->pdf_url)) {
            return response()->json([
                'message' => 'Il PDF del preventivo è stato eliminato dal cloud',
            ], 404);
        }

        $expirationMinutes = 60 * 6;

        $pdfService = new PreventivoPdfService();
        $temporaryUrl = $pdfService->getTemporaryUrlFromPath($preventivo->pdf_url, $expirationMinutes);

        return response()->json([
            'downloadUrl' => $temporaryUrl,
            'expiresAt' => now()->addMinutes($expirationMinutes)->toIso8601String(),
            'expiresInMinutes' => $expirationMinutes,
        ]);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            //'ID',
            'Numero Preventivo',
            'Data Preventivo',
            'Cliente',
            'Agente',
            'Stato',
            'Modalità Pagamento',
            'Produzione Annua Stimata',
            'Risparmio Autoconsumo Annuo',
            'Vendita Eccedenze RID Annuo',
            'Incentivo CER Annuo',
            'Detrazione Fiscale Annuo',
            'Creato il',
            'Aggiornato il',
        ];

        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $preventivo) {
            fputcsv($fp, [
                //$preventivo->id_preventivo,
                $preventivo->numero_preventivo,
                optional($preventivo->data_preventivo)->format('Y-m-d'),
                $preventivo->cliente ? trim(($preventivo->cliente->name ?? '') . ' ' . ($preventivo->cliente->last_name ?? '')) : '',
                $preventivo->agente ? trim(($preventivo->agente->name ?? '') . ' ' . ($preventivo->agente->last_name ?? '')) : '',
                $preventivo->stato,
                $preventivo->modalita_pagamento_salvata,
                $preventivo->produzione_annua_stimata,
                $preventivo->risparmio_autoconsumo_annuo,
                $preventivo->vendita_eccedenze_rid_annua,
                $preventivo->incentivo_cer_annuo,
                $preventivo->detrazione_fiscale_annua,
                optional($preventivo->created_at)->format('Y-m-d H:i:s'),
                optional($preventivo->updated_at)->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($fp);

        return $csvPath;
    }
}
