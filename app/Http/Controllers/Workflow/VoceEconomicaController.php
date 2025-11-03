<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoceEconomica;
use App\Models\ApplicabilitaVoce;

class VoceEconomicaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $vociEconomiche = VoceEconomica::query()
            ->where('is_active', true);

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

        return response()->json($vociEconomiche);
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
