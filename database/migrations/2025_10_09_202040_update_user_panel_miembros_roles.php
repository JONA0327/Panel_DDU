<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_panel_miembros', function (Blueprint $table) {
            // Actualizar la columna role para incluir los nuevos roles
            $table->enum('role', ['administrador', 'ventas', 'administracion'])->default('ventas')->change();
            
            // Agregar campos adicionales para el panel
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_access')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_panel_miembros', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'last_access']);
            // Revertir role a su estado anterior si es necesario
        });
    }
};
