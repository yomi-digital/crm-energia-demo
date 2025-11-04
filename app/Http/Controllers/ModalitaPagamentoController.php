<?php

namespace App\Http\Controllers;

use App\Models\ModalitaPagamento;
use Illuminate\Http\Request;

class ModalitaPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modalitaPagamento = ModalitaPagamento::query();

        if ($request->filled('customer_type')) {
            $customerType = strtolower($request->get('customer_type'));
            $modalitaPagamento->whereHas('applicabilita', function ($query) use ($customerType) {
                $query->whereRaw('lower(tipo_cliente) = ?', [$customerType]);
            });
        }

        $modalitaPagamento = $modalitaPagamento->get();

        return response()->json($modalitaPagamento);
    }
}
