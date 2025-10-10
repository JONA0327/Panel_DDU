<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class JuFileDecryption
{
    /**
     * Desencripta un archivo .ju y devuelve el contenido JSON decodificado
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
            $encryptedContent = file_get_contents($filePath);

            if ($encryptedContent === false) {
                Log::error("No se pudo leer el archivo .ju: {$filePath}");
                return null;
            }

            // Normalizar la codificación del archivo
            $normalizedContent = self::normalizeEncoding($encryptedContent);

            // Los archivos .ju generalmente están en formato JSON base64 codificado
            // Intentar decodificar directamente como JSON primero
            $jsonDecoded = json_decode($normalizedContent, true, 512, JSON_INVALID_UTF8_IGNORE);

            if ($jsonDecoded !== null && json_last_error() === JSON_ERROR_NONE) {
                Log::info("Archivo .ju decodificado directamente como JSON: {$filePath}");
                return $jsonDecoded;
            }

            // Si no es JSON directo, intentar decodificar base64
            $base64Decoded = base64_decode($normalizedContent, true);

            if ($base64Decoded !== false) {
                $jsonDecoded = json_decode($base64Decoded, true, 512, JSON_INVALID_UTF8_IGNORE);

                if ($jsonDecoded !== null && json_last_error() === JSON_ERROR_NONE) {
                    Log::info("Archivo .ju decodificado desde base64: {$filePath}");
                    return $jsonDecoded;
                }
            }

            // Intentar desencriptación con Laravel Encryption (contenido original)
            $laravelDecrypted = self::tryLaravelDecryption($encryptedContent);
            if ($laravelDecrypted) {
                $jsonDecoded = json_decode($laravelDecrypted, true);
                if ($jsonDecoded !== null) {
                    Log::info("Archivo .ju decodificado con Laravel Encryption: {$filePath}");
                    return $jsonDecoded;
                }
            }

            // También intentar Laravel con contenido normalizado
            $laravelFromNormalized = self::tryLaravelDecryption($normalizedContent);
            if ($laravelFromNormalized) {
                $jsonDecoded = json_decode($laravelFromNormalized, true);
                if ($jsonDecoded !== null) {
                    Log::info("Archivo .ju decodificado con Laravel desde contenido normalizado: {$filePath}");
                    return $jsonDecoded;
                }
            }

            // Intentar otros métodos de desencriptación si es necesario
            // Algunos archivos .ju pueden usar XOR simple o cesar cipher
            $decryptedContent = self::tryXorDecryption($encryptedContent);
            if ($decryptedContent) {
                $jsonDecoded = json_decode($decryptedContent, true);
                if ($jsonDecoded !== null) {
                    Log::info("Archivo .ju decodificado con XOR: {$filePath}");
                    return $jsonDecoded;
                }
            }

            Log::error("No se pudo desencriptar el archivo .ju: {$filePath}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error desencriptando archivo .ju {$filePath}: " . $e->getMessage());
            return null;
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
}
