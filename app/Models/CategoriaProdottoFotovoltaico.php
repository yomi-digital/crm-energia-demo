<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProdottoFotovoltaico extends Model
{
    use HasFactory;

    protected $table = 'categorie_prodotto_fotovoltaico';
    
    protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'nome_categoria',
        'descrizione',
    ];

    public function prodotti()
    {
        return $this->hasMany(ProdottoFotovoltaico::class, 'fk_categoria', 'id_categoria');
    }
}
