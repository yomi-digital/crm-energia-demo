<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModalitaPagamento extends Model
{
    use HasFactory;

    protected $table = 'modalita_pagamento';
    
    protected $primaryKey = 'id_modalita';

    protected $fillable = [
        'nome_modalita',
        'descrizione',
    ];

    public function applicabilita()
    {
        return $this->hasMany(ApplicabilitaModalitaPagamento::class, 'fk_modalita', 'id_modalita');
    }
}
