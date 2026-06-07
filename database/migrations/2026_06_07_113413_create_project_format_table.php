<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_format', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('game_format_id')->constrained('game_formats')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'game_format_id']);
        });

        // Migration de données : copier les formats du template vers le projet
        $projects = DB::table('projects')->get();
        foreach ($projects as $project) {
            $formatIds = DB::table('game_template_format')
                ->where('game_template_id', $project->template_id)
                ->pluck('game_format_id');
            foreach ($formatIds as $formatId) {
                DB::table('project_format')->insert([
                    'project_id'     => $project->id,
                    'game_format_id' => $formatId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_format');
    }
};