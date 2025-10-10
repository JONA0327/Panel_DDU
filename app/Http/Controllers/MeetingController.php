<?php

namespace App\Http\Controllers;

use App\Models\MeetingTranscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    public function show(MeetingTranscription $meeting): JsonResponse
    {
        $user = Auth::user();

        if ($meeting->user_id !== optional($user)->id && $meeting->username !== optional($user)->username) {
            abort(403);
        }

        $meeting->load(['containers:id,name', 'tasks' => function ($query) {
            $query->orderBy('fecha_limite')->orderBy('created_at');
        }]);

        $meetingData = [
            'id' => $meeting->id,
            'name' => $meeting->meeting_name,
            'description' => $meeting->meeting_description,
            'status' => $meeting->status_label,
            'started_at' => optional($meeting->started_at)->toIso8601String(),
            'ended_at' => optional($meeting->ended_at)->toIso8601String(),
            'duration_minutes' => $meeting->duration_minutes,
            'audio_url' => $meeting->audio_download_url,
            'transcript_url' => $meeting->transcript_download_url,
            'containers' => $meeting->containers->map(fn ($container) => [
                'id' => $container->id,
                'name' => $container->name,
            ])->values(),
        ];

        $tasks = $meeting->tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'tarea' => $task->tarea,
                'prioridad' => $task->prioridad,
                'fecha_inicio' => optional($task->fecha_inicio)->toDateString(),
                'fecha_limite' => optional($task->fecha_limite)->toDateString(),
                'descripcion' => $task->descripcion,
                'progreso' => $task->progreso,
            ];
        })->values();

        return response()->json([
            'meeting' => $meetingData,
            'tasks' => $tasks,
        ]);
    }
}
