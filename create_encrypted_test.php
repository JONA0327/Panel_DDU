<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Datos de prueba para encriptar
$testData = [
    "summary" => "Reunión de prueba encriptada con Laravel",
    "key_points" => [
        "Prueba de encriptación Laravel",
        "Verificación de desencriptación",
        "Validación del sistema"
    ],
    "segments" => [
        [
            "timestamp" => "00:00:30",
            "speaker" => "Administrador",
            "text" => "Esta es una prueba de encriptación con la clave de Laravel",
            "duration" => 15
        ]
    ],
    "participants" => [
        "Administrador",
        "Sistema"
    ],
    "duration" => 900,
    "timestamp" => "2025-01-14T16:00:00Z",
    "metadata" => [
        "project" => "DDU Panel",
        "version" => "1.0",
        "meeting_type" => "prueba_encriptacion",
        "encrypted" => true
    ]
];

// Convertir a JSON
$jsonData = json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "Datos originales:\n";
echo $jsonData . "\n\n";

// Encriptar con Laravel
try {
    $encrypted = encrypt($jsonData);
    echo "Datos encriptados con Laravel:\n";
    echo $encrypted . "\n\n";

    // Guardar archivo encriptado
    $encryptedFilePath = storage_path('app/ju_files/Laravel_Encrypted_Test.ju');
    file_put_contents($encryptedFilePath, $encrypted);
    echo "Archivo guardado en: $encryptedFilePath\n";

    // Probar desencriptación inmediata
    $decrypted = decrypt($encrypted);
    echo "Desencriptación inmediata exitosa:\n";
    echo $decrypted . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
