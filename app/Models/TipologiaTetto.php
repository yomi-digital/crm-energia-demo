<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipologiaTetto extends Model
{
    use HasFactory;

    protected $table = 'tipologie_tetto';
    
    protected $primaryKey = 'id_voce';

    protected $fillable = [
        'nome_tipologia',
        'note',
    ];

    protected $casts = [
        // Rimuovi i cast per i campi eliminati, se presenti
    ];
}
