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
        Schema::create('subfolders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('folder_id');
            $table->string('google_id')->unique();
            $table->string('name');
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subfolders');
    }
};
