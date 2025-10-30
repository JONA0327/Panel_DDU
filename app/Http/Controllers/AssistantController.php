<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversation;
use App\Models\AssistantDocument;
use App\Models\AssistantSetting;
use App\Models\MeetingContentContainer;
use App\Models\MeetingTranscription;
use App\Services\Assistant\AssistantService;
use App\Services\Assistant\DocumentParser;
use App\Services\Assistant\OpenAiClient;
use App\Services\Calendar\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AssistantController extends Controller
{
    public function __construct(
        private readonly AssistantService $assistantService,
        private readonly DocumentParser $documentParser,
        private readonly OpenAiClient $openAiClient,
        private readonly GoogleCalendarService $calendarService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $settings = $user->assistantSetting()->first();

        if (! $settings) {
            $settings = AssistantSetting::create([
                'user_id' => $user->id,
                'enable_drive_calendar' => true,
            ]);
        }

        $conversations = $user->assistantConversations()
            ->withCount('messages')
            ->latest()
            ->get();

        $activeConversation = $conversations->first();

        if ($activeConversation) {
            $activeConversation->load(['messages' => fn ($query) => $query->orderBy('created_at'), 'documents']);
        }

        $meetings = MeetingTranscription::query()
            ->forUser($user)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get(['id', 'meeting_name', 'created_at']);

        $containers = MeetingContentContainer::query()
            ->where('username', $user->username)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $calendarEvents = collect();

        if ($settings->enable_drive_calendar && $user->googleToken) {
            try {
                $calendarEvents = $this->calendarService->listUpcomingEvents($user, Carbon::now(), Carbon::now()->addWeeks(2));
            } catch (\Throwable $exception) {
                $calendarEvents = collect();
            }
        }

        // Debug: Verificar el estado de la API key
        $apiKeyExists = $settings && $settings->openai_api_key;
        $isConfigured = $this->openAiClient->isConfigured($settings);

        return view('dashboard.asistente.index', [
            'settings' => $settings,
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'meetings' => $meetings,
            'containers' => $containers,
            'calendarEvents' => $calendarEvents,
            'apiConnected' => $isConfigured,
            // Debug info temporal
            'debugInfo' => [
                'settings_exists' => (bool) $settings,
                'api_key_exists' => $apiKeyExists,
                'api_key_length' => $apiKeyExists ? strlen($settings->openai_api_key) : 0,
                'is_configured' => $isConfigured,
            ],
        ]);
    }

    public function createConversation(Request $request)
    {
        $user = $request->user();
        $conversation = $this->assistantService->ensureConversation($user, null);

        if ($request->filled('title')) {
            $conversation->update(['title' => $request->input('title')]);
        }

        return response()->json([
            'conversation' => $conversation->loadCount('messages')->load(['messages' => fn ($query) => $query->orderBy('created_at')]),
        ]);
    }

    public function showConversation(Request $request, AssistantConversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);

        return response()->json([
            'conversation' => $conversation->loadCount('messages')->load([
                'messages' => function ($query) {
                    $query->orderBy('created_at');
                },
                'documents',
            ]),
        ]);
    }

    public function deleteConversation(Request $request, AssistantConversation $conversation): Response
    {
        $this->authorizeConversation($request, $conversation);

        $conversation->delete();

        return response()->noContent();
    }

    public function updateConversation(Request $request, AssistantConversation $conversation): Response
    {
        $this->authorizeConversation($request, $conversation);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $conversation->update(['title' => $validated['title']]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'conversation_id' => ['nullable', Rule::exists('assistant_conversations', 'id')->where('user_id', $request->user()->id)],
            'meetings' => ['array'],
            'meetings.*' => ['integer'],
            'containers' => ['array'],
            'containers.*' => ['integer'],
        ]);

        $user = $request->user();
        $settings = $user->assistantSetting;

        if (! $this->openAiClient->isConfigured($settings)) {
            return response()->json([
                'error' => 'Configura la API key de OpenAI en la sección de configuración del asistente antes de continuar.',
            ], 422);
        }

        $conversation = $this->assistantService->ensureConversation($user, $validated['conversation_id'] ?? null);

        $this->assistantService->registerUserMessage($conversation, $validated['message']);

        try {
            $reply = $this->assistantService->generateAssistantReply(
                $user,
                $conversation,
                $settings,
                $validated['message'],
                [
                    'meetings' => $validated['meetings'] ?? [],
                    'containers' => $validated['containers'] ?? [],
                    'include_calendar' => $settings->enable_drive_calendar,
                ]
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'error' => 'No fue posible generar una respuesta con la IA. Inténtalo nuevamente en unos momentos.',
            ], 500);
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'reply' => $reply->content,
            'messages' => $conversation->messages()->orderBy('created_at')->get(),
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => ['required', Rule::exists('assistant_conversations', 'id')->where('user_id', $request->user()->id)],
            'document' => ['required', 'file', 'max:10240'],
        ]);

        $user = $request->user();
        $conversation = AssistantConversation::findOrFail($validated['conversation_id']);
        $this->authorizeConversation($request, $conversation);

        /** @var UploadedFile $file */
        $file = $validated['document'];

        $path = $file->store('assistant-documents/' . $user->id);
        $text = $this->documentParser->extractText($file);

        $document = $conversation->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extracted_text' => $text,
            'metadata' => $this->buildDocumentMetadata($file),
        ]);

        $settings = $user->assistantSetting;
        $summary = null;

        if ($text && $this->openAiClient->isConfigured($settings)) {
            $response = $this->openAiClient->createChatCompletion($settings, [
                ['role' => 'system', 'content' => 'Eres un asistente que resume documentos de manera breve.'],
                ['role' => 'user', 'content' => 'Resume el siguiente documento en español en máximo 6 oraciones destacando tema principal y puntos clave: ' . Str::limit($text, 6000)],
            ], ['temperature' => 0.2]);

            $summary = $this->openAiClient->extractMessageContent($response);

            $document->update(['summary' => $summary]);
        }

        $this->assistantService->registerDocument($conversation, $document);

        return response()->json([
            'document' => $document->fresh(),
            'summary' => $summary,
        ]);
    }

    protected function authorizeConversation(Request $request, AssistantConversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
    }

    protected function buildDocumentMetadata(UploadedFile $file): array
    {
        $metadata = [
            'extension' => $file->getClientOriginalExtension(),
        ];

        if (Str::startsWith($file->getMimeType(), 'image/')) {
            $metadata['image_preview'] = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        return $metadata;
    }
}
