<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\CategoriaProdottoFotovoltaico;

class ProductCategoriesController extends Controller
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

        $categories = CategoriaProdottoFotovoltaico::query();

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $isActiveValue = strtolower(trim($request->input($isActiveKey)));

            if ($isActiveValue !== 'all') {
                $categories->where('is_active', $request->boolean($isActiveKey));
            }
        } else {
            $categories->where('is_active', true);
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $categories->where(function ($query) use ($search) {
                $query->where('nome_categoria', 'like', "%{$search}%")
                    ->orWhere('descrizione', 'like', "%{$search}%");
            });
        }

        $categories = $categories->paginate($perPage);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nome_categoria' => ['required', 'string', 'max:255'],
                'descrizione' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            [
                'nome_categoria.required' => 'Specificare il nome della categoria.',
                'nome_categoria.string' => 'Il nome della categoria deve essere una stringa.',
                'nome_categoria.max' => 'Il nome della categoria non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $categoria = CategoriaProdottoFotovoltaico::create($validator->validated());

        return response()->json($categoria, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categoria = CategoriaProdottoFotovoltaico::findOrFail($id);

        return response()->json($categoria);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categoria = CategoriaProdottoFotovoltaico::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'nome_categoria' => ['sometimes', 'required', 'string', 'max:255'],
                'descrizione' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            [
                'nome_categoria.required' => 'Specificare il nome della categoria.',
                'nome_categoria.string' => 'Il nome della categoria deve essere una stringa.',
                'nome_categoria.max' => 'Il nome della categoria non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $categoria->fill($validator->validated());
        $categoria->save();

        return response()->json($categoria);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categoria = CategoriaProdottoFotovoltaico::findOrFail($id);

        if (! $categoria->is_active) {
            return response()->json([
                'message' => 'La categoria è già stata disattivata.',
            ], 400);
        }

        $categoria->is_active = false;
        $categoria->save();

        return response()->json($categoria->fresh(), 200);
    }
}
