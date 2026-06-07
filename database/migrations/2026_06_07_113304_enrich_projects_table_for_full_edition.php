<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->text('rules')->nullable()->after('description');
            $table->json('card_schema')->nullable()->after('rules');
            $table->string('card_layout')->nullable()->after('card_schema');
            $table->integer('duration_min')->nullable()->after('duration');
            $table->integer('duration_max')->nullable()->after('duration_min');
        });

        // Migration de données : duration -> duration_min/max + remplir rules/card_schema/card_layout depuis le template
        $projects = DB::table('projects')->get();
        foreach ($projects as $project) {
            $template = DB::table('game_templates')->where('id', $project->template_id)->first();
            DB::table('projects')->where('id', $project->id)->update([
                'duration_min' => $project->duration,
                'duration_max' => $project->duration,
                'rules'        => $template?->rules,
                'card_schema'  => $template?->card_schema,
                'card_layout'  => $template?->card_layout,
            ]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('duration')->nullable();
            $table->boolean('is_published')->default(false);
        });

        DB::statement('UPDATE projects SET duration = duration_min, is_published = false');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['rules', 'card_schema', 'card_layout', 'duration_min', 'duration_max']);
            $table->renameColumn('name', 'title');
        });
    }
};