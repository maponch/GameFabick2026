<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_formats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('game_template_format', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_template_id')->constrained('game_templates')->cascadeOnDelete();
            $table->foreignId('game_format_id')->constrained('game_formats')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['game_template_id', 'game_format_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_template_format');
        Schema::dropIfExists('game_formats');
    }
};