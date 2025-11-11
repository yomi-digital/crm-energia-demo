<?php

namespace App\Http\Controllers;

use App\Models\ModalitaPagamento;
use App\Models\ApplicabilitaModalitaPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModalitaPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modalitaPagamento = ModalitaPagamento::query()->with('applicabilita');

        $isActiveKey = $request->has('is_active')
            ? 'is_active'
            : ($request->has('isActive') ? 'isActive' : null);

        if ($isActiveKey !== null) {
            $modalitaPagamento->where('is_active', $request->boolean($isActiveKey));
        } else {
            $modalitaPagamento->where('is_active', true);
        }

        if ($request->filled('customer_type')) {
            $customerType = strtolower($request->get('customer_type'));
            $modalitaPagamento->whereHas('applicabilita', function ($query) use ($customerType) {
                $query->whereRaw('lower(tipo_cliente) = ?', [$customerType]);
            });
        }

        if ($request->filled('q')) {
            $search = $request->get('q');
            $modalitaPagamento->where(function ($query) use ($search) {
                $query->where('nome_modalita', 'like', "%{$search}%")
                    ->orWhere('descrizione', 'like', "%{$search}%");
            });
        }

        $modalitaPagamento = $request->filled('itemsPerPage')
            ? $modalitaPagamento->paginate((int) $request->get('itemsPerPage', 10))
            : $modalitaPagamento->get();

        return response()->json($modalitaPagamento);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nome_modalita' => ['required', 'string', 'max:255'],
                'descrizione' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
                'tipo_cliente' => ['nullable', 'array'],
                'tipo_cliente.*' => ['in:RESIDENZIALE,BUSINESS'],
            ],
            [
                'nome_modalita.required' => 'Specificare la modalità di pagamento.',
                'nome_modalita.string' => 'La modalità di pagamento deve essere una stringa.',
                'nome_modalita.max' => 'La modalità di pagamento non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
                'tipo_cliente.array' => 'Il campo tipo_cliente deve essere un array.',
                'tipo_cliente.*.in' => 'Le tipologie clienti consentite sono solo RESIDENZIALE o BUSINESS.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $tipoCliente = $payload['tipo_cliente'] ?? ['RESIDENZIALE', 'BUSINESS'];

        unset($payload['tipo_cliente']);

        $modalitaPagamento = ModalitaPagamento::create($payload);

        $clienti = collect($tipoCliente)->unique()->map(function ($clientType) use ($modalitaPagamento) {
            return [
                'fk_modalita' => $modalitaPagamento->id_modalita,
                'tipo_cliente' => $clientType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        ApplicabilitaModalitaPagamento::insert($clienti->all());

        return response()->json($modalitaPagamento->load('applicabilita'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $modalitaPagamento = ModalitaPagamento::with('applicabilita')->findOrFail($id);

        return response()->json($modalitaPagamento);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $modalitaPagamento = ModalitaPagamento::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'nome_modalita' => ['sometimes', 'required', 'string', 'max:255'],
                'descrizione' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
                'tipo_cliente' => ['nullable', 'array'],
                'tipo_cliente.*' => ['in:RESIDENZIALE,BUSINESS'],
            ],
            [
                'nome_modalita.required' => 'Specificare la modalità di pagamento.',
                'nome_modalita.string' => 'La modalità di pagamento deve essere una stringa.',
                'nome_modalita.max' => 'La modalità di pagamento non può superare 255 caratteri.',
                'descrizione.string' => 'La descrizione deve essere un testo valido.',
                'is_active.boolean' => 'Il campo is_active deve essere un booleano.',
                'tipo_cliente.array' => 'Il campo tipo_cliente deve essere un array.',
                'tipo_cliente.*.in' => 'Le tipologie clienti consentite sono solo RESIDENZIALE o BUSINESS.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errore di validazione.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $tipoCliente = null;
        if (array_key_exists('tipo_cliente', $payload)) {
            $tipoCliente = $payload['tipo_cliente'];
            unset($payload['tipo_cliente']);
        }

        $modalitaPagamento->fill($payload);
        $modalitaPagamento->save();

        if (is_array($tipoCliente)) {
            $clienti = collect($tipoCliente)->unique();

            $modalitaPagamento->applicabilita()->delete();

            if ($clienti->isEmpty()) {
                $clienti = collect(['RESIDENZIALE', 'BUSINESS']);
            }

            $modalitaPagamento->applicabilita()->createMany(
                $clienti->map(function ($clientType) {
                    return ['tipo_cliente' => $clientType];
                })->all()
            );
        }

        return response()->json($modalitaPagamento->load('applicabilita'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $modalitaPagamento = ModalitaPagamento::findOrFail($id);

        if (! $modalitaPagamento->is_active) {
            return response()->json([
                'message' => 'La modalità di pagamento è già stata disattivata.',
            ], 400);
        }

        $modalitaPagamento->is_active = false;
        $modalitaPagamento->save();

        return response()->json($modalitaPagamento->fresh('applicabilita'), 200);
    }
}
