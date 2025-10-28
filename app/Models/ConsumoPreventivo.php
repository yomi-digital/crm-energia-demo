<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoPreventivo extends Model
{
    use HasFactory;

    protected $table = 'consumi_preventivo';
    
    protected $primaryKey = 'id_consumo';

    protected $fillable = [
        'fk_preventivo',
        'anno_partenza',
        'mese_partenza',
        'costo_kwh_bolletta_medio',
        'costo_kwh_bolletta_totale',
        'totale_consumo_annuo',
        'totale_costi_annui',
        'tipologia_bolletta',
        'dettagli_consumo_json',
        'consumo_diurno_annuo',
        'consumo_notturno_annuo',
        'capacita_batteria_consigliata',
    ];

    protected $casts = [
        'costo_kwh_bolletta_medio' => 'float',
        'costo_kwh_bolletta_totale' => 'float',
        'totale_consumo_annuo' => 'float',
        'totale_costi_annui' => 'float',
        'consumo_diurno_annuo' => 'float',
        'consumo_notturno_annuo' => 'float',
        'capacita_batteria_consigliata' => 'float',
    ];

    public function preventivo()
    {
        return $this->belongsTo(Preventivo::class, 'fk_preventivo', 'id_preventivo');
    }
}
