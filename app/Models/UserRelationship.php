<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelationship extends Model
{
    use HasFactory;

    protected $table = 'users_relationships';

    protected $fillable = [
        'user_id',
        'related_id',
        'role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
