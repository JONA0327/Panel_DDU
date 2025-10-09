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
        Schema::create('meeting_content_containers', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('google_token_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('google_folder_id')->nullable();
            $table->timestamps();

            $table->foreign('google_token_id')->references('id')->on('google_tokens')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_content_containers');
    }
};
