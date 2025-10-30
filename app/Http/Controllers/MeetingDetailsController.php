<?php

namespace App\Http\Controllers;

use App\Models\GoogleToken;
use App\Models\MeetingTranscription;
use App\Services\JuDecryptionService;
use App\Services\JuFileDecryption;
use App\Services\UserGoogleDriveService;

class MeetingDetailsController extends Controller
{
    public function show($transcriptionId)
    {
        try {
            $transcription = MeetingTranscription::findOrFail($transcriptionId);
            
            // Verificar permisos de acceso
            $this->authorizeMeeting($transcription);

            $meetingInfo = [
                'summary' => 'Resumen no disponible.',
                'key_points' => [],
                'segments' => [],
            ];
            if ($transcription->transcript_drive_id) {
                $token = $this->resolveToken($transcription);
                if ($token) {
                    $driveService = new UserGoogleDriveService($token);
                    $juFileContent = $this->downloadFileContent($driveService, $transcription->transcript_drive_id);

                    if ($juFileContent) {
                        $decryptedData = JuDecryptionService::decryptContent($juFileContent);

                        if ($decryptedData) {
                            $meetingInfo = JuFileDecryption::extractMeetingInfo($decryptedData);
                        }
                    }
                }
            }

            $audioUrl = null;
            if ($transcription->audio_drive_id) {
                $audioUrl = route('download.audio', $transcription);
            }

            return response()->json([
                'success' => true,
                'summary' => $meetingInfo['summary'] ?? 'Resumen no disponible.',
                'key_points' => $meetingInfo['key_points'] ?? [],
                'segments' => $meetingInfo['segments'] ?? [],
                'audio_url' => $audioUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la reunión: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function downloadFileContent(UserGoogleDriveService $driveService, string $fileId): ?string
    {
        $response = $driveService->downloadFile($fileId);

        return $response ? $response->getBody()->getContents() : null;
    }

    private function resolveToken(MeetingTranscription $transcription): ?GoogleToken
    {
        if ($transcription->user_id) {
            $token = GoogleToken::where('user_id', $transcription->user_id)->first();
            if ($token) {
                return $token;
            }
        }

        if ($transcription->username) {
            return GoogleToken::where('username', $transcription->username)->first();
        }

        return null;
    }

    /**
     * Verificar si el usuario tiene permisos para acceder a la reunión.
     */
    private function authorizeMeeting(MeetingTranscription $meeting): void
    {
        $user = auth()->user();

        // Verificar si es el propietario directo de la reunión
        if ($meeting->user_id && $meeting->user_id === optional($user)->id) {
            return;
        }

        if ($meeting->username && $meeting->username === optional($user)->username) {
            return;
        }

        // Verificar si el usuario tiene acceso a través de grupos
        if ($user && $this->hasGroupAccess($meeting, $user)) {
            return;
        }

        abort(403, 'No tienes permisos para acceder a esta reunión.');
    }

    /**
     * Verificar si el usuario tiene acceso a la reunión a través de grupos compartidos.
     */
    private function hasGroupAccess(MeetingTranscription $meeting, $user): bool
    {
        return $meeting->groups()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->exists();
    }
}
