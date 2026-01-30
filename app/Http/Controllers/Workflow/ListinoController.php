<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Listino;

class ListinoController extends Controller
{
    public function index(Request $request)
    {
        $listini = Listino::query();

        if ($request->has('q')) {
            $search = $request->get('q');
            $listini->where(function ($query) use ($search) {
                $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('descrizione', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $isActive = $request->get('is_active');
            if ($isActive !== 'all') {
                $listini->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
            }
        } else {
             $listini->where('is_active', true);
        }

        $perPage = $request->get('itemsPerPage', 10);
        
        return response()->json($listini->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descrizione' => 'nullable|string',
            'tipo_cliente' => 'nullable|string',
            'tipo_fase' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listino = Listino::create($validator->validated());

        return response()->json($listino, 201);
    }

    public function show($id)
    {
        $listino = Listino::findOrFail($id);
        return response()->json($listino);
    }

    public function update(Request $request, $id)
    {
        $listino = Listino::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'descrizione' => 'nullable|string',
            'tipo_cliente' => 'nullable|string',
            'tipo_fase' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listino->update($validator->validated());

        return response()->json($listino);
    }

    public function destroy($id)
    {
        $listino = Listino::findOrFail($id);
        
        // Soft delete logic by setting is_active to false
        $listino->is_active = false;
        $listino->save();

        return response()->json($listino);
    }
}
