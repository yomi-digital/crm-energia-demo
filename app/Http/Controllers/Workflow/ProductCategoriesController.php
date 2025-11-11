<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CategoriaProdottoFotovoltaico;

class ProductCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $categories = CategoriaProdottoFotovoltaico::query();

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $categories->where('is_active', $request->boolean($isActiveKey));
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
