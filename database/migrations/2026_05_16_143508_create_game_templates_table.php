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
        Schema::create('game_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('rules');
            $table->foreignId('type_id')->constrained()->onDelete('restrict');
            $table->unsignedInteger('min_players');
            $table->unsignedInteger('max_players');
            $table->unsignedInteger('duration_min');
            $table->unsignedInteger('duration_max');
            $table->boolean('supports_existing_deck')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_templates');
    }
};
