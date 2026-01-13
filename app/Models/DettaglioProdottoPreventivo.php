<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DettaglioProdottoPreventivo extends Model
{
    use HasFactory;

    protected $table = 'dettagli_prodotto_preventivo';
    
    protected $primaryKey = 'id_dettaglio';

    protected $fillable = [
        'fk_preventivo',
        'fk_prodotto',
        'nome_prodotto_salvato',
        'categoria_prodotto_salvata',
        'quantita',
        'prezzo_unitario_salvato',
        'capacita_batteria_salvata',
        'kWp_salvato',
        'potenza_inverter_salvata',
        'marca_salvata',
        'iva',
        'descrizione_prodotto_salvata',
    ];

    protected $casts = [
        'quantita' => 'float',
        'prezzo_unitario_salvato' => 'float',
        'capacita_batteria_salvata' => 'float',
        'kWp_salvato' => 'float',
        'potenza_inverter_salvata' => 'float',
        'iva' => 'integer',
    ];

    public function preventivo()
    {
        return $this->belongsTo(Preventivo::class, 'fk_preventivo', 'id_preventivo');
    }

    public function prodotto()
    {
        return $this->belongsTo(ProdottoFotovoltaico::class, 'fk_prodotto', 'id_prodotto');
    }
}
