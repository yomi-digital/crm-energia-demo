<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'eventable_type',
        'eventable_id',
        'user_id',
        'event_type',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function eventable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
