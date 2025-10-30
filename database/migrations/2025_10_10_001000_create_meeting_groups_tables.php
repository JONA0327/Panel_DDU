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
        Schema::create('meeting_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('owner_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('meeting_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_group_id')->constrained('meeting_groups')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->unique(['meeting_group_id', 'user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('meeting_group_meeting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_group_id')->constrained('meeting_groups')->cascadeOnDelete();
            $table->unsignedBigInteger('meeting_id');
            $table->timestamps();

            $table->unique(['meeting_group_id', 'meeting_id']);
            $table->foreign('meeting_id')->references('id')->on('transcriptions_laravel')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_group_meeting');
        Schema::dropIfExists('meeting_group_user');
        Schema::dropIfExists('meeting_groups');
    }
};
