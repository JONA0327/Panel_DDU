<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class MeetingTranscription extends Model
{
    use HasFactory;

    protected $table = 'transcriptions_laravel';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'username',
        'meeting_name',
        'meeting_description',
        'status',
        'duration_seconds',
        'transcript_drive_id',
        'transcript_download_url',
        'audio_drive_id',
        'audio_download_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Containers that include this meeting.
     */
    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(MeetingContentContainer::class, 'meeting_content_relations', 'meeting_id', 'container_id')
            ->withTimestamps();
    }

    /**
     * Scope meetings for a given user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('username', $user->username);
    }

    /**
     * Human readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'Programada',
            self::STATUS_PROCESSING => 'En proceso',
            self::STATUS_COMPLETED => 'Finalizada',
            self::STATUS_FAILED => 'Error',
            default => ucfirst($this->status ?? 'Desconocido'),
        };
    }

    /**
     * CSS classes for status badge.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'bg-blue-100 text-blue-800',
            self::STATUS_PROCESSING => 'bg-amber-100 text-amber-700',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-700',
            self::STATUS_FAILED => 'bg-red-100 text-red-700',
            default => 'bg-gray-200 text-gray-700',
        };
    }

    /**
     * Duration in minutes helper.
     */
    public function getDurationMinutesAttribute(): ?int
    {
        if ($this->duration_seconds) {
            return (int) ceil($this->duration_seconds / 60);
        }

        return null;
    }
}
