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
        return date(config('app.datetime_format'), strtotime($value));
    }

    public function paperworks()
    {
        return $this->hasMany(Paperwork::class);
    }
}
