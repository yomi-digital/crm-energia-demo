<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listino extends Model
{
    use HasFactory;

    protected $table = 'listini';

    protected $fillable = [
        'nome',
        'descrizione',
        'tipo_cliente',
        'tipo_fase',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prodotti()
    {
        return $this->belongsToMany(
            ProdottoFotovoltaico::class, 
            'listino_prodotto_fotovoltaico', 
            'fk_listino', 
            'fk_prodotto'
        )->withTimestamps();
    }
}
