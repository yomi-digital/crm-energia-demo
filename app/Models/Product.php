<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand_id',
        'price',
        'notes',
        'enabled',
        'discount_percent',
        'fee_type',
        'management_fee',
        'getter_fee',
        'agent_fee',
        'structure_fee',
        'structure_top_fee',
        'salesperson_fee',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function feebands()
    {
        return $this->hasMany(Feeband::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }
}
