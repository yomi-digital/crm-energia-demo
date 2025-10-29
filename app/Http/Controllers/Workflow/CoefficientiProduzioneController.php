<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoefficienteProduzioneFotovoltaico;

class CoefficientiProduzioneController extends Controller
{
    public function index(Request $request)
    {
        $coefficienti = CoefficienteProduzioneFotovoltaico::query();

        if ($request->get('q')) {
            $search = $request->get('q');
            $coefficienti->where(function ($query) use ($search) {
                $query->where('area_geografica', 'like', "%{$search}%")
                    ->orWhere('esposizione', 'like', "%{$search}%")
                    ->orWhere('coefficiente_kwh_kwp', 'like', "%{$search}%");
            });
        }

        $coefficienti = $coefficienti->get();

        return response()->json($coefficienti);
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
