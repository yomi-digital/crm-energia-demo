<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category',
        'notes',
        'enabled',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function communications()
    {
        return $this->belongsToMany(
            Communication::class,
            'communication_brands',
            'id_brand',
            'id_communication'
        );
    }

    public function getCreatedAtAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }
}
