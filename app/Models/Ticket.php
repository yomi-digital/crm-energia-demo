<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function getCreatedAtAttribute($value)
    {
        return date(config('app.datetime_format'), strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function getClosedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function paperwork()
    {
        return $this->belongsTo(Paperwork::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
