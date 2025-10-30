<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'current_organization_id',
        'username',
        'full_name',
        'email',
        'password',
        'roles',
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
            ->where('is_active', true);
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

    /**
     * Google token owned by the user.
     */
    public function googleToken(): HasOne
    {
        return $this->hasOne(GoogleToken::class, 'username', 'username');
    }

    /**
     * Meeting containers associated to the user.
     */
    public function meetingContainers(): HasMany
    {
        return $this->hasMany(MeetingContentContainer::class);
    }

    /**
     * Meetings synchronized for the user.
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(MeetingTranscription::class, 'user_id', 'id');
    }

    /**
     * Meeting groups created by the user.
     */
    public function ownedMeetingGroups(): HasMany
    {
        return $this->hasMany(MeetingGroup::class, 'owner_id');
    }

    /**
     * Meeting groups where the user is a member.
     */
    public function meetingGroups(): BelongsToMany
    {
        return $this->belongsToMany(MeetingGroup::class, 'meeting_group_user')->withTimestamps();
    }
}
