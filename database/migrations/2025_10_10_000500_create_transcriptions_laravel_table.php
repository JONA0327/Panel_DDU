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
        Schema::create('transcriptions_laravel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('username')->nullable()->index();
            $table->string('meeting_name');
            $table->text('meeting_description')->nullable();
            $table->string('status')->default('completed')->index();
            $table->dateTimeTz('started_at')->nullable();
            $table->dateTimeTz('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('transcript_drive_id')->nullable();
            $table->text('transcript_download_url')->nullable();
            $table->string('audio_drive_id')->nullable();
            $table->text('audio_download_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions_laravel');
    }
};
