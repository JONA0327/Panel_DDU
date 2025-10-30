<?php

namespace App\Services\Meetings;

use App\Models\MeetingContentContainer;
use App\Models\MeetingTranscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DriveMeetingService
{
    /**
     * Retrieve meetings and statistics for a given user.
     */
    public function getOverviewForUser(User $user): array
    {
        $googleToken = $user->googleToken()
            ->with(['folders.subfolders' => fn ($query) => $query->orderBy('name')])
            ->first();

        $meetings = MeetingTranscription::query()
            ->forUser($user)
            ->with(['containers', 'groups'])
            ->orderByDesc('created_at')
            ->get();

        $sharedMeetings = MeetingTranscription::query()
            ->whereHas('groups.members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['containers', 'groups'])
            ->orderByDesc('created_at')
            ->get();

        $containerMeetings = MeetingContentContainer::query()
            ->where('username', $user->username)
            ->with(['meetings' => function ($query) {
                $query->with('groups')->orderByDesc('created_at');
            }])
            ->get()
            ->flatMap(function (MeetingContentContainer $container) {
                return $container->meetings->each(function (MeetingTranscription $meeting) use ($container) {
                    $meeting->setRelation('pivot_container', $container);
                });
            });

        $meetings = $meetings
            ->merge($sharedMeetings)
            ->merge($containerMeetings)
            ->unique('id')
            ->values();

        $stats = $this->calculateStats($meetings);

        return [$meetings, $stats, $googleToken];
    }

    /**
     * Build statistics for the meetings collection.
     */
    protected function calculateStats(Collection $meetings): array
    {
        $now = Carbon::now();

        return [
            'total' => $meetings->count(),
            'programadas' => $meetings->filter(fn (MeetingTranscription $meeting) => $meeting->status === MeetingTranscription::STATUS_SCHEDULED)->count(),
            'finalizadas' => $meetings->filter(fn (MeetingTranscription $meeting) => $meeting->status === MeetingTranscription::STATUS_COMPLETED)->count(),
            'esta_semana' => $meetings->filter(function (MeetingTranscription $meeting) use ($now) {
                if (! $meeting->started_at instanceof Carbon) {
                    return false;
                }

                return $meeting->started_at->between($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
            })->count(),
        ];
    }
}
