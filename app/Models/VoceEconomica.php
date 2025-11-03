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
        'anni_durata_default',
        'is_active',
    ];

    protected $casts = [
        'valore_default' => 'float',
        'anni_durata_default' => 'integer',
        'is_active' => 'boolean',
    ];

    public function applicabilita()
    {
        return $this->hasMany(ApplicabilitaVoce::class, 'fk_voce', 'id_voce');
    }
}
