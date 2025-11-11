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
        'nome_tipologia',
        'note',
        'costo_extra_kwp',
        'is_active',
    ];

    protected $casts = [
        'costo_extra_kwp' => 'float',
        'is_active' => 'boolean',
    ];
}
