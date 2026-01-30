<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoceEconomica extends Model
{
    use HasFactory;

    protected $table = 'voci_economiche';
    
    protected $primaryKey = 'id_voce';

    protected $fillable = [
        'nome_voce',
        'tipo_voce',
        'tipo_valore',
        'valore_default',
        'anno_inizio',
        'anno_fine',
        'is_active',
        'iva',
    ];

    protected $casts = [
        'valore_default' => 'float',
        'anno_inizio' => 'integer',
        'anno_fine' => 'integer',
        'is_active' => 'boolean',
        'iva' => 'boolean',
    ];

    public function applicabilita()
    {
        return $this->hasMany(ApplicabilitaVoce::class, 'fk_voce', 'id_voce');
    }
}
