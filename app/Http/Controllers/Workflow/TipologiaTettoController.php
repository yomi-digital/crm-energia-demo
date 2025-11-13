<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\TipologiaTetto;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

class TipologiaTettoController extends Controller
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
            ],
            [
                'is_active.in' => 'Il parametro is_active può essere solo true, false o all.',
                'is_active.string' => 'Il parametro is_active deve essere una stringa.',
                'isActive.in' => 'Il parametro isActive può essere solo true, false o all.',
                'isActive.string' => 'Il parametro isActive deve essere una stringa.',
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

        $tipologieTetto = TipologiaTetto::query();

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $isActiveValue = strtolower(trim($request->input($isActiveKey)));

            if ($isActiveValue !== 'all') {
                $tipologieTetto->where('is_active', $request->boolean($isActiveKey));
            }
        } else {
            $tipologieTetto->where('is_active', true);
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $tipologieTetto->where(function ($query) use ($search) {
                $query->where('nome_tipologia', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $tipologieTetto = $tipologieTetto->paginate($perPage);

        if ($request->has('export')) {
            $pageTipologie = $tipologieTetto->getCollection();
            $csvPath = $this->transformEntriesToCSV($pageTipologie);

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
            }, 'tipologie_tetto_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        }

        return response()->json($tipologieTetto);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nome_tipologia' => ['required', 'string', 'max:255'],
                'note' => ['nullable', 'string', 'max:255'],
                'costo_extra_kwp' => ['nullable', 'numeric'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            [
                'nome_tipologia.required' => 'Specificare il nome della tipologia di tetto.',
                'nome_tipologia.string' => 'Il nome della tipologia deve essere una stringa.',
                'nome_tipologia.max' => 'Il nome della tipologia non può superare 255 caratteri.',
                'note.string' => 'Le note devono essere un testo valido.',
                'note.max' => 'Le note non possono superare 255 caratteri.',
                'costo_extra_kwp.numeric' => 'Il costo extra per kWp deve essere un valore numerico.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tipologiaTetto = TipologiaTetto::create($validator->validated());

        return response()->json($tipologiaTetto, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tipologiaTetto = TipologiaTetto::findOrFail($id);

        return response()->json($tipologiaTetto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tipologiaTetto = TipologiaTetto::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'nome_tipologia' => ['sometimes', 'required', 'string', 'max:255'],
                'note' => ['nullable', 'string', 'max:255'],
                'costo_extra_kwp' => ['nullable', 'numeric'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            [
                'nome_tipologia.required' => 'Specificare il nome della tipologia di tetto.',
                'nome_tipologia.string' => 'Il nome della tipologia deve essere una stringa.',
                'nome_tipologia.max' => 'Il nome della tipologia non può superare 255 caratteri.',
                'note.string' => 'Le note devono essere un testo valido.',
                'note.max' => 'Le note non possono superare 255 caratteri.',
                'costo_extra_kwp.numeric' => 'Il costo extra per kWp deve essere un valore numerico.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tipologiaTetto->fill($validator->validated());
        $tipologiaTetto->save();

        return response()->json($tipologiaTetto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tipologiaTetto = TipologiaTetto::findOrFail($id);

        if (! $tipologiaTetto->is_active) {
            return response()->json([
                'message' => 'La tipologia tetto è già stata disattivata.',
            ], 400);
        }

        $tipologiaTetto->is_active = false;
        $tipologiaTetto->save();

        return response()->json($tipologiaTetto->fresh(), 200);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            //'ID',
            'Nome tipologia',
            'Note',
            'Costo extra kWp',
            'Attiva',
            'Creata il',
            'Aggiornata il',
        ];

        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $tipologia) {
            fputcsv($fp, [
                //$tipologia->id_voce,
                $tipologia->nome_tipologia,
                $tipologia->note,
                $tipologia->costo_extra_kwp . ' €',
                $tipologia->is_active ? 'Attivo' : 'Inattivo',
                optional($tipologia->created_at)->format('Y-m-d H:i:s'),
                optional($tipologia->updated_at)->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($fp);

        return $csvPath;
    }
}
