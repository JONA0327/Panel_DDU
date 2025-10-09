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
        Schema::create('meeting_content_relations', function (Blueprint $table) {
            $table->unsignedInteger('container_id');
            $table->unsignedBigInteger('meeting_id');
            $table->timestamps();

            $table->primary(['container_id', 'meeting_id']);

            $table->foreign('container_id')->references('id')->on('meeting_content_containers')->cascadeOnDelete();
            $table->foreign('meeting_id')->references('id')->on('transcriptions_laravel')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_content_relations');
    }
};
