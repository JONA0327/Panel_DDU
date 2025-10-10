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

        $tasks = Arr::get($json, 'tasks') ?? [];
        $tasks = $this->normaliseToStringArray($tasks);

        $segments = $this->extractSegments($json);

        return [
            'summary' => is_string($summary) ? trim($summary) : '',
            'key_points' => $keyPoints,
            'tasks' => $tasks,
            'segments' => $segments,
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
                    ?? Arr::get($segment, 'from');

                $end = Arr::get($segment, 'end')
                    ?? Arr::get($segment, 'end_time')
                    ?? Arr::get($segment, 'fin')
                    ?? Arr::get($segment, 'to');

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

    protected function attemptDecryptString(string $content, array $legacyKeys): ?array
    {
        $attempts = array_merge([
            fn () => Crypt::decryptString($content),
        ], array_map(fn ($key) => fn () => $this->decryptUsingKey($key, $content, false), $legacyKeys));

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
        $attempts = array_merge([
            fn () => Crypt::decrypt($content),
        ], array_map(fn ($key) => fn () => $this->decryptUsingKey($key, $content, true), $legacyKeys));

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
}
