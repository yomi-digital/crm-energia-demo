<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feeband extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'fee_type',
        'management_fee',
        'getter_fee',
        'agent_fee',
        'structure_fee',
        'structure_top_fee',
        'salesperson_fee',
    ];

    public function getStartDateAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }

    public function getEndDateAttribute($value)
    {
        return date(config('app.date_format'), strtotime($value));
    }
}
