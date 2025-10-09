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
        Schema::create('google_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('username')->nullable()->index();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTimeTz('expiry_date')->nullable();
            $table->string('scope')->nullable();
            $table->string('token_type')->nullable();
            $table->text('id_token')->nullable();
            $table->timestampTz('token_created_at')->nullable();
            $table->string('recordings_folder_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_tokens');
    }
};
