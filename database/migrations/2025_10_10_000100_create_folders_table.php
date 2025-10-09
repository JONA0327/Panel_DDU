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
        Schema::create('folders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('google_token_id');
            $table->string('google_id')->unique();
            $table->string('name');
            $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('google_token_id')->references('id')->on('google_tokens')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('folders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
