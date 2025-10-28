<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoefficienteProduzioneFotovoltaico extends Model
{
    use HasFactory;

    protected $table = 'coefficienti_produzione_fotovoltaico';
    
    protected $primaryKey = 'id_coeff';

    protected $fillable = [
        'area_geografica',
        'esposizione',
        'coefficiente_kwh_kwp',
    ];

    protected $casts = [
        'coefficiente_kwh_kwp' => 'float',
    ];
}
