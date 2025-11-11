<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TipologiaTetto;

class TipologiaTettoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $tipologieTetto = TipologiaTetto::query();

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $tipologieTetto->where('is_active', $request->boolean($isActiveKey));
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
}
