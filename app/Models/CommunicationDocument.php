<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationDocument extends Model
{
    use HasFactory;

    protected $table = 'communications_documents';

    protected $fillable = [
        'communication_id',
        'name',
        'url',
    ];

    public function communication()
    {
        return $this->belongsTo(Communication::class);
    }
}
