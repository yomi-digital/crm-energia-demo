<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\VoceEconomica;
use App\Models\ApplicabilitaVoce;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

class VoceEconomicaController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'is_active' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0', 'all', 'TRUE', 'FALSE', 'All', 'ALL'])],
                'isActive' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0', 'all', 'TRUE', 'FALSE', 'All', 'ALL'])],
                'customer_type' => ['nullable', 'string', Rule::in(['residenziale', 'business', 'RESIDENZIALE', 'BUSINESS'])],
                'tipo_voce' => ['nullable', 'string'],
                'q' => ['nullable', 'string'],
                'itemsPerPage' => ['nullable', 'integer', 'min:1'],
            ],
            [
                'is_active.in' => 'Il parametro is_active può essere solo true, false o all.',
                'is_active.string' => 'Il parametro is_active deve essere una stringa.',
                'isActive.in' => 'Il parametro isActive può essere solo true, false o all.',
                'isActive.string' => 'Il parametro isActive deve essere una stringa.',
                'customer_type.in' => 'Il parametro customer_type può essere solo RESIDENZIALE o BUSINESS.',
                'customer_type.string' => 'Il parametro customer_type deve essere una stringa.',
                'tipo_voce.string' => 'Il parametro tipo_voce deve essere una stringa.',
                'q.string' => 'Il parametro q deve essere una stringa.',
                'itemsPerPage.integer' => 'Il parametro itemsPerPage deve essere un numero intero.',
                'itemsPerPage.min' => 'Il parametro itemsPerPage deve essere almeno 1.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parametri non validi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $perPage = $request->get('itemsPerPage', 10);

        $vociEconomiche = VoceEconomica::query();

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $isActiveValue = strtolower(trim($request->input($isActiveKey)));

            if ($isActiveValue !== 'all') {
                $vociEconomiche->where('is_active', $request->boolean($isActiveKey));
            }
        } else {
            $vociEconomiche->where('is_active', true);
        }

        if ($request->filled('customer_type')) {
            $customerType = strtolower($request->get('customer_type'));
            $vociEconomiche->whereHas('applicabilita', function ($query) use ($customerType) {
                $query->whereRaw('lower(tipo_cliente) = ?', [$customerType]);
            });
        }

        if ($request->filled('tipo_voce')) {
            $vociEconomiche->where('tipo_voce', $request->get('tipo_voce'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $vociEconomiche->where('nome_voce', 'like', "%{$search}%");
        }

        $vociEconomiche = $vociEconomiche->paginate($perPage);

        if ($request->has('export')) {
            $pageVoci = $vociEconomiche->getCollection()->load('applicabilita');
            $csvPath = $this->transformEntriesToCSV($pageVoci);

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
            }, 'voci_economiche_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        }

        return response()->json($vociEconomiche);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'nome_voce' => ['required', 'string', 'max:255'],
                'tipo_voce' => ['required', 'string', 'max:100', 'in:incentivo,sconto,costo,prodotto'],
                'tipo_valore' => ['required', 'string', 'max:100', 'in:$,%'],
                'valore_default' => ['nullable', 'numeric', 'gte:0'],
                'anno_inizio' => ['nullable', 'integer'],
                'anno_fine' => ['nullable', 'integer', 'gte:anno_inizio'],
                'is_active' => ['sometimes', 'boolean'],
                'iva' => ['sometimes', 'boolean'],
                'tipo_cliente' => ['nullable', 'array'],
                'tipo_cliente.*' => ['in:RESIDENZIALE,BUSINESS'],
            ],
            [
                'nome_voce.required' => 'Specificare il nome della voce.',
                'nome_voce.string' => 'Il nome della voce deve essere una stringa.',
                'nome_voce.max' => 'Il nome della voce non può superare 255 caratteri.',
                'tipo_voce.required' => 'Specificare il tipo di voce. incentivo, sconto, costo o prodotto.',
                'tipo_voce.string' => 'Il tipo di voce deve essere una stringa.',
                'tipo_voce.max' => 'Il tipo di voce non può superare 100 caratteri.',
                'tipo_voce.in' => 'Il tipo di voce deve essere incentivo, sconto, costo o prodotto.',
                'tipo_valore.required' => 'Specificare il tipo di valore. $ o %.',
                'tipo_valore.string' => 'Il tipo di valore deve essere una stringa.',
                'tipo_valore.max' => 'Il tipo di valore non può superare 100 caratteri.',
                'tipo_valore.in' => 'Il tipo di valore deve essere $ o %.',
                'valore_default.numeric' => 'Il valore default deve essere numerico.',
                'valore_default.gte' => 'Il valore default deve essere maggiore o uguale a 0.',
                'anno_inizio.integer' => 'L\'anno di inizio deve essere numerico.',
                'anno_fine.integer' => 'L\'anno di fine deve essere numerico.',
                'anno_fine.gte' => 'L\'anno di fine deve essere maggiore o uguale all\'anno di inizio.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
                'iva.boolean' => 'Il campo iva deve essere un booleano.',
                'tipo_cliente.array' => 'Il campo tipo_cliente deve essere un array.',
                'tipo_cliente.*.in' => 'Le tipologie clienti consentite sono solo RESIDENZIALE o BUSINESS.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $tipiCliente = $payload['tipo_cliente'] ?? ['RESIDENZIALE', 'BUSINESS'];
        unset($payload['tipo_cliente']);

        $voceEconomica = VoceEconomica::create($payload);

        $voceEconomica->applicabilita()->createMany(
            collect($tipiCliente)
                ->unique()
                ->map(fn ($tipo) => ['tipo_cliente' => $tipo])
                ->all()
        );

        return response()->json($voceEconomica->load('applicabilita'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $voceEconomica = VoceEconomica::with('applicabilita')->findOrFail($id);

        return response()->json($voceEconomica);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $voceEconomica = VoceEconomica::findOrFail($id);

        $validator = \Validator::make(
            $request->all(),
            [
                'nome_voce' => ['sometimes', 'required', 'string', 'max:255'],
                'tipo_voce' => ['sometimes', 'required', 'string', 'max:100', 'in:incentivo,sconto,costo,prodotto'],
                'tipo_valore' => ['sometimes', 'required', 'string', 'max:100'],
                'valore_default' => ['nullable', 'numeric', 'gte:0'],
                'anno_inizio' => ['nullable', 'integer'],
                'anno_fine' => ['nullable', 'integer', 'gte:anno_inizio'],
                'is_active' => ['sometimes', 'boolean'],
                'iva' => ['sometimes', 'boolean'],
                'tipo_cliente' => ['nullable', 'array'],
                'tipo_cliente.*' => ['in:RESIDENZIALE,BUSINESS'],
            ],
            [
                'nome_voce.required' => 'Specificare il nome della voce.',
                'nome_voce.string' => 'Il nome della voce deve essere una stringa.',
                'nome_voce.max' => 'Il nome della voce non può superare 255 caratteri.',
                'tipo_voce.required' => 'Specificare il tipo di voce.',
                'tipo_voce.string' => 'Il tipo di voce deve essere una stringa.',
                'tipo_voce.max' => 'Il tipo di voce non può superare 100 caratteri.',
                'tipo_voce.in' => 'Il tipo di voce deve essere incentivo, sconto, costo o prodotto.',
                'tipo_valore.required' => 'Specificare il tipo di valore.',
                'tipo_valore.string' => 'Il tipo di valore deve essere una stringa.',
                'tipo_valore.max' => 'Il tipo di valore non può superare 100 caratteri.',
                'valore_default.numeric' => 'Il valore default deve essere numerico.',
                'valore_default.gte' => 'Il valore default deve essere maggiore o uguale a 0.',
                'anno_inizio.integer' => 'L\'anno di inizio deve essere numerico.',
                'anno_fine.integer' => 'L\'anno di fine deve essere numerico.',
                'anno_fine.gte' => 'L\'anno di fine deve essere maggiore o uguale all\'anno di inizio.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
                'iva.boolean' => 'Il campo iva deve essere un booleano.',
                'tipo_cliente.array' => 'Il campo tipo_cliente deve essere un array.',
                'tipo_cliente.*.in' => 'Le tipologie clienti consentite sono solo RESIDENZIALE o BUSINESS.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $tipiCliente = null;

        if (array_key_exists('tipo_cliente', $payload)) {
            $tipiCliente = $payload['tipo_cliente'];
            unset($payload['tipo_cliente']);
        }

        $voceEconomica->fill($payload);
        $voceEconomica->save();

        if (is_array($tipiCliente)) {
            $tipiCliente = collect($tipiCliente)->unique();

            $voceEconomica->applicabilita()->delete();

            if ($tipiCliente->isEmpty()) {
                $tipiCliente = collect(['RESIDENZIALE', 'BUSINESS']);
            }

            $voceEconomica->applicabilita()->createMany(
                $tipiCliente->map(fn ($tipo) => ['tipo_cliente' => $tipo])->all()
            );
        }

        return response()->json($voceEconomica->load('applicabilita'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $voceEconomica = VoceEconomica::findOrFail($id);

        if (! $voceEconomica->is_active) {
            return response()->json([
                'message' => 'La voce economica è già stata disattivata.',
            ], 400);
        }

        $voceEconomica->is_active = false;
        $voceEconomica->save();

        return response()->json($voceEconomica->fresh('applicabilita'), 200);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            //'ID',
            'Nome voce',
            'Tipo voce',
            'Tipo valore',
            'Valore default',
            'Anno inizio',
            'Anno fine',
            'Attiva',
            'Tipi cliente',
            'Creata il',
            'Aggiornata il',
        ];

        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $voce) {
            $tipiCliente = $voce->relationLoaded('applicabilita')
                ? $voce->applicabilita->pluck('tipo_cliente')->implode(', ')
                : '';

            $tipoValoreFormatted = "";
            if ($voce->tipo_valore == '$') {
                $tipoValoreFormatted = 'Valore fisso';
            } else if ($voce->tipo_valore == '%') {
                $tipoValoreFormatted = 'Valore percentuale';
            } else {
                $tipoValoreFormatted = $voce->tipo_valore;
            }

            $valoreDefaultFormatted = "";
            if ($voce->tipo_valore == '$') {
                $valoreDefaultFormatted = $voce->valore_default . ' €';
            } else if ($voce->tipo_valore == '%') {
                $valoreDefaultFormatted = $voce->valore_default . ' %';
            } else {
                $valoreDefaultFormatted = $voce->valore_default;
            }

            fputcsv($fp, [
                //$voce->id_voce,
                $voce->nome_voce,
                $voce->tipo_voce,
                $tipoValoreFormatted,
                $valoreDefaultFormatted,
                $voce->anno_inizio,
                $voce->anno_fine,
                $voce->is_active ? 'Attivo' : 'Inattivo',
                $tipiCliente,
                optional($voce->created_at)->format('Y-m-d H:i:s'),
                optional($voce->updated_at)->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($fp);

        return $csvPath;
    }
}
