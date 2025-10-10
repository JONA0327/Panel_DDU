<?php

namespace App\Http\Controllers;

use App\Models\GoogleToken;
use App\Models\MeetingTranscription;
use App\Services\JuDecryptionService;
use App\Services\UserGoogleDriveService;

class MeetingDetailsController extends Controller
{
    public function show($transcriptionId)
    {
        try {
            $transcription = MeetingTranscription::findOrFail($transcriptionId);

            $juData = null;
            if ($transcription->transcript_drive_id) {
                $token = $this->resolveToken($transcription);
                if ($token) {
                    $driveService = new UserGoogleDriveService($token);
                    $juFileContent = $this->downloadFileContent($driveService, $transcription->transcript_drive_id);

                    if ($juFileContent) {
                        $juData = JuDecryptionService::decryptContent($juFileContent);
                    }
                }
            }

            $audioUrl = null;
            if ($transcription->audio_drive_id) {
                $audioUrl = route('download.audio', $transcription);
            }

            return response()->json([
                'success' => true,
                'summary' => $juData['summary'] ?? 'Resumen no disponible.',
                'key_points' => $juData['key_points'] ?? [],
                'segments' => $juData['segments'] ?? [],
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
}
