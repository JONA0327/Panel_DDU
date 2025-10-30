<?php

namespace App\Services\Assistant;

use App\Models\AssistantSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiClient
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => config('services.openai.base_uri', 'https://api.openai.com/v1/'),
            'timeout' => 120,
        ]);
    }

    public function isConfigured(?AssistantSetting $settings): bool
    {
        return (bool) ($settings?->openai_api_key);
    }

    public function createChatCompletion(AssistantSetting $settings, array $messages, array $options = []): array
    {
        if (! $this->isConfigured($settings)) {
            throw new RuntimeException('El asistente no está configurado con una API key de OpenAI.');
        }

        $payload = array_merge([
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'messages' => $messages,
            'temperature' => 0.3,
        ], $options);

        try {
            $response = $this->httpClient->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $settings->openai_api_key,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (GuzzleException $exception) {
            Log::error('Error comunicándose con OpenAI', [
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('No fue posible obtener una respuesta de OpenAI en este momento.');
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data) || empty($data['choices'])) {
            Log::error('Respuesta inesperada de OpenAI', [
                'response' => $data,
            ]);

            throw new RuntimeException('OpenAI devolvió una respuesta inválida.');
        }

        return $data;
    }

    public function extractMessageContent(array $response): string
    {
        $choice = Arr::first($response['choices'], fn ($choice) => isset($choice['message']['content']));

        return trim((string) ($choice['message']['content'] ?? ''));
    }

    public function extractToolCalls(array $response): Collection
    {
        $choice = Arr::first($response['choices']);
        $toolCalls = Arr::get($choice, 'message.tool_calls', []);

        return collect($toolCalls);
    }
}
