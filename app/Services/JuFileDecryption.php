<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class JuFileDecryption
{
    /**
     * Desencripta un archivo .ju siguiendo el flujo completo de Laravel Crypt
     * Basado en la documentación del sistema de encriptación de la aplicación
     *
     * @param string $filePath Ruta al archivo .ju
     * @return array|null Contenido desencriptado del archivo o null si falla
     */
    public static function decrypt($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                Log::error("Archivo .ju no encontrado: {$filePath}");
                return null;
            }

            // Leer el contenido del archivo
            $content = file_get_contents($filePath);

            if ($content === false) {
                Log::error("No se pudo leer el archivo .ju: {$filePath}");
                return null;
            }

            // Usar el método principal de desencriptación que sigue el flujo documentado
            $result = self::decryptJuContent($content);

            if ($result && isset($result['data'])) {
                Log::info("Archivo .ju procesado exitosamente: {$filePath}");
                return $result['data'];
            } else if ($result && isset($result['raw'])) {
                Log::info("Archivo .ju procesado con datos raw: {$filePath}");
                return $result['raw'];
            }

            Log::error("No se pudo desencriptar el archivo .ju: {$filePath}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error desencriptando archivo .ju {$filePath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Método principal de desencriptación que sigue el flujo documentado
     * Similar al usado en MeetingContentParsing::decryptJuFile
     *
     * @param string $content Contenido del archivo .ju
     * @return array Array con 'data', 'raw' y 'needs_encryption'
     */
    public static function decryptJuContent($content)
    {
        try {
            Log::info('decryptJuContent: Starting decryption process');

            // 1) Si el contenido ya es JSON válido (sin encriptar)
            $json_data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                Log::info('decryptJuContent: Content is already valid JSON (unencrypted)');
                return [
                    'data' => self::extractMeetingInfo($json_data),
                    'raw' => $json_data,
                    'needs_encryption' => true,
                ];
            }

            // 2) Intentar descifrar string encriptado de Laravel Crypt (base64) usando APP_KEY actual o claves legacy
            if (substr($content, 0, 3) === 'eyJ') {
                Log::info('decryptJuContent: Attempting to decrypt Laravel Crypt format');
                $legacyKeys = array_filter(array_map('trim', explode(',', (string) env('LEGACY_APP_KEYS', ''))));
                $triedKeys = [];
                $decrypted = null;

                // Helper closure para desencriptar con una clave concreta
                $attemptDecrypt = function(string $key) use (&$content) {
                    $prevKey = config('app.key');
                    // Ajustar key temporalmente
                    config(['app.key' => $key]);
                    try {
                        return \Illuminate\Support\Facades\Crypt::decryptString($content);
                    } finally {
                        // Restaurar clave original
                        config(['app.key' => $prevKey]);
                    }
                };

                // Primero con la clave actual
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($content);
                    $triedKeys[] = 'current';
                    Log::info('decryptJuContent: Direct decryption successful');
                } catch (\Exception $e) {
                    $triedKeys[] = 'current(failed:' . $e->getMessage() . ')';
                    Log::warning('decryptJuContent: Direct decryption failed', ['error' => $e->getMessage()]);

                    // Intentar con legacy keys si error fue MAC invalid o similar
                    if (!empty($legacyKeys)) {
                        foreach ($legacyKeys as $idx => $legacyKey) {
                            try {
                                $legacyDecrypted = $attemptDecrypt($legacyKey);
                                if ($legacyDecrypted) {
                                    $decrypted = $legacyDecrypted;
                                    $triedKeys[] = 'legacy[' . $idx . ']';
                                    Log::info('decryptJuContent: Decryption successful with legacy key', ['legacy_index' => $idx]);
                                    break;
                                }
                            } catch (\Exception $eL) {
                                $triedKeys[] = 'legacy[' . $idx . '](failed:' . $eL->getMessage() . ')';
                                Log::warning('decryptJuContent: Legacy key failed', ['index' => $idx, 'error' => $eL->getMessage()]);
                            }
                        }
                    }
                }

                if ($decrypted !== null) {
                    $json_data = json_decode($decrypted, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        Log::info('decryptJuContent: JSON parsing after decryption successful', [
                            'keys' => array_keys($json_data),
                            'attempts' => $triedKeys,
                        ]);
                        return [
                            'data' => self::extractMeetingInfo($json_data),
                            'raw' => $json_data,
                            'needs_encryption' => false,
                        ];
                    } else {
                        Log::warning('decryptJuContent: JSON decode failed after decryption', [
                            'attempts' => $triedKeys,
                            'error' => json_last_error_msg(),
                        ]);
                    }
                }
            }

            // 3) Intentar desencriptar formato JSON {"iv":"...","value":"..."}
            if (str_contains($content, '"iv"') && str_contains($content, '"value"')) {
                Log::info('decryptJuContent: Detected Laravel Crypt JSON format');
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decrypt($content);
                    Log::info('decryptJuContent: Laravel Crypt JSON decryption successful');

                    $json_data = json_decode($decrypted, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        Log::info('decryptJuContent: JSON parsing after Laravel Crypt decryption successful', ['keys' => array_keys($json_data)]);
                        return [
                            'data' => self::extractMeetingInfo($json_data),
                            'raw' => $json_data,
                            'needs_encryption' => false,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('decryptJuContent: Laravel Crypt JSON decryption failed', ['error' => $e->getMessage()]);
                }
            }

            Log::warning('decryptJuContent: Using default data - all decryption methods failed');
            return [
                'data' => self::getDefaultMeetingData(),
                'raw' => null,
                'needs_encryption' => false,
            ];

        } catch (\Exception $e) {
            Log::error('decryptJuContent: General exception', ['error' => $e->getMessage()]);
            return [
                'data' => self::getDefaultMeetingData(),
                'raw' => null,
                'needs_encryption' => false,
            ];
        }
    }

    /**
     * Intenta desencriptar usando XOR con diferentes claves comunes
     *
     * @param string $content Contenido encriptado
     * @return string|null Contenido desencriptado o null si falla
     */
    private static function tryXorDecryption($content)
    {
        // Claves XOR comunes para archivos de reuniones, incluyendo la APP_KEY
        $appKey = config('app.key');
        $appKeyDecoded = base64_decode(str_replace('base64:', '', $appKey));

        $commonKeys = [
            'juntify', 'meeting', 'transcript', 'audio',
            'key123', 'secret', 'password', 'ju2024',
            $appKey, // Clave completa con base64:
            str_replace('base64:', '', $appKey), // Sin prefijo base64:
            $appKeyDecoded, // Clave decodificada
            'f/uXuSJuJh3zlUpNyr5bZW8/2UpcGf082CHKPskSb04=', // Clave específica
            base64_decode('f/uXuSJuJh3zlUpNyr5bZW8/2UpcGf082CHKPskSb04=') // Clave decodificada específica
        ];

        foreach ($commonKeys as $key) {
            if (empty($key)) continue;

            $decrypted = self::xorDecrypt($content, $key);

            // Verificar si el resultado parece JSON válido
            if (self::looksLikeJson($decrypted)) {
                return $decrypted;
            }
        }

        return null;
    }

    /**
     * Intenta desencriptar usando el sistema de encriptación de Laravel
     *
     * @param string $content Contenido encriptado
     * @return string|null Contenido desencriptado o null si falla
     */
    private static function tryLaravelDecryption($content)
    {
        try {
            // Intentar desencriptar directamente con Laravel
            $decrypted = decrypt($content);
            return $decrypted;
        } catch (\Exception $e) {
            // Si falla, intentar con diferentes formatos
            try {
                // Intentar con base64 encode primero
                $encoded = base64_encode($content);
                $decrypted = decrypt($encoded);
                return $decrypted;
            } catch (\Exception $e) {
                // Intentar interpretando como payload de Laravel
                if (strpos($content, 'eyJ') === 0) { // Posible JWT/Laravel payload
                    try {
                        $payload = json_decode(base64_decode($content), true);
                        if (isset($payload['value'])) {
                            $decrypted = decrypt($payload['value']);
                            return $decrypted;
                        }
                    } catch (\Exception $e) {
                        // Continuar con otros métodos
                    }
                }
            }
        }

        return null;
    }

    /**
     * Función cryptstring para encriptación/desencriptación simétrica
     * Esta función debe ser la misma que se usa para encriptar
     *
     * @param string $data Datos a procesar
     * @param string $key Clave de encriptación
     * @return string Datos procesados
     */
    private static function cryptstring($data, $key = null)
    {
        if ($key === null) {
            // Usar la clave de aplicación por defecto
            $key = config('app.key');
            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }
        }

        // Si los datos están en formato Laravel encrypted payload, extraer el value
        $jsonData = json_decode($data, true);
        if ($jsonData && isset($jsonData['value'])) {
            // Es un payload encriptado de Laravel, desencriptar el value
            $encryptedData = base64_decode($jsonData['value']);
            return self::cryptstring($encryptedData, $key);
        }

        // Implementación XOR simple para encriptación simétrica
        $keyLen = strlen($key);
        $dataLen = strlen($data);
        $result = '';

        for ($i = 0; $i < $dataLen; $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }

        return $result;
    }

    /**
     * Normaliza la codificación del contenido del archivo
     *
     * @param string $content Contenido crudo del archivo
     * @return string Contenido normalizado
     */
    private static function normalizeEncoding($content)
    {
        // Detectar y convertir UTF-16 (Little Endian o Big Endian)
        if (substr($content, 0, 2) === "\xFF\xFE") {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
        } elseif (substr($content, 0, 2) === "\xFE\xFF") {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16BE');
        }

        // Si parece JSON (empieza con { o [), no limpiar espacios
        $trimmedContent = trim($content);
        if (self::looksLikeJson($trimmedContent)) {
            return $trimmedContent;
        }

        // Si no es JSON, limpiar para base64
        $content = str_replace(["\r", "\n", " "], '', $trimmedContent);

        // Remover caracteres inválidos para base64
        $validBase64Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
        $cleanContent = '';
        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];
            if (strpos($validBase64Chars, $char) !== false) {
                $cleanContent .= $char;
            }
        }

        return $cleanContent;
    }

    /**
     * Aplica desencriptación XOR con una clave dada
     *
     * @param string $data Datos a desencriptar
     * @param string $key Clave XOR
     * @return string Datos desencriptados
     */
    private static function xorDecrypt($data, $key)
    {
        $keyLen = strlen($key);
        $dataLen = strlen($data);
        $result = '';

        for ($i = 0; $i < $dataLen; $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }

        return $result;
    }

    /**
     * Verifica si una cadena parece ser JSON válido
     *
     * @param string $string Cadena a verificar
     * @return bool True si parece JSON válido
     */
    private static function looksLikeJson($string)
    {
        return (substr(trim($string), 0, 1) === '{' || substr(trim($string), 0, 1) === '[') &&
               json_decode($string) !== null;
    }

    /**
     * Extrae información específica del contenido .ju desencriptado
     *
     * @param array $juContent Contenido desencriptado del archivo .ju
     * @return array Información estructurada para el modal
     */
    public static function extractMeetingInfo($juContent)
    {
        if (!is_array($juContent)) {
            return [
                'summary' => null,
                'key_points' => [],
                'segments' => [],
                'participants' => [],
                'duration' => null,
                'timestamp' => null
            ];
        }

        return [
            'summary' => $juContent['summary'] ?? $juContent['resumen'] ?? null,
            'key_points' => $juContent['key_points'] ?? $juContent['puntos_clave'] ?? $juContent['highlights'] ?? [],
            'segments' => $juContent['segments'] ?? $juContent['segmentos'] ?? $juContent['transcription'] ?? [],
            'participants' => $juContent['participants'] ?? $juContent['participantes'] ?? $juContent['speakers'] ?? [],
            'duration' => $juContent['duration'] ?? $juContent['duracion'] ?? null,
            'timestamp' => $juContent['timestamp'] ?? $juContent['fecha'] ?? $juContent['created_at'] ?? null,
            'metadata' => $juContent['metadata'] ?? $juContent['meta'] ?? [],
            'raw_content' => $juContent
        ];
    }

    /**
     * Busca archivos .ju asociados a una reunión
     *
     * @param string $meetingName Nombre de la reunión
     * @param string $username Usuario propietario
     * @return string|null Ruta al archivo .ju encontrado
     */
    public static function findJuFileForMeeting($meetingName, $username)
    {
        // Directorios comunes donde se almacenan archivos .ju
        $possiblePaths = [
            storage_path("app/meetings/{$username}/"),
            storage_path("app/transcripts/{$username}/"),
            storage_path("app/ju_files/"),
            storage_path("app/private/meetings/"),
        ];

        // Nombres de archivo posibles
        $possibleNames = [
            "{$meetingName}.ju",
            str_replace(' ', '_', $meetingName) . ".ju",
            str_replace(' ', '-', $meetingName) . ".ju",
            strtolower(str_replace(' ', '_', $meetingName)) . ".ju"
        ];

        foreach ($possiblePaths as $basePath) {
            if (!is_dir($basePath)) {
                continue;
            }

            foreach ($possibleNames as $fileName) {
                $fullPath = $basePath . $fileName;
                if (file_exists($fullPath)) {
                    Log::info("Archivo .ju encontrado: {$fullPath}");
                    return $fullPath;
                }
            }

            // Buscar archivos .ju en el directorio
            $juFiles = glob($basePath . "*.ju");
            foreach ($juFiles as $juFile) {
                // Verificar si el nombre del archivo contiene el nombre de la reunión
                $fileName = basename($juFile, '.ju');
                if (stripos($fileName, str_replace(' ', '', $meetingName)) !== false) {
                    Log::info("Archivo .ju encontrado por similitud: {$juFile}");
                    return $juFile;
                }
            }
        }

        Log::warning("No se encontró archivo .ju para la reunión: {$meetingName}");
        return null;
    }

    /**
     * Devuelve datos por defecto cuando no se puede desencriptar un archivo .ju
     *
     * @return array Datos por defecto de la reunión
     */
    public static function getDefaultMeetingData()
    {
        return [
            'summary' => null,
            'key_points' => [],
            'segments' => [],
            'participants' => [],
            'action_items' => [],
            'metadata' => [],
            'timestamp' => null,
            'duration' => null,
        ];
    }
}
