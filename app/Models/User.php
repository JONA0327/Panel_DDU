<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // Removed 'password' => 'hashed' to allow manual hash control
    ];

    /**
     * Get the panel memberships for this user.
     */
    public function panelMemberships()
    {
        return $this->hasMany(UserPanelMiembro::class, 'user_id', 'id');
    }

    /**
     * Get DDU panel membership for this user.
     */
    public function dduMembership()
    {
        return $this->hasOne(UserPanelMiembro::class, 'user_id', 'id')
            ->whereHas('panel', function ($query) {
                $query->where('company_name', 'DDU');
            });
    }

    /**
     * Check if user is a DDU member.
     */
    public function isDduMember()
    {
        return UserPanelMiembro::isDduMember($this->id);
    }

    /**
     * Get user's DDU role.
     */
    public function getDduRole()
    {
        $membership = $this->dduMembership;
        return $membership ? $membership->role : null;
    }
}
