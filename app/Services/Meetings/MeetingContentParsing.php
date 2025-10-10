<?php

namespace App\Services\Meetings;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MeetingContentParsing
{
    public function decryptJuFile(?string $content): array
    {
        $content = is_string($content) ? trim($content) : '';

        if ($content === '') {
            return ['data' => null, 'needs_encryption' => false];
        }

        $jsonData = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            return [
                'data' => $this->extractMeetingDataFromJson($jsonData),
                'needs_encryption' => true,
            ];
        }

        $legacyKeys = $this->getLegacyKeys();

        if (Str::startsWith($content, 'eyJ')) {
            $result = $this->attemptDecryptString($content, $legacyKeys);
            if ($result !== null) {
                return $result;
            }
        }

        if (str_contains($content, '"iv"') && str_contains($content, '"value"')) {
            $result = $this->attemptDecryptPayload($content, $legacyKeys);
            if ($result !== null) {
                return $result;
            }
        }

        throw new RuntimeException('Unable to decrypt .ju file content.');
    }

    public function extractMeetingDataFromJson(array $json): array
    {
        $summary = Arr::get($json, 'summary')
            ?? Arr::get($json, 'resumen')
            ?? '';

        $keyPoints = Arr::get($json, 'key_points')
            ?? Arr::get($json, 'puntos_clave')
            ?? [];
        $keyPoints = $this->normaliseToStringArray($keyPoints);

        $segments = $this->extractSegments($json);

        $actionItems = Arr::get($json, 'action_items')
            ?? Arr::get($json, 'tasks')
            ?? Arr::get($json, 'tareas')
            ?? [];

        return [
            'summary' => is_string($summary) ? trim($summary) : '',
            'key_points' => $keyPoints,
            'segments' => $segments,
            'action_items' => $this->normaliseActionItems($actionItems),
            'participants' => $this->normaliseParticipants(
                Arr::get($json, 'participants')
                    ?? Arr::get($json, 'participantes')
                    ?? Arr::get($json, 'speakers')
            ),
            'duration' => $this->normaliseDuration(
                Arr::get($json, 'duration')
                    ?? Arr::get($json, 'duracion')
                    ?? Arr::get($json, 'meeting_duration')
            ),
            'timestamp' => $this->normaliseTimestamp(
                Arr::get($json, 'timestamp')
                    ?? Arr::get($json, 'fecha')
                    ?? Arr::get($json, 'created_at')
                    ?? Arr::get($json, 'meeting_date')
            ),
            'metadata' => $this->normaliseMetadata(
                Arr::get($json, 'metadata')
                    ?? Arr::get($json, 'meta')
                    ?? []
            ),
        ];
    }

    protected function extractSegments(array $json): array
    {
        $rawSegments = Arr::get($json, 'segments');

        if (! is_array($rawSegments) || empty($rawSegments)) {
            $rawSegments = Arr::get($json, 'transcript.segments')
                ?? Arr::get($json, 'transcription.segments')
                ?? Arr::get($json, 'transcript');
        }

        if (! is_array($rawSegments)) {
            return [];
        }

        return collect($rawSegments)
            ->map(function ($segment, $index) {
                $segment = is_array($segment) ? $segment : [];

                $speaker = Arr::get($segment, 'speaker')
                    ?? Arr::get($segment, 'speaker_label')
                    ?? Arr::get($segment, 'speaker_name')
                    ?? 'Hablante '.($index + 1);

                $text = Arr::get($segment, 'text')
                    ?? Arr::get($segment, 'transcript')
                    ?? Arr::get($segment, 'content')
                    ?? '';

                $start = Arr::get($segment, 'start')
                    ?? Arr::get($segment, 'start_time')
                    ?? Arr::get($segment, 'inicio')
                    ?? Arr::get($segment, 'from')
                    ?? Arr::get($segment, 'timestamp');

                $end = Arr::get($segment, 'end')
                    ?? Arr::get($segment, 'end_time')
                    ?? Arr::get($segment, 'fin')
                    ?? Arr::get($segment, 'to')
                    ?? Arr::get($segment, 'timestamp_end');

                return [
                    'speaker' => is_string($speaker) ? $speaker : 'Hablante '.($index + 1),
                    'text' => is_string($text) ? trim($text) : '',
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->filter(fn ($segment) => $segment['text'] !== '')
            ->values()
            ->all();
    }

    protected function normaliseToStringArray($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n]+/', $value) ?: [];
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function ($item) {
                if (is_array($item)) {
                    $item = Arr::get($item, 'text') ?? Arr::get($item, 'value') ?? json_encode($item);
                }

                return is_string($item) ? trim($item) : '';
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function normaliseActionItems($value): array
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function ($item, $index) {
                if (is_string($item)) {
                    $title = trim($item);

                    if ($title === '') {
                        return null;
                    }

                    return [
                        'title' => $title,
                        'description' => null,
                        'owner' => null,
                        'due_date' => null,
                        'start_date' => null,
                        'status' => null,
                        'priority' => null,
                        'progress' => null,
                        'position' => $index,
                    ];
                }

                if (! is_array($item)) {
                    return null;
                }

                return [
                    'title' => $this->stringOrNull(
                        Arr::get($item, 'title')
                            ?? Arr::get($item, 'name')
                            ?? Arr::get($item, 'tarea')
                            ?? Arr::get($item, 'action')
                            ?? Arr::get($item, 'item')
                    ),
                    'description' => $this->stringOrNull(
                        Arr::get($item, 'description')
                            ?? Arr::get($item, 'detalle')
                            ?? Arr::get($item, 'details')
                    ),
                    'owner' => $this->stringOrNull(
                        Arr::get($item, 'owner')
                            ?? Arr::get($item, 'responsable')
                            ?? Arr::get($item, 'assigned_to')
                            ?? Arr::get($item, 'asignado')
                    ),
                    'due_date' => $this->stringOrNull(
                        Arr::get($item, 'due_date')
                            ?? Arr::get($item, 'deadline')
                            ?? Arr::get($item, 'fecha_limite')
                    ),
                    'start_date' => $this->stringOrNull(
                        Arr::get($item, 'start_date')
                            ?? Arr::get($item, 'fecha_inicio')
                    ),
                    'status' => $this->stringOrNull(
                        Arr::get($item, 'status')
                            ?? Arr::get($item, 'estado')
                    ),
                    'priority' => $this->stringOrNull(
                        Arr::get($item, 'priority')
                            ?? Arr::get($item, 'prioridad')
                    ),
                    'progress' => $this->numericOrNull(
                        Arr::get($item, 'progress')
                            ?? Arr::get($item, 'avance')
                            ?? Arr::get($item, 'progreso')
                    ),
                    'position' => $index,
                ];
            })
            ->filter(function ($item) {
                return is_array($item) && ($item['title'] ?? null);
            })
            ->values()
            ->all();
    }

    protected function normaliseParticipants($value): array
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function ($participant) {
                if (is_array($participant)) {
                    $name = Arr::get($participant, 'name')
                        ?? Arr::get($participant, 'nombre')
                        ?? Arr::get($participant, 'speaker')
                        ?? Arr::get($participant, 'label');

                    $role = Arr::get($participant, 'role')
                        ?? Arr::get($participant, 'rol')
                        ?? Arr::get($participant, 'position');

                    $normalised = [];

                    if ($name = $this->stringOrNull($name)) {
                        $normalised['name'] = $name;
                    }

                    if ($role = $this->stringOrNull($role)) {
                        $normalised['role'] = $role;
                    }

                    return $normalised ?: null;
                }

                if (is_string($participant)) {
                    $name = trim($participant);

                    return $name === '' ? null : ['name' => $name];
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function normaliseMetadata($value): array
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->mapWithKeys(function ($item, $key) {
                $stringKey = $this->stringOrNull($key) ?? 'meta';

                if ($item instanceof Collection) {
                    $item = $item->all();
                }

                if (is_array($item)) {
                    $item = json_encode($item, JSON_UNESCAPED_UNICODE);
                }

                return [$stringKey => $this->stringOrNull($item)];
            })
            ->filter()
            ->all();
    }

    protected function normaliseDuration($value)
    {
        if (is_numeric($value)) {
            return $this->numericOrNull($value);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return null;
            }

            if (is_numeric($trimmed)) {
                return $this->numericOrNull($trimmed);
            }

            return $trimmed;
        }

        return null;
    }

    protected function normaliseTimestamp($value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_numeric($value)) {
            return date(DATE_ATOM, (int) $value);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        return null;
    }

    protected function stringOrNull($value): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        return null;
    }

    protected function numericOrNull($value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    protected function attemptDecryptString(string $content, array $legacyKeys): ?array
    {
        $candidates = $this->buildEncryptedPayloadCandidates($content);

        $attempts = [];

        foreach ($candidates as $candidate) {
            $attempts[] = fn () => Crypt::decryptString($candidate);
        }

        foreach ($legacyKeys as $key) {
            foreach ($candidates as $candidate) {
                $attempts[] = fn () => $this->decryptUsingKey($key, $candidate, false);
            }
        }

        foreach ($attempts as $attempt) {
            try {
                $payload = $attempt();
                $json = json_decode($payload, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    return [
                        'data' => $this->extractMeetingDataFromJson($json),
                        'needs_encryption' => false,
                    ];
                }
            } catch (\Throwable $exception) {
                Log::debug('Failed to decrypt .ju string payload', ['error' => $exception->getMessage()]);
            }
        }

        return null;
    }

    protected function attemptDecryptPayload(string $content, array $legacyKeys): ?array
    {
        $candidates = $this->buildEncryptedPayloadCandidates($content);

        $attempts = [];

        foreach ($candidates as $candidate) {
            $attempts[] = fn () => Crypt::decrypt($candidate);
        }

        foreach ($legacyKeys as $key) {
            foreach ($candidates as $candidate) {
                $attempts[] = fn () => $this->decryptUsingKey($key, $candidate, true);
            }
        }

        foreach ($attempts as $attempt) {
            try {
                $payload = $attempt();
                $json = is_array($payload) ? $payload : json_decode((string) $payload, true);

                if (is_array($json)) {
                    return [
                        'data' => $this->extractMeetingDataFromJson($json),
                        'needs_encryption' => false,
                    ];
                }
            } catch (\Throwable $exception) {
                Log::debug('Failed to decrypt .ju JSON payload', ['error' => $exception->getMessage()]);
            }
        }

        return null;
    }

    protected function decryptUsingKey(string $key, string $content, bool $jsonPayload)
    {
        $key = $this->parseKey($key);
        $encrypter = new Encrypter($key, config('app.cipher'));

        return $jsonPayload ? $encrypter->decrypt($content) : $encrypter->decryptString($content);
    }

    protected function parseKey(string $key): string
    {
        $trimmed = trim($key);
        if ($trimmed === '') {
            throw new RuntimeException('Encryption key cannot be empty.');
        }

        if (Str::startsWith($trimmed, 'base64:')) {
            $decoded = base64_decode(substr($trimmed, 7), true);
            if ($decoded === false) {
                throw new RuntimeException('Invalid base64 encryption key.');
            }

            return $decoded;
        }

        return $trimmed;
    }

    protected function getLegacyKeys(): array
    {
        $legacy = env('LEGACY_APP_KEYS', '');

        return collect(explode(',', (string) $legacy))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    protected function buildEncryptedPayloadCandidates(string $content): array
    {
        $baseCandidates = collect([
            $content,
            trim($content),
            preg_replace('/\s+/', '', $content),
        ])->filter(fn ($candidate) => is_string($candidate) && $candidate !== '');

        $decoded = base64_decode($content, true);
        if ($decoded !== false && $decoded !== '') {
            $baseCandidates = $baseCandidates->merge([$decoded, trim($decoded)]);
        }

        $baseCandidates = $baseCandidates
            ->filter(fn ($candidate) => is_string($candidate) && $candidate !== '')
            ->unique()
            ->values();

        $additional = $baseCandidates
            ->filter(fn ($candidate) => $this->looksLikeLaravelPayloadJson($candidate))
            ->map(fn ($candidate) => base64_encode($candidate));

        return $baseCandidates
            ->merge($additional)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function looksLikeLaravelPayloadJson(string $value): bool
    {
        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return false;
        }

        return isset($decoded['iv'], $decoded['value'], $decoded['mac'])
            && is_string($decoded['iv'])
            && is_string($decoded['value'])
            && is_string($decoded['mac']);
    }
}
