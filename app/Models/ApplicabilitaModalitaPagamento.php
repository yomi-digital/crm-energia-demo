<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicabilitaModalitaPagamento extends Model
{
    use HasFactory;

    protected $table = 'applicabilita_modalita_pagamento';
    
    protected $primaryKey = null;
    
    public $incrementing = false;

    protected $fillable = [
        'fk_modalita',
        'tipo_cliente',
    ];

    public function modalitaPagamento()
    {
        return $this->belongsTo(ModalitaPagamento::class, 'fk_modalita', 'id_modalita');
    }
}
