<?php

namespace App\Http\Controllers;

use App\Models\ModalitaPagamento;
use Illuminate\Http\Request;

class ModalitaPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modalitaPagamento = ModalitaPagamento::all();
        return response()->json($modalitaPagamento);
    }
}
