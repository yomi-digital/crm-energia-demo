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
        'last_name',
        'email',
        'password',
        'must_change_password',
        'phone',
        'avatar',
        'enabled',
        'team_leader',
        'extractor',
        'manager_id',
        'agency_id',
        'area',
        'agent_code',
        'ip',
        'last_login_at',
        'last_logout_at',
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
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
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

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brands_users', 'user_id', 'brand_id')->withPivot(['race', 'bonus', 'pay_level']);
    }

    public function manager()
    {
        return $this->hasOne(Manager::class, 'id', 'manager_id');
    }

    public function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }

    public function relationships()
    {
        return $this->hasManyThrough(User::class, UserRelationship::class, 'related_id', 'id', 'id', 'id');
        // return $this->belongsToMany(User::class, 'users_agents', 'user_id', 'agent_id');
    }

    public function agents()
    {
        return $this->relationships()->where('role', 'agente');
        // return $this->belongsToMany(User::class, 'users_agents', 'user_id', 'agent_id');
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
