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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('type_id')->constrained()->onDelete('restrict');

            // Données du jeu
            $table->string('title');
            $table->text('description')->nullable();

            $table->integer('duration'); // en minutes
            $table->unsignedInteger('min_players');
            $table->unsignedInteger('max_players');

            // Statuts
            $table->enum('status', ['brouillon', 'fini'])->default('brouillon');
            $table->boolean('is_published')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
