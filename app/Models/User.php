<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function getDefaultGuardName(): string { return 'api'; }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fullName()
    {
        return trim(implode(' ', [$this->name, $this->last_name]));
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function manager()
    {
        return $this->hasOne(Manager::class, 'id', 'manager_id');
    }

    public function structure()
    {
        return $this->hasOne(Structure::class, 'id', 'structure_id');
    }

    public function relationships()
    {
        return $this->hasManyThrough(User::class, UserRelationship::class, 'user_id', 'related_id')->withPivot('role');
        // return $this->belongsToMany(User::class, 'users_agents', 'user_id', 'agent_id');
    }

    public function agents()
    {
        return $this->relationships()->where('role', 'agente');
        // return $this->belongsToMany(User::class, 'users_agents', 'user_id', 'agent_id');
    }
}
