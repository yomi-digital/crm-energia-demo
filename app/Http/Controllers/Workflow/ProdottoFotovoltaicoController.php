<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ProdottoFotovoltaico;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProdottoFotovoltaicoController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'is_active' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0', 'all', 'TRUE', 'FALSE', 'All', 'ALL'])],
                'isActive' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0', 'all', 'TRUE', 'FALSE', 'All', 'ALL'])],
                'q' => ['nullable', 'string'],
                'itemsPerPage' => ['nullable', 'integer', 'min:1'],
                'listino_id' => ['nullable', 'integer', 'exists:listini,id'],
            ],
            [
                'is_active.in' => 'Il parametro is_active può essere solo true, false o all.',
                'is_active.string' => 'Il parametro is_active deve essere una stringa.',
                'isActive.in' => 'Il parametro isActive può essere solo true, false o all.',
                'isActive.string' => 'Il parametro isActive deve essere una stringa.',
                'q.string' => 'Il parametro q deve essere una stringa.',
                'itemsPerPage.integer' => 'Il parametro itemsPerPage deve essere un numero intero.',
                'itemsPerPage.min' => 'Il parametro itemsPerPage deve essere almeno 1.',
                'listino_id.exists' => 'Il listino selezionato non esiste.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parametri non validi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $perPage = $request->get('itemsPerPage', 10);

        $prodotti = ProdottoFotovoltaico::query()->with(['categoria', 'listini']);

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $isActiveValue = strtolower(trim($request->input($isActiveKey)));

            if ($isActiveValue !== 'all') {
                $prodotti->where('is_active', $request->boolean($isActiveKey));
            }
        } else {
            $prodotti->where('is_active', true);
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $prodotti->where(function ($query) use ($search) {
                $query->where('codice_prodotto', 'like', "%{$search}%")
                    ->orWhere('descrizione', 'like', "%{$search}%");
            });
        }

        if ($request->has('listino_id')) {
            $prodotti->whereHas('listini', function($q) use ($request) {
                $q->where('listini.id', $request->get('listino_id'));
            });
        }

        $prodotti = $prodotti->paginate($perPage);

        if ($request->has('export')) {
            $pageProducts = $prodotti->getCollection();
            $csvPath = $this->transformEntriesToCSV($pageProducts);

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
            }, 'prodotti_fotovoltaico_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        }

        return response()->json($prodotti);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'fk_categoria' => ['required', 'integer', 'exists:categorie_prodotto_fotovoltaico,id_categoria'],
                'codice_prodotto' => ['required', 'string', 'max:255'],
                'descrizione' => ['nullable', 'string'],
                'potenza_kwp_pannelli' => ['required', 'numeric'],
                'capacita_kwh' => ['required', 'numeric'],
                'prezzo_base' => ['required', 'numeric'],
                'potenza_inverter' => ['required', 'numeric', 'min:0'],
                'marca_inverter' => ['required', 'string', 'max:255'],
                'quantita_inverter' => ['nullable', 'integer', 'min:0'],
                'marca_batteria' => ['nullable', 'string', 'max:255'],
                'potenza_batteria' => ['nullable', 'numeric', 'min:0'],
                'quantita_batterie' => ['nullable', 'integer', 'min:0'],
                'quantita_pannelli' => ['nullable', 'integer', 'min:0'],
                'marca_pannelli' => ['nullable', 'string', 'max:255'],
                'finanziamento_rate_standard' => ['nullable', 'array'],
                'link_scheda_prodotto_tecnica' => ['nullable', 'url'],
                'is_active' => ['sometimes', 'boolean'],
                'listini' => ['nullable', 'array'],
                'listini.*' => ['exists:listini,id'],
            ],
            [
                'fk_categoria.required' => 'Selezionare una categoria valida.',
                'fk_categoria.integer' => 'L\'identificativo della categoria deve essere numerico.',
                'fk_categoria.exists' => 'La categoria selezionata non esiste.',
                'codice_prodotto.required' => 'Specificare il nome del prodotto.',
                'codice_prodotto.string' => 'Il nome del prodotto deve essere una stringa.',
                'codice_prodotto.max' => 'Il nome del prodotto non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'potenza_kwp_pannelli.required' => 'La potenza kWp dei pannelli è obbligatoria.',
                'potenza_kwp_pannelli.numeric' => 'La potenza kWp dei pannelli deve essere un valore numerico.',
                'capacita_kwh.required' => 'La capacità kWh è obbligatoria.',
                'capacita_kwh.numeric' => 'La capacità kWh deve essere un valore numerico.',
                'prezzo_base.required' => 'Il prezzo base è obbligatorio.',
                'prezzo_base.numeric' => 'Il prezzo base deve essere un valore numerico.',
                'potenza_inverter.required' => 'La potenza inverter è obbligatoria.',
                'potenza_inverter.numeric' => 'La potenza inverter deve essere un valore numerico.',
                'potenza_inverter.min' => 'La potenza inverter deve essere maggiore o uguale a 0.',
                'marca_inverter.required' => 'La marca inverter è obbligatoria.',
                'marca_inverter.string' => 'La marca inverter deve essere una stringa.',
                'marca_inverter.max' => 'La marca inverter non può superare 255 caratteri.',
                'quantita_inverter.integer' => 'La quantità inverter deve essere un numero intero.',
                'quantita_inverter.min' => 'La quantità inverter deve essere maggiore o uguale a 0.',
                'marca_batteria.string' => 'La marca batteria deve essere una stringa.',
                'marca_batteria.max' => 'La marca batteria non può superare 255 caratteri.',
                'potenza_batteria.numeric' => 'La potenza batteria deve essere un valore numerico.',
                'potenza_batteria.min' => 'La potenza batteria deve essere maggiore o uguale a 0.',
                'quantita_batterie.integer' => 'La quantità batterie deve essere un numero intero.',
                'quantita_batterie.min' => 'La quantità batterie deve essere maggiore o uguale a 0.',
                'quantita_pannelli.integer' => 'La quantità pannelli deve essere un numero intero.',
                'quantita_pannelli.min' => 'La quantità pannelli deve essere maggiore o uguale a 0.',
                'marca_pannelli.string' => 'La marca pannelli deve essere una stringa.',
                'marca_pannelli.max' => 'La marca pannelli non può superare 255 caratteri.',
                'finanziamento_rate_standard.array' => 'Le rate standard devono essere fornite come array.',
                'link_scheda_prodotto_tecnica.url' => 'Il link della scheda tecnica deve essere un URL valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
                'listini.array' => 'Il campo listini deve essere un array.',
                'listini.*.exists' => 'Uno o più listini selezionati non sono validi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        if (isset($payload['finanziamento_rate_standard'])) {
            $payload['finanziamento_rate_standard'] = json_encode($payload['finanziamento_rate_standard']);
        }

        $prodotto = ProdottoFotovoltaico::create($payload);

        if (isset($payload['listini'])) {
            $prodotto->listini()->sync($payload['listini']);
        }

        return response()->json($prodotto->fresh(['categoria', 'listini']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $prodotto = ProdottoFotovoltaico::with(['categoria', 'listini'])->findOrFail($id);

        return response()->json($prodotto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $prodotto = ProdottoFotovoltaico::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'fk_categoria' => ['sometimes', 'required', 'integer', 'exists:categorie_prodotto_fotovoltaico,id_categoria'],
                'codice_prodotto' => ['sometimes', 'required', 'string', 'max:255'],
                'descrizione' => ['nullable', 'string'],
                'potenza_kwp_pannelli' => ['sometimes', 'required', 'numeric'],
                'capacita_kwh' => ['sometimes', 'required', 'numeric'],
                'prezzo_base' => ['sometimes', 'required', 'numeric'],
                'potenza_inverter' => ['sometimes', 'required', 'numeric', 'min:0'],
                'marca_inverter' => ['sometimes', 'required', 'string', 'max:255'],
                'quantita_inverter' => ['nullable', 'integer', 'min:0'],
                'marca_batteria' => ['nullable', 'string', 'max:255'],
                'potenza_batteria' => ['nullable', 'numeric', 'min:0'],
                'quantita_batterie' => ['nullable', 'integer', 'min:0'],
                'quantita_pannelli' => ['nullable', 'integer', 'min:0'],
                'marca_pannelli' => ['nullable', 'string', 'max:255'],
                'finanziamento_rate_standard' => ['nullable', 'array'],
                'link_scheda_prodotto_tecnica' => ['nullable', 'url'],
                'is_active' => ['sometimes', 'boolean'],
                'listini' => ['nullable', 'array'],
                'listini.*' => ['exists:listini,id'],
            ],
            [
                'fk_categoria.required' => 'Selezionare una categoria valida.',
                'fk_categoria.integer' => 'L\'identificativo della categoria deve essere numerico.',
                'fk_categoria.exists' => 'La categoria selezionata non esiste.',
                'codice_prodotto.required' => 'Specificare il codice del prodotto.',
                'codice_prodotto.string' => 'Il codice del prodotto deve essere una stringa.',
                'codice_prodotto.max' => 'Il codice del prodotto non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'potenza_kwp_pannelli.required' => 'La potenza kWp dei pannelli è obbligatoria.',
                'potenza_kwp_pannelli.numeric' => 'La potenza kWp dei pannelli deve essere un valore numerico.',
                'capacita_kwh.required' => 'La capacità kWh è obbligatoria.',
                'capacita_kwh.numeric' => 'La capacità kWh deve essere un valore numerico.',
                'prezzo_base.required' => 'Il prezzo base è obbligatorio.',
                'prezzo_base.numeric' => 'Il prezzo base deve essere un valore numerico.',
                'potenza_inverter.required' => 'La potenza inverter è obbligatoria.',
                'potenza_inverter.numeric' => 'La potenza inverter deve essere un valore numerico.',
                'potenza_inverter.min' => 'La potenza inverter deve essere maggiore o uguale a 0.',
                'marca_inverter.required' => 'La marca inverter è obbligatoria.',
                'marca_inverter.string' => 'La marca inverter deve essere una stringa.',
                'marca_inverter.max' => 'La marca inverter non può superare 255 caratteri.',
                'quantita_inverter.integer' => 'La quantità inverter deve essere un numero intero.',
                'quantita_inverter.min' => 'La quantità inverter deve essere maggiore o uguale a 0.',
                'marca_batteria.string' => 'La marca batteria deve essere una stringa.',
                'marca_batteria.max' => 'La marca batteria non può superare 255 caratteri.',
                'potenza_batteria.numeric' => 'La potenza batteria deve essere un valore numerico.',
                'potenza_batteria.min' => 'La potenza batteria deve essere maggiore o uguale a 0.',
                'quantita_batterie.integer' => 'La quantità batterie deve essere un numero intero.',
                'quantita_batterie.min' => 'La quantità batterie deve essere maggiore o uguale a 0.',
                'quantita_pannelli.integer' => 'La quantità pannelli deve essere un numero intero.',
                'quantita_pannelli.min' => 'La quantità pannelli deve essere maggiore o uguale a 0.',
                'marca_pannelli.string' => 'La marca pannelli deve essere una stringa.',
                'marca_pannelli.max' => 'La marca pannelli non può superare 255 caratteri.',
                'finanziamento_rate_standard.array' => 'Le rate standard devono essere fornite come array.',
                'link_scheda_prodotto_tecnica.url' => 'Il link della scheda tecnica deve essere un URL valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
                'listini.array' => 'Il campo listini deve essere un array.',
                'listini.*.exists' => 'Uno o più listini selezionati non sono validi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        if (array_key_exists('finanziamento_rate_standard', $payload)) {
            $payload['finanziamento_rate_standard'] = $payload['finanziamento_rate_standard'] !== null
                ? json_encode($payload['finanziamento_rate_standard'])
                : null;
        }

        $prodotto->fill($payload);
        $prodotto->save();

        if (isset($payload['listini'])) {
            $prodotto->listini()->sync($payload['listini']);
        }

        return response()->json($prodotto->fresh(['categoria', 'listini']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $prodotto = ProdottoFotovoltaico::findOrFail($id);

        if (! $prodotto->is_active) {
            return response()->json([
                'message' => 'Il prodotto è già stato disattivato.',
            ], 400);
        }

        $prodotto->is_active = false;
        $prodotto->save();

        return response()->json($prodotto->fresh(['categoria', 'listini']), 200);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            //'ID',
            'Codice prodotto',
            'Descrizione',
            'Categoria',
            'Listini',
            'Potenza kWp',
            'Capacità kWh',
            'Prezzo base',
            //'Rate standard',
            'Link scheda tecnica',
            'Attivo',
            'Creato il',
            'Aggiornato il',
        ];

        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $prodotto) {
            $rateStandard = $prodotto->finanziamento_rate_standard;
            if (is_array($rateStandard) || is_object($rateStandard)) {
                $rateStandard = json_encode($rateStandard);
            }
            
            $listini = $prodotto->listini->pluck('nome')->implode(', ');

            fputcsv($fp, [
                //$prodotto->id_prodotto,
                $prodotto->codice_prodotto,
                $prodotto->descrizione,
                optional($prodotto->categoria)->nome_categoria,
                $listini,
                $prodotto->potenza_kwp_pannelli . ' kWp',
                $prodotto->capacita_kwh . ' kWh',
                $prodotto->prezzo_base . ' €',
                //$rateStandard,
                $prodotto->link_scheda_prodotto_tecnica,
                $prodotto->is_active ? 'Attivo' : 'Inattivo',
                optional($prodotto->created_at)->format('Y-m-d H:i:s'),
                optional($prodotto->updated_at)->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($fp);

        return $csvPath;
    }
}
