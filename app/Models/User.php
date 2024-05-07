<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
        'email',
        'join_date',
        'phone_number',
        'status',
        'role_name',
        'email',
        'role_name',
        'avatar',
        'position',
        'agent_id',
        'password',
        'merchant_id',
        'user_name',
        'mobile_number',
        'role_id',
        'timezone_id',
        'url'
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->hasOne(User::class, 'id', 'agent_id');
    }

    public function merchant()
    {
        return $this->hasOne(User::class, 'agent_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    function timezone() {
        return $this->belongsTo(Timezone::class, 'timezone_id');
    }
}
