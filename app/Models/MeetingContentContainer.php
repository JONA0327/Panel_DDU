<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MeetingContentContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'google_token_id',
        'name',
        'description',
        'google_folder_id',
    ];

    /**
     * The user that owns the container.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Associated Google token.
     */
    public function googleToken(): BelongsTo
    {
        return $this->belongsTo(GoogleToken::class);
    }

    /**
     * Meetings related to the container.
     */
    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(MeetingTranscription::class, 'meeting_content_relations', 'container_id', 'meeting_id')
            ->withTimestamps();
    }
}
