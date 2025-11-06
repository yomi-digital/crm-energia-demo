<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DettaglioBusinessPlan extends Model
{
    use HasFactory;

    protected $table = 'dettaglio_business_plan';
    
    protected $primaryKey = 'id_bp';

    protected $fillable = [
        'fk_preventivo',
        'anno_simulazione',
        'costo_annuo_investimento',
        'costo_annuo_assicurazione',
        'costo_annuo_manutenzione',
        'ricavo_risparmio_bolletta',
        'ricavo_vendita_eccedenze',
        'ricavo_incentivo_cer',
        'ricavo_fondo_perduto',
        'incentivo_pnnr',
        'detrazione_fiscale',
        'sconto',
        'flusso_cassa_annuo',
        'flusso_cassa_cumulato',
    ];

    protected $casts = [
        'anno_simulazione' => 'integer',
        'costo_annuo_investimento' => 'float',
        'costo_annuo_assicurazione' => 'float',
        'costo_annuo_manutenzione' => 'float',
        'ricavo_risparmio_bolletta' => 'float',
        'ricavo_vendita_eccedenze' => 'float',
        'ricavo_incentivo_cer' => 'float',
        'ricavo_fondo_perduto' => 'float',
        'incentivo_pnnr' => 'float',
        'detrazione_fiscale' => 'float',
        'sconto' => 'float',
        'flusso_cassa_annuo' => 'float',
        'flusso_cassa_cumulato' => 'float',
    ];

    public function preventivo()
    {
        return $this->belongsTo(Preventivo::class, 'fk_preventivo', 'id_preventivo');
    }
}
