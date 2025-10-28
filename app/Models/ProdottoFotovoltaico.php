<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdottoFotovoltaico extends Model
{
    use HasFactory;

    protected $table = 'prodotti_fotovoltaico';
    
    protected $primaryKey = 'id_prodotto';

    protected $fillable = [
        'fk_categoria',
        'codice_prodotto',
        'descrizione',
        'potenza_kwp',
        'capacita_kwh',
        'prezzo_base',
    ];

    protected $casts = [
        'potenza_kwp' => 'float',
        'capacita_kwh' => 'float',
        'prezzo_base' => 'float',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProdottoFotovoltaico::class, 'fk_categoria', 'id_categoria');
    }

    public function dettagliPreventivi()
    {
        return $this->hasMany(DettaglioProdottoPreventivo::class, 'fk_prodotto', 'id_prodotto');
    }
}
