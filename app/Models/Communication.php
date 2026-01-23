<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject',
        'body',
    ];

    public function documents()
    {
        return $this->hasMany(CommunicationDocument::class);
    }

    public function brands()
    {
        return $this->belongsToMany(
            Brand::class,
            'communication_brands',
            'id_communication',
            'id_brand'
        );
    }

    public function getCreatedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function getSentAtAttribute($value)
    {
        return $value ? date(config('app.date_format'), strtotime($value)) : null;
    }
}
