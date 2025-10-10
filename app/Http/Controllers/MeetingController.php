<?php

namespace App\Http\Controllers;

use App\Models\MeetingTranscription;
use App\Services\Meetings\MeetingContentParsing;
use App\Services\JuFileDecryption;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MeetingController extends Controller
{
    public function show(MeetingTranscription $meeting, MeetingContentParsing $parser): JsonResponse
    {
        $user = Auth::user();

        if ($meeting->user_id !== optional($user)->id && $meeting->username !== optional($user)->username) {
            abort(403);
        }

        $meeting->load(['containers:id,name', 'tasks' => function ($query) {
            $query->orderBy('fecha_limite')->orderBy('created_at');
        }]);

        $juResult = ['data' => null, 'needs_encryption' => false];
        $juError = null;

        // Intentar obtener contenido del archivo .ju usando múltiples métodos
        if ($juContent = $this->resolveJuContent($meeting)) {
            try {
                $juResult = $parser->decryptJuFile($juContent);
            } catch (\Throwable $exception) {
                $juError = $exception->getMessage();
                Log::warning('Unable to parse .ju file for meeting', [
                    'meeting_id' => $meeting->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        // Si no se pudo obtener desde metadata, buscar archivo .ju en el sistema
        if (!$juResult['data'] && $user->username) {
            try {
                $juFilePath = JuFileDecryption::findJuFileForMeeting($meeting->meeting_name, $user->username);

                if ($juFilePath) {
                    $decryptedContent = JuFileDecryption::decrypt($juFilePath);

                    if ($decryptedContent) {
                        $juResult['data'] = JuFileDecryption::extractMeetingInfo($decryptedContent);
                        $juError = null;

                        Log::info("Archivo .ju desencriptado exitosamente para reunión {$meeting->id} desde {$juFilePath}");
                    }
                }
            } catch (\Throwable $exception) {
                Log::error("Error procesando archivo .ju para reunión {$meeting->id}: " . $exception->getMessage());
                if (!$juError) {
                    $juError = "No se pudo procesar el archivo .ju: " . $exception->getMessage();
                }
            }
        }

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
            'ju' => $juResult['data'],
            'ju_needs_encryption' => $juResult['needs_encryption'] ?? false,
            'ju_error' => $juError,
            'tasks' => $tasks,
        ]);
    }

    protected function resolveJuContent(MeetingTranscription $meeting): ?string
    {
        $metadata = $meeting->metadata;

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            $metadata = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (! is_array($metadata)) {
            return null;
        }

        $contentCandidates = [
            Arr::get($metadata, 'ju_content'),
            Arr::get($metadata, 'juFile.content'),
            Arr::get($metadata, 'ju_file.content'),
            Arr::get($metadata, 'ju.data'),
        ];

        foreach ($contentCandidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        $pathCandidates = [
            Arr::get($metadata, 'ju_file_path'),
            Arr::get($metadata, 'juFile.path'),
            Arr::get($metadata, 'ju_file.path'),
            Arr::get($metadata, 'paths.ju'),
        ];

        foreach ($pathCandidates as $path) {
            if (is_string($path)) {
                // Intentar primero con Storage de Laravel
                if (Storage::exists($path)) {
                    return Storage::get($path);
                }

                // Intentar como ruta absoluta del sistema
                if (file_exists($path)) {
                    $content = file_get_contents($path);
                    if ($content !== false) {
                        return $content;
                    }
                }
            }
        }

        return null;
    }
}
