<?php

namespace App\Console\Commands;

use App\Services\JuFileDecryption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestNewJuDecryption extends Command
{
    protected $signature = 'test:new-ju-decryption {filename?}';
    protected $description = 'Prueba el nuevo sistema de desencriptación de archivos .ju siguiendo el flujo documentado';

    public function handle()
    {
        $filename = $this->argument('filename') ?? 'Kualifin_Test.ju';

        $this->info("🔧 Probando nuevo sistema de desencriptación para: {$filename}");
        $this->newLine();

        // Buscar el archivo en diferentes ubicaciones
        $possiblePaths = [
            storage_path("app/meetings/jonathancox/"),
            storage_path("app/transcripts/jonathancox/"),
            storage_path("app/ju_files/"),
            storage_path("app/private/meetings/"),
            base_path(""),
        ];

        $filePath = null;
        foreach ($possiblePaths as $basePath) {
            $testPath = $basePath . $filename;
            if (file_exists($testPath)) {
                $filePath = $testPath;
                break;
            }
        }

        if (!$filePath) {
            $this->error("❌ Archivo no encontrado: {$filename}");
            $this->info("Ubicaciones buscadas:");
            foreach ($possiblePaths as $path) {
                $this->line("  - {$path}{$filename}");
            }
            return 1;
        }

        $this->info("📁 Archivo encontrado en: {$filePath}");
        $this->newLine();

        // Leer contenido crudo
        $rawContent = file_get_contents($filePath);
        $this->info("📊 Información del archivo:");
        $this->line("  - Tamaño: " . strlen($rawContent) . " bytes");
        $this->line("  - Primeros 50 caracteres: " . substr($rawContent, 0, 50));
        $this->line("  - Últimos 50 caracteres: " . substr($rawContent, -50));

        // Detectar formato
        if (substr($rawContent, 0, 3) === 'eyJ') {
            $this->line("  - Formato detectado: Laravel Crypt encriptado (base64)");
        } else if (str_contains($rawContent, '"iv"') && str_contains($rawContent, '"value"')) {
            $this->line("  - Formato detectado: Laravel Crypt JSON format");
        } else {
            $decoded = json_decode($rawContent, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->line("  - Formato detectado: JSON sin encriptar");
            } else {
                $this->line("  - Formato detectado: Desconocido/Otro formato");
            }
        }

        $this->newLine();

        // Probar método directo de contenido
        $this->info("🔄 Probando desencriptación directa del contenido...");
        try {
            $result = JuFileDecryption::decryptJuContent($rawContent);

            if ($result && isset($result['data'])) {
                $this->info("✅ Desencriptación exitosa!");
                $this->line("  - needs_encryption: " . ($result['needs_encryption'] ? 'true' : 'false'));

                if ($result['data']) {
                    $data = $result['data'];
                    $this->line("  - Resumen disponible: " . (isset($data['summary']) && $data['summary'] ? 'Sí' : 'No'));
                    $this->line("  - Puntos clave: " . (is_array($data['key_points']) ? count($data['key_points']) : 0));
                    $this->line("  - Segmentos: " . (is_array($data['segments']) ? count($data['segments']) : 0));
                    $this->line("  - Participantes: " . (is_array($data['participants']) ? count($data['participants']) : 0));

                    if (isset($data['summary']) && $data['summary']) {
                        $this->newLine();
                        $this->info("📝 Resumen:");
                        $this->line(substr($data['summary'], 0, 200) . (strlen($data['summary']) > 200 ? '...' : ''));
                    }

                    if (is_array($data['key_points']) && !empty($data['key_points'])) {
                        $this->newLine();
                        $this->info("🔑 Primeros puntos clave:");
                        foreach (array_slice($data['key_points'], 0, 3) as $point) {
                            $this->line("  • " . $point);
                        }
                    }
                }
            } else {
                $this->error("❌ No se pudieron obtener datos");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error en desencriptación: " . $e->getMessage());
        }

        $this->newLine();

        // Probar método de archivo completo
        $this->info("🔄 Probando desencriptación desde archivo...");
        try {
            $fileResult = JuFileDecryption::decrypt($filePath);

            if ($fileResult) {
                $this->info("✅ Desencriptación desde archivo exitosa!");
                $this->line("  - Tipo de datos: " . gettype($fileResult));
                if (is_array($fileResult)) {
                    $this->line("  - Claves disponibles: " . implode(', ', array_keys($fileResult)));
                }
            } else {
                $this->error("❌ Desencriptación desde archivo falló");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error en desencriptación desde archivo: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("🎯 Prueba completada");

        return 0;
    }
}
