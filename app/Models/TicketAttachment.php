<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'paperwork_id',
        'name',
        'url',
        'mime_type',
        'size',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function paperwork()
    {
        return $this->belongsTo(Paperwork::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return date(config('app.datetime_format'), strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }
} 
