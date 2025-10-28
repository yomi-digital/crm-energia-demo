<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventivoVoceEconomica extends Model
{
    use HasFactory;

    protected $table = 'preventivi_voci_economiche';
    
    protected $primaryKey = 'id_dettaglio';

    protected $fillable = [
        'fk_preventivo',
        'nome_voce_salvato',
        'tipo_voce_salvata',
        'valore_applicato',
        'tipo_valore_salvato',
        'anni_durata_agevolazione_salvata',
    ];

    protected $casts = [
        'valore_applicato' => 'float',
        'anni_durata_agevolazione_salvata' => 'float',
    ];

    public function preventivo()
    {
        return $this->belongsTo(Preventivo::class, 'fk_preventivo', 'id_preventivo');
    }
}
