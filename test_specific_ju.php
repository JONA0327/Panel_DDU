<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\JuFileDecryption;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Probar archivo específico
$file = storage_path('app/ju_files/Prueba_Reunion.ju');

if (!file_exists($file)) {
    echo "Archivo no encontrado: $file\n";
    exit(1);
}

echo "Probando: " . basename($file) . "\n";
echo "Ruta completa: $file\n";

// Leer contenido crudo
$content = file_get_contents($file);
echo "\nContenido crudo (primeros 100 chars): " . substr($content, 0, 100) . "\n";
echo "Longitud del contenido: " . strlen($content) . "\n";

// Detectar y convertir encoding si es necesario
if (substr($content, 0, 2) === "\xFF\xFE") {
    echo "Detectado UTF-16 LE, convirtiendo...\n";
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
    echo "Contenido después de conversión (primeros 100 chars): " . substr($content, 0, 100) . "\n";
} elseif (substr($content, 0, 2) === "\xFE\xFF") {
    echo "Detectado UTF-16 BE, convirtiendo...\n";
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16BE');
    echo "Contenido después de conversión (primeros 100 chars): " . substr($content, 0, 100) . "\n";
}

// Limpiar contenido más agresivamente
$content = trim($content);
$content = str_replace(["\r", "\n", " "], '', $content);

echo "Contenido limpio (primeros 100 chars): " . substr($content, 0, 100) . "\n";
echo "Longitud después de limpiar: " . strlen($content) . "\n";

// Verificar caracteres del contenido
echo "Primeros 20 caracteres en ord(): ";
for ($i = 0; $i < min(20, strlen($content)); $i++) {
    echo ord($content[$i]) . " ";
}
echo "\n";

// Validar que todos los caracteres sean válidos para base64
$validBase64Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
$invalidChars = [];
for ($i = 0; $i < strlen($content); $i++) {
    $char = $content[$i];
    if (strpos($validBase64Chars, $char) === false) {
        if (!isset($invalidChars[$char])) {
            $invalidChars[$char] = 0;
        }
        $invalidChars[$char]++;
    }
}

if (!empty($invalidChars)) {
    echo "Caracteres inválidos encontrados:\n";
    foreach ($invalidChars as $char => $count) {
        echo "  '" . $char . "' (ord: " . ord($char) . ") - " . $count . " veces\n";
    }

    // Intentar limpiar caracteres inválidos
    $cleanContent = '';
    for ($i = 0; $i < strlen($content); $i++) {
        $char = $content[$i];
        if (strpos($validBase64Chars, $char) !== false) {
            $cleanContent .= $char;
        }
    }

    echo "Contenido después de limpiar caracteres inválidos: longitud " . strlen($cleanContent) . "\n";
    $content = $cleanContent;
}

// Probar decodificación base64 manualmente
echo "\nProbando decodificación base64...\n";
$base64Decoded = base64_decode($content, true);

if ($base64Decoded !== false) {
    echo "✓ Base64 válido\n";
    echo "Contenido decodificado (primeros 100 chars): " . substr($base64Decoded, 0, 100) . "\n";

    // Intentar convertir a UTF-8 válido
    echo "Verificando encoding del contenido decodificado...\n";
    if (!mb_check_encoding($base64Decoded, 'UTF-8')) {
        echo "Contenido no está en UTF-8 válido, intentando conversión...\n";
        $base64Decoded = mb_convert_encoding($base64Decoded, 'UTF-8', 'UTF-8');
    }

    // Usar JSON_INVALID_UTF8_IGNORE para manejar UTF-8 malformado
    $json = json_decode($base64Decoded, true, 512, JSON_INVALID_UTF8_IGNORE);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ JSON válido después de base64\n";
        echo "\nContenido completo:\n";
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "✗ JSON inválido: " . json_last_error_msg() . "\n";
        echo "Contenido problemático (primeros 500 chars): " . substr($base64Decoded, 0, 500) . "\n";
    }
} else {
    echo "✗ Base64 inválido\n";
}

// Probar con el servicio
echo "\n--- Probando con JuFileDecryption ---\n";
try {
    $result = JuFileDecryption::decrypt($file);

    if ($result === null) {
        echo "✗ No se pudo desencriptar el archivo con el servicio\n";
    } else {
        echo "✓ Desencriptación exitosa con el servicio\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
