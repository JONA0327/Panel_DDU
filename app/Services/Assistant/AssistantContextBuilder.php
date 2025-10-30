<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\AssistantDocument;
use App\Models\MeetingContentContainer;
use App\Models\MeetingTranscription;
use App\Models\User;
use App\Services\Calendar\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssistantContextBuilder
{
    public function __construct(private readonly GoogleCalendarService $calendarService)
    {
    }

    public function build(User $user, AssistantConversation $conversation, array $options = []): array
    {
        $meetingIds = Arr::wrap($options['meetings'] ?? []);
        $containerIds = Arr::wrap($options['containers'] ?? []);
        $includeCalendar = (bool) ($options['include_calendar'] ?? true);
        $userMessage = $options['user_message'] ?? null;

        // Debug temporal
        Log::info('AssistantContextBuilder::build called', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'meetingIds' => $meetingIds,
            'containerIds' => $containerIds,
            'includeCalendar' => $includeCalendar,
        ]);

        $contextParts = [];

        if (! empty($meetingIds)) {
            $contextParts[] = $this->buildMeetingsContext($user, $meetingIds, $userMessage);
        }

        if (! empty($containerIds)) {
            $contextParts[] = $this->buildContainersContext($user, $containerIds);
        }

        if ($includeCalendar) {
            $contextParts[] = $this->buildCalendarContext($user);
        }

        $contextParts[] = $this->buildDocumentsContext($conversation);

        $contextParts = array_filter($contextParts);

        return [
            'text' => implode("\n\n", $contextParts),
            'documents' => $conversation->documents,
        ];
    }

    protected function buildMeetingsContext(User $user, array $meetingIds, ?string $query = null): ?string
    {
        Log::info('buildMeetingsContext called', [
            'user_id' => $user->id,
            'meetingIds' => $meetingIds,
            'meetingIds_count' => count($meetingIds),
        ]);

    // Enviar la transcripción completa para las reuniones seleccionadas (sin truncar)
    // Nota: esto incluye todo el contenido de `segments` por reunión. Si quieres
    // volver a limitar por defecto, ajusta esta variable.
    $segmentLimit = null; // null = sin límite

        $meetings = MeetingTranscription::query()
            ->whereIn('id', $meetingIds)
            ->with(['containers'])
            ->get();

        Log::info('Meetings found', [
            'meetings_count' => $meetings->count(),
            'meetings_names' => $meetings->pluck('meeting_name')->toArray(),
        ]);

        if ($meetings->isEmpty()) {
            Log::info('No meetings found, returning null');
            return null;
        }

        $lines = [
            'Contexto de reuniones seleccionadas:',
        ];

        foreach ($meetings as $meeting) {
            $lines[] = sprintf('- Reunión "%s" (completada)', $meeting->meeting_name ?? 'Sin título');
            
            // Obtener detalles reales de la reunión desde Google Drive
            $meetingInfo = $this->getMeetingDetailsFromDrive($meeting, $user);
            
            if ($meetingInfo['summary'] && $meetingInfo['summary'] !== 'Resumen no disponible.') {
                $lines[] = '  Resumen: ' . Str::of($meetingInfo['summary'])->squish();
            }

            if (!empty($meetingInfo['key_points'])) {
                $lines[] = '  Puntos clave:';
                foreach ($meetingInfo['key_points'] as $point) {
                    $pointText = is_array($point) ? ($point['description'] ?? $point['title'] ?? $point['text'] ?? '') : (string)$point;
                    if ($pointText) {
                        $lines[] = '    • ' . Str::of($pointText)->squish();
                    }
                }
            }

            if (!empty($meetingInfo['segments'])) {
                $lines[] = '  Extracto de transcripción:';
                $transcriptLines = [];

                // Si no hay límite (necesitamos la transcripción completa), iteramos todos los segmentos y no truncamos los textos
                if (is_null($segmentLimit)) {
                    foreach ($meetingInfo['segments'] as $segment) {
                        $speaker = is_array($segment) ? ($segment['speaker'] ?? $segment['role'] ?? 'Hablante') : 'Hablante';
                        $text = is_array($segment) ? ($segment['text'] ?? $segment['content'] ?? $segment['sentence'] ?? '') : (string)$segment;
                        if ($text) {
                            // No limitar el texto: enviar la transcripción completa para búsquedas textuales
                            $transcriptLines[] = "    {$speaker}: " . Str::of($text)->squish();
                        }
                    }
                } else {
                    foreach (array_slice($meetingInfo['segments'], 0, $segmentLimit) as $segment) {
                        $speaker = is_array($segment) ? ($segment['speaker'] ?? $segment['role'] ?? 'Hablante') : 'Hablante';
                        $text = is_array($segment) ? ($segment['text'] ?? $segment['content'] ?? $segment['sentence'] ?? '') : (string)$segment;
                        if ($text) {
                            // Para fragmentos resumidos limitamos cada línea para ahorrar contexto
                            $transcriptLines[] = "    {$speaker}: " . Str::of($text)->squish()->limit(200);
                        }
                    }
                }

                if (!empty($transcriptLines)) {
                    $lines = array_merge($lines, $transcriptLines);
                    // Si había límite y existen más segmentos, avisamos que hay más
                    if (!is_null($segmentLimit) && count($meetingInfo['segments']) > $segmentLimit) {
                        $lines[] = '    [...más contenido disponible]';
                    }
                }
            }
        }

        return implode("\n", $lines);
    }

    protected function buildContainersContext(User $user, array $containerIds): ?string
    {
        $containers = MeetingContentContainer::query()
            ->where('username', $user->username)
            ->whereIn('id', $containerIds)
            ->with(['meetings'])
            ->get();

        if ($containers->isEmpty()) {
            return null;
        }

        $lines = [
            'Contexto de contenedores de reuniones:',
        ];

        foreach ($containers as $container) {
            $lines[] = sprintf('- Contenedor "%s": %s', $container->name, Str::of($container->description)->squish()->limit(200));

            foreach ($container->meetings as $meeting) {
                $lines[] = sprintf('    • Reunión %s (completada)', $meeting->meeting_name ?? 'Sin título');
            }
        }

        return implode("\n", $lines);
    }

    protected function buildCalendarContext(User $user): ?string
    {
        try {
            $events = $this->calendarService->listUpcomingEvents($user, Carbon::now(), Carbon::now()->addWeeks(2));
        } catch (\Throwable $exception) {
            Log::warning('No se pudo obtener el calendario para el asistente.', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if ($events->isEmpty()) {
            return 'No hay eventos próximos en el calendario durante las próximas dos semanas.';
        }

        $lines = ['Eventos próximos del calendario:'];

        foreach ($events as $event) {
            $lines[] = sprintf('- %s el %s de %s a %s',
                $event['summary'],
                Carbon::parse($event['start'])->translatedFormat('d \d\e F \a \l\a\s H:i'),
                Carbon::parse($event['start'])->format('H:i'),
                Carbon::parse($event['end'])->format('H:i')
            );

            if (! empty($event['description'])) {
                $lines[] = '  Descripción: ' . Str::of($event['description'])->squish()->limit(200);
            }
        }

        return implode("\n", $lines);
    }

    protected function buildDocumentsContext(AssistantConversation $conversation): ?string
    {
        $documents = $conversation->documents;

        if ($documents->isEmpty()) {
            return null;
        }

        $lines = ['Documentos adjuntos analizados:'];

        /** @var AssistantDocument $document */
        foreach ($documents as $document) {
            $lines[] = sprintf('- %s (%s)', $document->original_name, $document->mime_type ?? 'tipo desconocido');

            if ($document->summary) {
                $lines[] = '  Resumen: ' . Str::of($document->summary)->squish()->limit(300);
            } elseif ($document->extracted_text) {
                $lines[] = '  Extracto: ' . Str::of($document->extracted_text)->squish()->limit(300);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Obtiene los detalles de una reunión desde Google Drive
     */
    private function getMeetingDetailsFromDrive(MeetingTranscription $meeting, User $user): array
    {
        $defaultInfo = [
            'summary' => 'Resumen no disponible.',
            'key_points' => [],
            'segments' => [],
        ];

        if (!$meeting->transcript_drive_id) {
            return $defaultInfo;
        }

        try {
            // Buscar token del usuario o del propietario de la reunión
            $token = $user->googleToken;
            if (!$token && $meeting->username !== $user->username) {
                $owner = \App\Models\User::where('username', $meeting->username)->first();
                $token = $owner?->googleToken;
            }

            if (!$token) {
                return $defaultInfo;
            }

            $driveService = new \App\Services\UserGoogleDriveService($token);
            $juFileContent = $this->downloadFileContent($driveService, $meeting->transcript_drive_id);

            if ($juFileContent) {
                $decryptedData = \App\Services\JuDecryptionService::decryptContent($juFileContent);

                if ($decryptedData) {
                    return \App\Services\JuFileDecryption::extractMeetingInfo($decryptedData);
                }
            }

            return $defaultInfo;

        } catch (\Exception $e) {
            Log::warning('Error obteniendo detalles de reunión para asistente', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage()
            ]);
            return $defaultInfo;
        }
    }

    /**
     * Descarga el contenido de un archivo desde Google Drive
     */
    private function downloadFileContent(\App\Services\UserGoogleDriveService $driveService, string $fileId): ?string
    {
        try {
            $response = $driveService->downloadFile($fileId);
            return $response ? $response->getBody()->getContents() : null;
        } catch (\Exception $e) {
            Log::warning('Error descargando archivo de Google Drive', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Determina si la consulta requiere acceso a la transcripción completa
     */
    protected function requiresFullTranscription(?string $query): bool
    {
        if (!$query) {
            return false;
        }

        $query = strtolower($query);
        
        // Palabras clave que indican necesidad de transcripción completa
        $fullTranscriptionKeywords = [
            'fragmentos',
            'intervino',
            'dijo',
            'menciono',
            'hablo',
            'pregunto',
            'respondio',
            'comento',
            'participo',
            'converso',
            'dialogo',
            'discutio',
            'opino',
            'conversacion',
            'todas las veces',
            'cada vez que',
            'cuando dijo',
            'momentos donde',
            'partes donde',
            'citas',
            'exactas palabras',
            'textual',
            'literal'
        ];

        foreach ($fullTranscriptionKeywords as $keyword) {
            if (str_contains($query, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
