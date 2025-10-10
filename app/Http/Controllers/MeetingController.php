<?php

namespace App\Http\Controllers;

use App\Models\MeetingTranscription;
use App\Services\JuDecryptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    public function showDetails(Request $request): JsonResponse
    {
        $filePath = (string) $request->input('path');
        $audioUrl = (string) $request->input('audio_url', '');

        if ($filePath === '') {
            return response()->json(['error' => 'Ruta del archivo no proporcionada.'], 422);
        }

        $resolvedPath = $this->resolveFilePath($filePath);

        if (!$resolvedPath) {
            return response()->json(['error' => 'Archivo no encontrado.'], 404);
        }

        $decryptedData = JuDecryptionService::decrypt($resolvedPath);

        if (!$decryptedData) {
            return response()->json(['error' => 'No se pudo desencriptar la información de la reunión.'], 500);
        }

        $summary = data_get($decryptedData, 'summary')
            ?? data_get($decryptedData, 'resumen')
            ?? 'No disponible';

        $keyPoints = data_get($decryptedData, 'key_points', []);
        $keyPoints = is_array($keyPoints) ? array_values($keyPoints) : [];

        $segments = data_get($decryptedData, 'segments')
            ?? data_get($decryptedData, 'transcription')
            ?? [];
        $segments = is_array($segments) ? array_values($segments) : [];

        return response()->json([
            'summary' => $summary,
            'key_points' => $keyPoints,
            'segments' => $segments,
            'audio_url' => $audioUrl,
        ]);
    }

    private function resolveFilePath(string $path): ?string
    {
        $candidatePaths = [$path];

        $isAbsolute = str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:\\\\/', $path) === 1;

        if (!$isAbsolute) {
            $candidatePaths[] = storage_path($path);
            $candidatePaths[] = storage_path('app/' . ltrim($path, '/\\'));
            $candidatePaths[] = base_path($path);
            $candidatePaths[] = public_path($path);
        }

        foreach ($candidatePaths as $candidate) {
            if (!$candidate) {
                continue;
            }

            $realPath = realpath($candidate);
            if ($realPath === false || !is_file($realPath)) {
                continue;
            }

            if (
                Str::startsWith($realPath, base_path()) ||
                Str::startsWith($realPath, storage_path())
            ) {
                return $realPath;
            }
        }

        return null;
    }
}
