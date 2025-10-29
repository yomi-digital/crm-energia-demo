<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProdottoFotovoltaico;

class ProdottoFotovoltaicoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $prodotti = ProdottoFotovoltaico::query()->with('categoria');

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
