<?php

namespace App\Console\Commands;

use App\Services\JuFileDecryption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class TestJuEncryptionDecryption extends Command
{
    protected $signature = 'test:ju-encryption-decryption';
    protected $description = 'Prueba completa del sistema de encriptación/desencriptación de archivos .ju';

    public function handle()
    {
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

        $this->info('🔧 Probando sistema de encriptación/desencriptación de archivos .ju');
        $this->line('Basado en la documentación del flujo de Laravel Crypt');
        $this->newLine();

        // 1. Simular el proceso de encriptación (como lo hace la aplicación)
        $this->info('1️⃣ Encriptando datos con Laravel Crypt...');
        $jsonPayload = json_encode($testMeetingData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->line("   JSON original: " . strlen($jsonPayload) . " bytes");

        $encryptedString = Crypt::encryptString($jsonPayload);
        $this->line("   Contenido encriptado: " . strlen($encryptedString) . " bytes");
        $this->line("   Formato: " . (substr($encryptedString, 0, 3) === 'eyJ' ? 'Base64 Laravel Crypt' : 'Otro'));
        $this->newLine();

        // 2. Probar desencriptación con nuestro nuevo método
        $this->info('2️⃣ Desencriptando con nuevo método JuFileDecryption::decryptJuContent()...');
        try {
            $result = JuFileDecryption::decryptJuContent($encryptedString);

            if ($result && isset($result['data'])) {
                $this->info("   ✅ Desencriptación exitosa!");
                $this->line("   - needs_encryption: " . ($result['needs_encryption'] ? 'true' : 'false'));

                $data = $result['data'];
                $this->line("   - Resumen disponible: " . (isset($data['summary']) && $data['summary'] ? 'Sí' : 'No'));
                $this->line("   - Puntos clave: " . (is_array($data['key_points']) ? count($data['key_points']) : 0));
                $this->line("   - Segmentos: " . (is_array($data['segments']) ? count($data['segments']) : 0));
                $this->line("   - Participantes: " . (is_array($data['participants']) ? count($data['participants']) : 0));

                if (isset($data['summary']) && $data['summary']) {
                    $this->newLine();
                    $this->line("   📝 Resumen extraído:");
                    $this->line("   " . substr($data['summary'], 0, 100) . "...");
                }
            } else {
                $this->error("   ❌ No se pudieron obtener datos");
                $this->line(print_r($result, true));
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();

        // 3. Probar con formato JSON de Laravel Crypt
        $this->info('3️⃣ Probando con formato JSON de Laravel Crypt...');
        $encryptedJson = Crypt::encrypt($jsonPayload); // Esto genera formato {"iv":"...","value":"..."}
        $this->line("   Formato JSON encriptado generado");

        try {
            $result2 = JuFileDecryption::decryptJuContent($encryptedJson);

            if ($result2 && isset($result2['data'])) {
                $this->info("   ✅ Desencriptación JSON exitosa!");
                $this->line("   - needs_encryption: " . ($result2['needs_encryption'] ? 'true' : 'false'));
            } else {
                $this->error("   ❌ Falló la desencriptación JSON");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error JSON: " . $e->getMessage());
        }

        $this->newLine();

        // 4. Probar con JSON sin encriptar
        $this->info('4️⃣ Probando con JSON sin encriptar...');
        try {
            $result3 = JuFileDecryption::decryptJuContent($jsonPayload);

            if ($result3 && isset($result3['data'])) {
                $this->info("   ✅ Procesamiento de JSON sin encriptar exitoso!");
                $this->line("   - needs_encryption: " . ($result3['needs_encryption'] ? 'true' : 'false'));
                $this->line("   - Debería marcarse para reencriptar: " . ($result3['needs_encryption'] ? 'Sí' : 'No'));
            } else {
                $this->error("   ❌ Falló el procesamiento de JSON sin encriptar");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error JSON sin encriptar: " . $e->getMessage());
        }

        $this->newLine();

        // 5. Guardar archivos de prueba
        $this->info('5️⃣ Guardando archivos de prueba...');

        // Guardar archivo encriptado con Crypt::encryptString
        $testPath1 = storage_path('app/ju_files/test_encrypted_string.ju');
        file_put_contents($testPath1, $encryptedString);
        $this->line("   📁 Archivo encriptado (string): " . $testPath1);

        // Guardar archivo encriptado con Crypt::encrypt
        $testPath2 = storage_path('app/ju_files/test_encrypted_json.ju');
        file_put_contents($testPath2, $encryptedJson);
        $this->line("   📁 Archivo encriptado (JSON): " . $testPath2);

        // Guardar archivo sin encriptar
        $testPath3 = storage_path('app/ju_files/test_unencrypted.ju');
        file_put_contents($testPath3, $jsonPayload);
        $this->line("   📁 Archivo sin encriptar: " . $testPath3);

        $this->newLine();
        $this->info('🎯 Pruebas completadas');

        return 0;
    }
}
