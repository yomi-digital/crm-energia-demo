<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'privacy',
        'business_name',
        'tax_id_code',
        'vat_number',
        'ateco_code',
        'pec',
        'unique_code',
        'category',
        'address',
        'region',
        'province',
        'city',
        'zip',
    ];

    protected $casts = [
        'privacy' => 'boolean',
    ];

    /**
     * Bootstrap del modello - imposta automaticamente confirmed_at alla creazione
     */
    protected static function boot()
    {
        parent::boot();

        // Quando viene creato un nuovo Customer, impostalo come confermato
        static::creating(function ($customer) {
            $customer->confirmed_at = now()->format('Y-m-d');
        });
    }

    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getAddedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function getConfirmedAtAttribute($value)
    {
        return $value ? date(config('app.datetime_format'), strtotime($value)) : null;
    }

    public function paperworks()
    {
        return $this->hasMany(Paperwork::class);
    }
}
