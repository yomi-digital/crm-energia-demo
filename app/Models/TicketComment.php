<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment',
    ];

    public function getCreatedAtAttribute($value)
    {
        return date(config('app.datetime_format'), strtotime($value));
    }

    public function getReadAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by');
    }
}
