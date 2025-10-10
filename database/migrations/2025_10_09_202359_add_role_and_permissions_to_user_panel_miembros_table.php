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
        // Primero poblar la tabla permissions si está vacía
        $permissionsCount = \Illuminate\Support\Facades\DB::table('permissions')->count();
        if ($permissionsCount === 0) {
            \Illuminate\Support\Facades\DB::table('permissions')->insert([
                [
                    'name' => 'colaborador',
                    'display_name' => 'Colaborador',
                    'description' => 'Puede crear, editar y participar en reuniones y actividades',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'lector',
                    'display_name' => 'Lector',
                    'description' => 'Solo puede ver reuniones y contenido, sin editar',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }

        Schema::table('user_panel_miembros', function (Blueprint $table) {
            // Agregar campo para permisos solo si no existe
            if (!Schema::hasColumn('user_panel_miembros', 'permission_id')) {
                $table->foreignId('permission_id')->after('user_id')->constrained('permissions')->default(2); // lector por defecto
            }

            // Agregar campos adicionales para gestión (solo si no existen)
            if (!Schema::hasColumn('user_panel_miembros', 'joined_at')) {
                $table->timestamp('joined_at')->after('permission_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_panel_miembros', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
            $table->dropColumn(['permission_id']);
            if (Schema::hasColumn('user_panel_miembros', 'joined_at')) {
                $table->dropColumn(['joined_at']);
            }
        });
    }
};
