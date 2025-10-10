<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\JuFileDecryption;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Prueba de Desencriptación con CryptString ===\n";

// Probar con el archivo encriptado que tienes
$encryptedFile = "Kualifin #2 Requerimientos.ju";

if (!file_exists($encryptedFile)) {
    echo "Archivo no encontrado: $encryptedFile\n";
    exit(1);
}

echo "Probando archivo: $encryptedFile\n";

// Leer contenido
$content = file_get_contents($encryptedFile);
echo "Longitud del contenido: " . strlen($content) . "\n";
echo "Primeros 100 caracteres: " . substr($content, 0, 100) . "\n\n";

// Verificar si es un payload encriptado de Laravel
$isLaravelPayload = false;
$jsonData = json_decode($content, true);
if ($jsonData && isset($jsonData['value'], $jsonData['iv'])) {
    echo "✓ Es un payload encriptado de Laravel\n";
    echo "IV: " . $jsonData['iv'] . "\n";
    echo "Value length: " . strlen($jsonData['value']) . "\n";
    $isLaravelPayload = true;
}

// Probar desencriptación manual con cryptstring
echo "\n--- Prueba Manual de CryptString ---\n";

// Obtener la clave de la aplicación
$appKey = config('app.key');
echo "App Key: " . $appKey . "\n";

if (str_starts_with($appKey, 'base64:')) {
    $key = base64_decode(substr($appKey, 7));
    echo "Key decodificado (length): " . strlen($key) . "\n";
} else {
    $key = $appKey;
}

if ($isLaravelPayload) {
    // Intentar desencriptar el value del payload
    $encryptedValue = base64_decode($jsonData['value']);
    echo "Encrypted value length: " . strlen($encryptedValue) . "\n";

    // Aplicar cryptstring (XOR)
    $keyLen = strlen($key);
    $dataLen = strlen($encryptedValue);
    $decryptedValue = '';

    for ($i = 0; $i < $dataLen; $i++) {
        $decryptedValue .= chr(ord($encryptedValue[$i]) ^ ord($key[$i % $keyLen]));
    }

    echo "Decrypted value (primeros 100 chars): " . substr($decryptedValue, 0, 100) . "\n";

    // Verificar si es JSON válido
    $jsonResult = json_decode($decryptedValue, true);
    if ($jsonResult !== null) {
        echo "✓ El resultado es JSON válido\n";
        echo "Keys del JSON: " . implode(', ', array_keys($jsonResult)) . "\n";
    } else {
        echo "✗ El resultado no es JSON válido: " . json_last_error_msg() . "\n";
        echo "Intentando con diferentes offsets...\n";

        // Probar con diferentes offsets en caso de padding
        for ($offset = 0; $offset < 16; $offset++) {
            $testData = substr($decryptedValue, $offset, 200);
            if (substr($testData, 0, 1) === '{' || substr($testData, 0, 1) === '[') {
                echo "Encontrado posible JSON en offset $offset: " . substr($testData, 0, 50) . "...\n";
                break;
            }
        }
    }
}

// Probar desencriptación con JuFileDecryption
echo "\n--- Prueba con JuFileDecryption Service ---\n";
try {
    $result = JuFileDecryption::decrypt($encryptedFile);

    if ($result === null) {
        echo "✗ No se pudo desencriptar el archivo con el servicio actual\n";
    } else {
        echo "✓ Archivo desencriptado exitosamente con el servicio\n";
        echo "Tipo de resultado: " . gettype($result) . "\n";

        if (is_array($result)) {
            echo "Keys disponibles: " . implode(', ', array_keys($result)) . "\n";

            // Verificar si contiene las keys esperadas de una reunión
            $expectedKeys = ['summary', 'participants', 'segments', 'key_points', 'metadata'];
            $foundKeys = array_intersect($expectedKeys, array_keys($result));
            if (!empty($foundKeys)) {
                echo "✓ Encontradas keys de reunión: " . implode(', ', $foundKeys) . "\n";

                if (isset($result['summary'])) {
                    echo "Resumen: " . substr($result['summary'], 0, 100) . "...\n";
                }
                if (isset($result['participants'])) {
                    echo "Participantes: " . count($result['participants']) . "\n";
                }
            } else {
                echo "ℹ Keys encontradas no parecen ser de reunión. Posible payload encriptado.\n";
            }
        }
    }

} catch (Exception $e) {
    echo "✗ Error durante la desencriptación: " . $e->getMessage() . "\n";
}

echo "\n=== Fin de la Prueba ===\n";
