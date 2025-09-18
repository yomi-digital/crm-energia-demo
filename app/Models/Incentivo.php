<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incentivo extends Model
{
    protected $table = 'incentivi';

    protected $fillable = [
        'periodoBolletta',
        'prezzoMedioKwh',
        'spesaBollettaMensile',
        'hasPanels',
        'citta',
        'email',
        'nominativo',
        'numeroDiTelefono',
        'privacyAccepted',
        'provincia'
    ];

    protected $casts = [
        'prezzoMedioKwh' => 'float',
        'spesaBollettaMensile' => 'float',
        'privacyAccepted' => 'boolean'
    ];
}
