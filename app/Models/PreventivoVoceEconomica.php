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
        'iva',
        'anno_inizio_salvato',
        'anno_fine_salvato',
    ];

    protected $casts = [
        'valore_applicato' => 'float',
        'iva' => 'integer',
        'anno_inizio_salvato' => 'float',
        'anno_fine_salvato' => 'float',
    ];

    public function preventivo()
    {
        return $this->belongsTo(Preventivo::class, 'fk_preventivo', 'id_preventivo');
    }
}
