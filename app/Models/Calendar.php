<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $table = 'calendar';

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id', 'id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
