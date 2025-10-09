<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // colaborador, lector
            $table->string('description');
            $table->json('abilities')->nullable(); // permisos específicos como array JSON
            $table->timestamps();
        });

        // Insertar permisos por defecto
        DB::table('permissions')->insert([
            [
                'name' => 'colaborador',
                'description' => 'Puede crear, editar y participar en reuniones y actividades',
                'abilities' => json_encode([
                    'reuniones.crear',
                    'reuniones.editar',
                    'reuniones.participar',
                    'asistente.usar',
                    'documentos.subir',
                    'comentarios.crear'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'lector',
                'description' => 'Solo puede ver reuniones y contenido, sin editar',
                'abilities' => json_encode([
                    'reuniones.ver',
                    'asistente.ver',
                    'documentos.ver',
                    'comentarios.ver'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
