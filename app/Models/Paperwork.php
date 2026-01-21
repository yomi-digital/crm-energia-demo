<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paperwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'appointment_id',
        'manager_id',
        'product_id',
        'mandate_id',
        'account_pod_pdr',
        'annual_consumption',
        'contract_type',
        'category',
        'type',
        'energy_type',
        'mobile_type',
        'coverage',
        'previous_provider',
        'notes',
        'owner_notes',
        'transfers_history',
        'order_code',
        'order_status',
        'order_substatus',
        'partner_sent_at',
        'partner_outcome',
        'partner_outcome_at',
        'paid',
        'canceled_at',
        'expired_at',
        'confirmed_at',
        'confirmed_by',
        'created_by',
        'catasto',
        'foglio',
        'particella',
        'sub',
        'indirizzo_installazione',
        'shipping',
        'visura',
        'other',
    ];

    protected $casts = [
        'transfers_history' => 'array',
        'partner_sent_at' => 'date',
        'partner_outcome_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function mandate()
    {
        return $this->belongsTo(Mandate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function documents()
    {
        return $this->hasMany(PaperworkDocument::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketAttachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAddedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function events()
    {
        return $this->morphMany(Event::class, 'eventable');
    }
}
