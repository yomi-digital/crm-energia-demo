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
        'marca_inverter_salvata',
        'quantita_inverter_salvati',
        'quantita_batterie_salvate',
        'potenza_batterie_salvato',
        'marca_batteria_salvato',
        'marca_pannelli_salvato',
        'quantita_pannelli_salvato',
        'iva',
        'descrizione_prodotto_salvata',
        'link_prodotto_salvato',
    ];

    protected $casts = [
        'quantita' => 'float',
        'prezzo_unitario_salvato' => 'float',
        'capacita_batteria_salvata' => 'float',
        'kWp_salvato' => 'float',
        'potenza_inverter_salvata' => 'float',
        'potenza_batterie_salvato' => 'float',
        'quantita_inverter_salvati' => 'integer',
        'quantita_batterie_salvate' => 'integer',
        'quantita_pannelli_salvato' => 'integer',
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
