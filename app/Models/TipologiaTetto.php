<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipologiaTetto extends Model
{
    use HasFactory;

    protected $table = 'tipologie_tetto';
    
    protected $primaryKey = 'id_voce';

    protected $fillable = [
        'nome_voce',
        'tipo_voce',
        'tipo_valore',
        'valore_default',
        'anni_durata_default',
    ];

    protected $casts = [
        'valore_default' => 'float',
        'anni_durata_default' => 'integer',
    ];
}
