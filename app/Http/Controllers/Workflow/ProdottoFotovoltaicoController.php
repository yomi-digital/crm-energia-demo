<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ProdottoFotovoltaico;

class ProdottoFotovoltaicoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $prodotti = ProdottoFotovoltaico::query()->with('categoria');

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $prodotti->where('is_active', $request->boolean($isActiveKey));
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

        $prodotti = $prodotti->paginate($perPage);

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
                'potenza_kwp' => ['required', 'numeric'],
                'capacita_kwh' => ['required', 'numeric'],
                'prezzo_base' => ['required', 'numeric'],
                'finanziamento_rate_standard' => ['nullable', 'array'],
                'link_scheda_prodotto_tecnica' => ['nullable', 'url'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            [
                'fk_categoria.required' => 'Selezionare una categoria valida.',
                'fk_categoria.integer' => 'L\'identificativo della categoria deve essere numerico.',
                'fk_categoria.exists' => 'La categoria selezionata non esiste.',
                'codice_prodotto.required' => 'Specificare il nome del prodotto.',
                'codice_prodotto.string' => 'Il nome del prodotto deve essere una stringa.',
                'codice_prodotto.max' => 'Il nome del prodotto non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'potenza_kwp.required' => 'La potenza kWp è obbligatoria.',
                'potenza_kwp.numeric' => 'La potenza kWp deve essere un valore numerico.',
                'capacita_kwh.required' => 'La capacità kWh è obbligatoria.',
                'capacita_kwh.numeric' => 'La capacità kWh deve essere un valore numerico.',
                'prezzo_base.required' => 'Il prezzo base è obbligatorio.',
                'prezzo_base.numeric' => 'Il prezzo base deve essere un valore numerico.',
                'finanziamento_rate_standard.array' => 'Le rate standard devono essere fornite come array.',
                'link_scheda_prodotto_tecnica.url' => 'Il link della scheda tecnica deve essere un URL valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
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

        return response()->json($prodotto->fresh(['categoria']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $prodotto = ProdottoFotovoltaico::with('categoria')->findOrFail($id);

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
                'potenza_kwp' => ['sometimes', 'required', 'numeric'],
                'capacita_kwh' => ['sometimes', 'required', 'numeric'],
                'prezzo_base' => ['sometimes', 'required', 'numeric'],
                'finanziamento_rate_standard' => ['nullable', 'array'],
                'link_scheda_prodotto_tecnica' => ['nullable', 'url'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            [
                'fk_categoria.required' => 'Selezionare una categoria valida.',
                'fk_categoria.integer' => 'L\'identificativo della categoria deve essere numerico.',
                'fk_categoria.exists' => 'La categoria selezionata non esiste.',
                'codice_prodotto.required' => 'Specificare il codice del prodotto.',
                'codice_prodotto.string' => 'Il codice del prodotto deve essere una stringa.',
                'codice_prodotto.max' => 'Il codice del prodotto non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'potenza_kwp.required' => 'La potenza kWp è obbligatoria.',
                'potenza_kwp.numeric' => 'La potenza kWp deve essere un valore numerico.',
                'capacita_kwh.required' => 'La capacità kWh è obbligatoria.',
                'capacita_kwh.numeric' => 'La capacità kWh deve essere un valore numerico.',
                'prezzo_base.required' => 'Il prezzo base è obbligatorio.',
                'prezzo_base.numeric' => 'Il prezzo base deve essere un valore numerico.',
                'finanziamento_rate_standard.array' => 'Le rate standard devono essere fornite come array.',
                'link_scheda_prodotto_tecnica.url' => 'Il link della scheda tecnica deve essere un URL valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
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

        return response()->json($prodotto->fresh(['categoria']));
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

        return response()->json($prodotto->fresh(['categoria']), 200);
    }
}
