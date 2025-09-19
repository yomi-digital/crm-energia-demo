<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incentivo extends Model
{
    protected $table = 'incentivi';

    protected $fillable = [
        'periodoBolletta',
        'kwhSpesi',
        'spesaBollettaMensile',
        'hasPanels',
        'citta',
        'email',
        'nominativo',
        'numeroDiTelefono',
        'privacyAccepted',
        'provincia',
        'incentivo_cer',
        'incentivo_dedicated',
        'incentivo_pod'
    ];

    protected $casts = [
        'kwhSpesi' => 'float',
        'spesaBollettaMensile' => 'float',
        'privacyAccepted' => 'boolean',
        'incentivo_cer' => 'float',
        'incentivo_dedicated' => 'float',
        'incentivo_pod' => 'float'
    ];

    /**
     * Relazione con Customer basata su email
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'email', 'email');
    }
}
