<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\AssistantDocument;
use App\Models\AssistantMessage;
use App\Models\AssistantSetting;
use App\Models\User;
use App\Services\Calendar\GoogleCalendarService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class AssistantService
{
    public function __construct(
        private readonly OpenAiClient $client,
        private readonly AssistantContextBuilder $contextBuilder,
        private readonly GoogleCalendarService $calendarService,
    ) {
    }

    public function ensureConversation(User $user, ?int $conversationId = null): AssistantConversation
    {
        if ($conversationId) {
            $conversation = $user->assistantConversations()->findOrFail($conversationId);
        } else {
            $conversation = $user->assistantConversations()->create([
                'title' => 'Nueva conversación',
            ]);

            $conversation->messages()->create([
                'role' => 'system',
                'content' => $this->buildSystemPrompt(),
            ]);

            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => 'Hola, soy tu asistente DDU. Puedo apoyarte con tus reuniones, documentos y eventos de calendario usando el contexto que selecciones. ¿En qué puedo ayudarte hoy?',
            ]);
        }

        return $conversation;
    }

    public function registerUserMessage(AssistantConversation $conversation, string $message, array $metadata = []): AssistantMessage
    {
        return $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
            'metadata' => $metadata,
        ]);
    }

    public function generateAssistantReply(User $user, AssistantConversation $conversation, AssistantSetting $settings, string $message, array $options = []): AssistantMessage
    {
        // Agregar el mensaje del usuario a las opciones para análisis de contexto
        $options['user_message'] = $message;
        $context = $this->contextBuilder->build($user, $conversation, $options);

        $history = $conversation->messages()->orderBy('created_at')->get()->map(function (AssistantMessage $message) {
            $payload = [
                'role' => $message->role,
                'content' => $message->content,
            ];

            $attachments = Arr::get($message->metadata, 'attachments', []);

            if (! empty($attachments)) {
                $payload['content'] = array_merge([
                    ['type' => 'text', 'text' => $message->content],
                ], $attachments);
            }

            return $payload;
        })->toArray();

        $systemPrompt = $this->buildSystemPrompt($context['text']);

        $messages = array_merge([
            ['role' => 'system', 'content' => $systemPrompt],
        ], $history);

        $tools = $this->buildToolsDefinition();

        $response = $this->client->createChatCompletion($settings, $messages, ['tools' => $tools, 'tool_choice' => 'auto']);

        $toolCalls = $this->client->extractToolCalls($response);

        if ($toolCalls->isNotEmpty()) {
            // Primero agregar el mensaje del asistente con los tool calls
            $choice = Arr::first($response['choices']);
            $assistantMessage = $choice['message'];
            $messages[] = [
                'role' => 'assistant',
                'content' => $assistantMessage['content'] ?? null,
                'tool_calls' => $assistantMessage['tool_calls'] ?? []
            ];

            // Luego agregar las respuestas de los tools
            foreach ($toolCalls as $toolCall) {
                $messages[] = $this->handleToolCall($user, $conversation, $toolCall);
            }

            $response = $this->client->createChatCompletion($settings, $messages);
        }

        $content = $this->client->extractMessageContent($response);

        if (blank($content)) {
            throw new RuntimeException('El asistente no pudo generar una respuesta válida.');
        }

        return $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $content,
        ]);
    }

    protected function handleToolCall(User $user, AssistantConversation $conversation, array $toolCall): array
    {
        $name = Arr::get($toolCall, 'function.name');
        $arguments = json_decode(Arr::get($toolCall, 'function.arguments', '{}'), true) ?: [];

        return match ($name) {
            'schedule_calendar_event' => $this->handleScheduleEventTool($user, $conversation, $arguments, Arr::get($toolCall, 'id')),
            default => [
                'role' => 'tool',
                'tool_call_id' => Arr::get($toolCall, 'id'),
                'content' => 'La función solicitada no está implementada.',
            ],
        };
    }

    protected function handleScheduleEventTool(User $user, AssistantConversation $conversation, array $arguments, ?string $toolCallId): array
    {
        $title = Arr::get($arguments, 'title');
        $start = Arr::get($arguments, 'start');
        $end = Arr::get($arguments, 'end');
        $description = Arr::get($arguments, 'description');
        $attendees = Arr::wrap(Arr::get($arguments, 'attendees', []));

        if (! $title || ! $start || ! $end) {
            return [
                'role' => 'tool',
                'tool_call_id' => $toolCallId,
                'content' => 'No se proporcionaron datos suficientes para programar el evento.',
            ];
        }

        try {
            $event = $this->calendarService->createEvent($user, [
                'summary' => $title,
                'start' => $start,
                'end' => $end,
                'description' => $description,
                'attendees' => $attendees,
            ]);
        } catch (\Throwable $exception) {
            Log::error('No se pudo programar el evento desde el asistente.', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return [
                'role' => 'tool',
                'tool_call_id' => $toolCallId,
                'content' => 'Ocurrió un error al intentar programar el evento en Google Calendar.',
            ];
        }

        return [
            'role' => 'tool',
            'tool_call_id' => $toolCallId,
            'content' => json_encode([
                'status' => 'success',
                'event' => $event,
            ], JSON_THROW_ON_ERROR),
        ];
    }

    protected function buildSystemPrompt(?string $context = null): string
    {
        $base = 'Eres el asistente inteligente de DDU. Usa exclusivamente el contexto de reuniones, documentos y eventos del calendario proporcionados. Mantén el contexto de la conversación y ofrece respuestas en español. Si no tienes datos suficientes, indícalo.';

        if ($context) {
            $base .= "\n\nContexto disponible:\n" . $context;
        }

        $base .= "\n\nCuando el usuario solicite agendar una reunión o evento:\n" .
                 "1. SIEMPRE revisa la FECHA Y HORA ACTUAL en el contexto proporcionado\n" .
                 "2. Calcula correctamente fechas relativas (mañana, pasado mañana, etc.)\n" .
                 "3. NUNCA uses años anteriores - SIEMPRE usa el año actual (2025)\n" .
                 "4. Utiliza la función de programación para crear el evento en Google Calendar";

        return $base;
    }

    protected function buildToolsDefinition(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'schedule_calendar_event',
                    'description' => 'Programa un evento en el Google Calendar del usuario. IMPORTANTE: Siempre utiliza el año actual (2025) y calcula fechas relativas basándote en la fecha actual proporcionada en el contexto.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => [
                                'type' => 'string',
                                'description' => 'Título o resumen del evento.',
                            ],
                            'start' => [
                                'type' => 'string',
                                'description' => 'Fecha y hora de inicio en formato ISO 8601 (ej: 2025-10-31T11:00:00-06:00). DEBE usar el año actual 2025.',
                            ],
                            'end' => [
                                'type' => 'string',
                                'description' => 'Fecha y hora de finalización en formato ISO 8601 (ej: 2025-10-31T12:00:00-06:00). DEBE usar el año actual 2025.',
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Descripción detallada del evento.',
                            ],
                            'attendees' => [
                                'type' => 'array',
                                'description' => 'Lista de correos electrónicos de asistentes.',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'required' => ['title', 'start', 'end'],
                    ],
                ],
            ],
        ];
    }

    public function registerDocument(AssistantConversation $conversation, AssistantDocument $document): AssistantMessage
    {
        $description = "He analizado el documento {$document->original_name}.";

        if ($document->summary) {
            $description .= ' Resumen: ' . Str::of($document->summary)->squish();
        } elseif ($document->extracted_text) {
            $description .= ' Extracto: ' . Str::of($document->extracted_text)->squish()->limit(200);
        }

        $metadata = [];

        $imagePreview = Arr::get($document->metadata, 'image_preview');

        if ($imagePreview) {
            $metadata['attachments'] = [[
                'type' => 'image_url',
                'image_url' => ['url' => $imagePreview],
            ]];
        }

        return $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}
