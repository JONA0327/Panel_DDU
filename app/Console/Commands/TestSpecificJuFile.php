<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JuFileDecryption;

class TestSpecificJuFile extends Command
{
    protected $signature = 'test:ju-file {file}';
    protected $description = 'Test decryption of a specific .ju file';

    public function handle()
    {
        $fileName = $this->argument('file');
        $filePath = storage_path("app/ju_files/{$fileName}");

        if (!file_exists($filePath)) {
            $this->error("Archivo no encontrado: {$filePath}");
            return 1;
        }

        $this->info("Probando: {$fileName}");
        $this->info("Ruta: {$filePath}");
        $this->newLine();

        try {
            $result = JuFileDecryption::decrypt($filePath);

            if ($result === null) {
                $this->error('✗ No se pudo desencriptar el archivo');
                return 1;
            }

            $this->info('✓ Archivo desencriptado exitosamente');
            $this->newLine();

            // Extraer información básica
            $info = JuFileDecryption::extractMeetingInfo($result);

            $this->table(['Campo', 'Valor'], [
                ['Proyecto', $result['metadata']['project'] ?? 'No disponible'],
                ['Tipo', $result['metadata']['meeting_type'] ?? 'No disponible'],
                ['Resumen', substr($info['summary'] ?? $result['summary'] ?? 'No disponible', 0, 100) . '...'],
                ['Puntos clave', count($info['key_points'] ?? $result['key_points'] ?? []) . ' puntos'],
                ['Participantes', count($info['participants'] ?? $result['participants'] ?? []) . ' participantes'],
                ['Duración', ($result['duration'] ?? 0) . ' segundos'],
                ['Timestamp', $result['timestamp'] ?? 'No disponible'],
            ]);

            if ($this->confirm('¿Mostrar contenido completo?', false)) {
                $this->newLine();
                $this->line('Contenido completo:');
                $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}
