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
        abort(405, 'Operazione non consentita.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(
            CoefficienteProduzioneFotovoltaico::findOrFail($id)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $coefficiente = CoefficienteProduzioneFotovoltaico::findOrFail($id);

        $validator = \Validator::make(
            $request->all(),
            [
                'coefficiente_kwh_kwp' => ['required', 'numeric', 'gt:0'],
            ],
            [
                'coefficiente_kwh_kwp.required' => 'Specificare il coefficiente kWh/kWp.',
                'coefficiente_kwh_kwp.numeric' => 'Il coefficiente deve essere un numero.',
                'coefficiente_kwh_kwp.gt' => 'Il coefficiente deve essere maggiore di 0.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $coefficiente->coefficiente_kwh_kwp = $validator->validated()['coefficiente_kwh_kwp'];
        $coefficiente->save();

        return response()->json($coefficiente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort(405, 'Operazione non consentita.');
    }
}
