<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicabilitaVoce extends Model
{
    use HasFactory;

    protected $table = 'applicabilita_voce';
    
    protected $primaryKey = null;
    
    public $incrementing = false;

    protected $fillable = [
        'fk_voce',
        'tipo_cliente',
    ];

    public function voceEconomica()
    {
        return $this->belongsTo(VoceEconomica::class, 'fk_voce', 'id_voce');
    }
}
