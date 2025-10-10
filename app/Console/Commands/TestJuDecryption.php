<?php

namespace App\Console\Commands;

use App\Services\JuFileDecryption;
use Illuminate\Console\Command;

class TestJuDecryption extends Command
{
    protected $signature = 'test:ju-decrypt {file?}';
    protected $description = 'Probar la desencriptación de archivos .ju';

    public function handle()
    {
        $file = $this->argument('file');

        if (!$file) {
            // Buscar archivos .ju en los directorios
            $juFiles = [
                ...glob(storage_path('app/ju_files/*.ju')),
                ...glob(storage_path('app/meetings/*/*.ju')),
            ];

            if (empty($juFiles)) {
                $this->error('No se encontraron archivos .ju para probar.');
                return 1;
            }

            $this->info('Archivos .ju encontrados:');
            foreach ($juFiles as $index => $juFile) {
                $this->line(($index + 1) . '. ' . basename($juFile) . ' - ' . $juFile);
            }

            $choice = (int) $this->ask('Selecciona el número del archivo a probar', '1');
            $file = $juFiles[$choice - 1] ?? $juFiles[0];
        }

        if (!file_exists($file)) {
            $this->error("Archivo no encontrado: {$file}");
            return 1;
        }

        $this->info("Probando desencriptación de: {$file}");
        $this->newLine();

        $decrypted = JuFileDecryption::decrypt($file);

        if (!$decrypted) {
            $this->error('No se pudo desencriptar el archivo.');
            return 1;
        }

        $this->info('✓ Archivo desencriptado exitosamente');
        $this->newLine();

        $extracted = JuFileDecryption::extractMeetingInfo($decrypted);

        $this->info('Información extraída:');
        $this->table([
            'Campo', 'Valor'
        ], [
            ['Resumen', $extracted['summary'] ? substr($extracted['summary'], 0, 100) . '...' : 'No disponible'],
            ['Puntos clave', count($extracted['key_points']) . ' puntos'],
            ['Segmentos', count($extracted['segments']) . ' segmentos'],
            ['Participantes', count($extracted['participants']) . ' participantes'],
            ['Duración', $extracted['duration'] ? $extracted['duration'] . ' segundos' : 'No disponible'],
            ['Timestamp', $extracted['timestamp'] ?? 'No disponible'],
        ]);

        if ($this->confirm('¿Mostrar contenido completo?', false)) {
            $this->newLine();
            $this->info('Contenido completo:');
            $this->line(json_encode($extracted, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return 0;
    }
}
