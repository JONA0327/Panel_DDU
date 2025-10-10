<?php

// Script de prueba para el nuevo sistema de desencriptación de .ju
// Basado en la documentación del flujo de encriptación/desencriptación de Laravel Crypt

use App\Services\JuFileDecryption;
use Illuminate\Support\Facades\Crypt;

// Datos de prueba simulando el contenido de una reunión
$testMeetingData = [
    'segments' => [
        [
            'speaker' => 'Jonathan Cox',
            'text' => 'Buenos días, comenzamos la reunión de revisión de requerimientos.',
            'start' => 0,
            'timestamp' => '00:00'
        ],
        [
            'speaker' => 'María García',
            'text' => 'Perfecto, tenemos varios puntos importantes que revisar hoy.',
            'start' => 15,
            'timestamp' => '00:15'
        ]
    ],
    'summary' => 'Reunión de revisión de requerimientos del proyecto Kualifin. Se discutieron los puntos principales y se asignaron tareas.',
    'key_points' => [
        'Revisión completa de requerimientos funcionales',
        'Validación de casos de uso principales',
        'Definición de cronograma de desarrollo'
    ],
    'action_items' => [
        [
            'title' => 'Documentar casos de uso',
            'owner' => 'Jonathan Cox',
            'due_date' => '2025-10-15',
            'priority' => 'Alta'
        ]
    ],
    'participants' => [
        ['name' => 'Jonathan Cox', 'role' => 'Product Manager'],
        ['name' => 'María García', 'role' => 'Desarrolladora Senior']
    ],
    'metadata' => [
        'meeting_id' => 'kualifin_req_001',
        'platform' => 'Google Meet',
        'recorded' => true
    ]
];

echo "🔧 Probando sistema de encriptación/desencriptación de archivos .ju\n";
echo "Basado en la documentación del flujo de Laravel Crypt\n\n";

// 1. Simular el proceso de encriptación (como lo hace la aplicación)
echo "1️⃣ Encriptando datos con Laravel Crypt...\n";
$jsonPayload = json_encode($testMeetingData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo "   JSON original: " . strlen($jsonPayload) . " bytes\n";

$encryptedString = Crypt::encryptString($jsonPayload);
echo "   Contenido encriptado: " . strlen($encryptedString) . " bytes\n";
echo "   Formato: " . (substr($encryptedString, 0, 3) === 'eyJ' ? 'Base64 Laravel Crypt' : 'Otro') . "\n\n";

// 2. Probar desencriptación con nuestro nuevo método
echo "2️⃣ Desencriptando con nuevo método JuFileDecryption::decryptJuContent()...\n";
try {
    $result = JuFileDecryption::decryptJuContent($encryptedString);

    if ($result && isset($result['data'])) {
        echo "   ✅ Desencriptación exitosa!\n";
        echo "   - needs_encryption: " . ($result['needs_encryption'] ? 'true' : 'false') . "\n";

        $data = $result['data'];
        echo "   - Resumen disponible: " . (isset($data['summary']) && $data['summary'] ? 'Sí' : 'No') . "\n";
        echo "   - Puntos clave: " . (is_array($data['key_points']) ? count($data['key_points']) : 0) . "\n";
        echo "   - Segmentos: " . (is_array($data['segments']) ? count($data['segments']) : 0) . "\n";
        echo "   - Participantes: " . (is_array($data['participants']) ? count($data['participants']) : 0) . "\n";

        if (isset($data['summary']) && $data['summary']) {
            echo "\n   📝 Resumen extraído:\n";
            echo "   " . substr($data['summary'], 0, 100) . "...\n";
        }
    } else {
        echo "   ❌ Falló la desencriptación\n";
        print_r($result);
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Probar con formato JSON de Laravel Crypt
echo "3️⃣ Probando con formato JSON de Laravel Crypt...\n";
$encryptedJson = Crypt::encrypt($jsonPayload); // Esto genera formato {"iv":"...","value":"..."}
echo "   Formato JSON encriptado generado\n";

try {
    $result2 = JuFileDecryption::decryptJuContent($encryptedJson);

    if ($result2 && isset($result2['data'])) {
        echo "   ✅ Desencriptación JSON exitosa!\n";
        echo "   - needs_encryption: " . ($result2['needs_encryption'] ? 'true' : 'false') . "\n";
    } else {
        echo "   ❌ Falló la desencriptación JSON\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error JSON: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Probar con JSON sin encriptar
echo "4️⃣ Probando con JSON sin encriptar...\n";
try {
    $result3 = JuFileDecryption::decryptJuContent($jsonPayload);

    if ($result3 && isset($result3['data'])) {
        echo "   ✅ Procesamiento de JSON sin encriptar exitoso!\n";
        echo "   - needs_encryption: " . ($result3['needs_encryption'] ? 'true' : 'false') . "\n";
        echo "   - Debería marcarse para reencriptar: " . ($result3['needs_encryption'] ? 'Sí' : 'No') . "\n";
    } else {
        echo "   ❌ Falló el procesamiento de JSON sin encriptar\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error JSON sin encriptar: " . $e->getMessage() . "\n";
}

echo "\n🎯 Pruebas completadas\n";
