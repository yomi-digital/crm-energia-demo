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
        'top_partner_fee',
        'top_fee',
        'partner_fee',
        'collaborator_fee',
        'smart_fee',
    ];

    public function getStartDateAttribute($value)
    {
        return $value ? date(config('app.date_format'), strtotime($value)) : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? date(config('app.date_format'), strtotime($value)) : null;
    }
}
