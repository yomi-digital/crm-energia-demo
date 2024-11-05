<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'parent',
        'agent_id',
        'agent',
        'brand_id',
        'brand',
        'product_id',
        'product',
        'order_code',
        'paperwork_id',
        'inserted_at',
        'activated_at',
        'status',
        'payout',
        'payout_confirmed',
    ];
}
