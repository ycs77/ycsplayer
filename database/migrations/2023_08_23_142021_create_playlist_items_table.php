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
        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->char('type', 10);
            $table->string('title', 20);
            $table->string('url');
            $table->string('thumbnail')->nullable();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_items');
    }
};