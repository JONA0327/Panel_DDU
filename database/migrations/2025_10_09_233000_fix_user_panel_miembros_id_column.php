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
        // First check if the table exists
        $tableExists = Schema::hasTable('user_panel_miembros');

        if (!$tableExists) {
            // Create the table if it doesn't exist
            Schema::create('user_panel_miembros', function (Blueprint $table) {
                $table->id(); // Auto-incrementing primary key
                $table->unsignedBigInteger('panel_id')->default(1);
                $table->string('user_id'); // UUID from users table
                $table->string('role');
                $table->foreignId('permission_id')->constrained('permissions');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Add foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } else {
            // Table exists, check if id is UUID or integer
            $result = DB::select("SHOW COLUMNS FROM user_panel_miembros WHERE Field = 'id'");
            if (!empty($result)) {
                $column = $result[0];
                $isUuid = (strpos(strtolower($column->Type), 'char') !== false || strpos(strtolower($column->Type), 'varchar') !== false);

                if ($isUuid) {
                    // The id column is currently UUID, we need to recreate the table structure
                    // First, backup existing data
                    $existingData = DB::table('user_panel_miembros')->get();

                    // Drop the table and recreate it properly
                    Schema::dropIfExists('user_panel_miembros');

                    Schema::create('user_panel_miembros', function (Blueprint $table) {
                        $table->id(); // Auto-incrementing integer primary key
                        $table->unsignedBigInteger('panel_id')->default(1);
                        $table->string('user_id'); // UUID from users table
                        $table->string('role');
                        $table->foreignId('permission_id')->constrained('permissions');
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();

                        // Add foreign key constraint
                        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    });

                    // Restore data without the old id (let auto-increment handle new ids)
                    foreach ($existingData as $data) {
                        // Convert panel_id from UUID to integer (default to 1 for DDU)
                        $panelId = is_numeric($data->panel_id) ? (int)$data->panel_id : 1;

                        DB::table('user_panel_miembros')->insert([
                            'panel_id' => $panelId,
                            'user_id' => $data->user_id,
                            'role' => $data->role,
                            'permission_id' => $data->permission_id ?? 2,
                            'is_active' => $data->is_active ?? true,
                            'created_at' => $data->created_at ?? now(),
                            'updated_at' => $data->updated_at ?? now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop if we created it
        // Schema::dropIfExists('user_panel_miembros');
    }
};
