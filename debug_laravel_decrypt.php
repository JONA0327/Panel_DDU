<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\JuFileDecryption;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = storage_path('app/ju_files/Laravel_Encrypted_Test.ju');

if (!file_exists($file)) {
    echo "Archivo no encontrado: $file\n";
    exit(1);
}

echo "Probando desencriptación paso a paso...\n\n";

// Leer contenido crudo
$rawContent = file_get_contents($file);
echo "1. Contenido crudo (primeros 100 chars): " . substr($rawContent, 0, 100) . "...\n\n";

// Verificar si parece ser JSON de Laravel
$decoded = json_decode($rawContent, true);
if ($decoded && isset($decoded['iv'], $decoded['value'], $decoded['mac'])) {
    echo "2. ✓ Detectado como payload de Laravel encryption\n";

    try {
        $decrypted = decrypt($rawContent);
        echo "3. ✓ Desencriptación Laravel exitosa\n";
        echo "4. Contenido desencriptado: " . substr($decrypted, 0, 200) . "...\n\n";

        $finalJson = json_decode($decrypted, true);
        if ($finalJson) {
            echo "5. ✓ JSON final válido\n";
            echo "Resumen: " . ($finalJson['summary'] ?? 'No disponible') . "\n";
            echo "Participantes: " . count($finalJson['participants'] ?? []) . "\n";
        } else {
            echo "5. ✗ Error parseando JSON final\n";
        }

    } catch (Exception $e) {
        echo "3. ✗ Error desencriptando: " . $e->getMessage() . "\n";
    }
} else {
    echo "2. ✗ No parece ser payload de Laravel\n";
}

echo "\n--- Probando con el servicio JuFileDecryption ---\n";

try {
    $result = JuFileDecryption::decrypt($file);

    if ($result) {
        echo "✓ Servicio funcionó\n";
        echo "Tipo de resultado: " . gettype($result) . "\n";
        if (is_array($result)) {
            echo "Claves: " . implode(', ', array_keys($result)) . "\n";
        }
    } else {
        echo "✗ Servicio retornó null\n";
    }
} catch (Exception $e) {
    echo "✗ Error en servicio: " . $e->getMessage() . "\n";
}
