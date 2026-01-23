<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationBrand extends Model
{
    use HasFactory;

    protected $table = 'communication_brands';

    protected $fillable = [
        'id_communication',
        'id_brand',
    ];

    public function communication()
    {
        return $this->belongsTo(Communication::class, 'id_communication');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brand');
    }
}

