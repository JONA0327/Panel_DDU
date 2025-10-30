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

        $contextParts = [];

        if (! empty($meetingIds)) {
            $contextParts[] = $this->buildMeetingsContext($user, $meetingIds);
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

    protected function buildMeetingsContext(User $user, array $meetingIds): ?string
    {
        $meetings = MeetingTranscription::query()
            ->whereIn('id', $meetingIds)
            ->with(['containers'])
            ->get();

        if ($meetings->isEmpty()) {
            return null;
        }

        $lines = [
            'Contexto de reuniones seleccionadas:',
        ];

        foreach ($meetings as $meeting) {
            $metadata = $meeting->metadata ?? [];
            $summary = Arr::get($metadata, 'summary')
                ?? Arr::get($metadata, 'resumen')
                ?? Arr::get($metadata, 'general_summary');
            $keyPoints = Arr::wrap(Arr::get($metadata, 'key_points') ?? Arr::get($metadata, 'puntos_clave', []));
            $transcript = Arr::get($metadata, 'transcript')
                ?? Arr::get($metadata, 'transcripcion')
                ?? null;

            $lines[] = sprintf('- Reunión "%s" (%s)', $meeting->meeting_name ?? 'Sin título', $meeting->status_label ?? 'estado desconocido');

            if ($summary) {
                $lines[] = '  Resumen: ' . Str::of($summary)->squish();
            }

            if (! empty($keyPoints)) {
                $lines[] = '  Puntos clave:';
                foreach ($keyPoints as $point) {
                    $lines[] = '    • ' . Str::of($point)->squish();
                }
            }

            if ($transcript) {
                $lines[] = '  Extracto de transcripción: ' . Str::of($transcript)->squish()->limit(600);
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
                $lines[] = sprintf('    • Reunión %s (%s)', $meeting->meeting_name ?? 'Sin título', $meeting->status_label ?? 'estado desconocido');
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
}
