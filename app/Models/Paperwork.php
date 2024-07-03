<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paperwork extends Model
{
    use HasFactory, SoftDeletes;

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

    public function getAddedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }
}
