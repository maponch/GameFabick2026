<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('default_color')->nullable();
            $table->string('default_image_path')->nullable();
            $table->json('existing_deck_mapping')->nullable();
            $table->json('custom_data')->nullable();
            $table->timestamps();
        });

        // Migration des données : clone des objets actuels vers project_objects
        // L'ancien pivot object_project lie un projet à des objets partagés (via object_id),
        // potentiellement avec des surcharges custom_text/custom_color/custom_image_id.
        // On résout en clonant l'objet ET en appliquant les surcharges sur le clone.
        $pivots = DB::table('object_project')->get();
        foreach ($pivots as $pivot) {
            $object = DB::table('objects')->where('id', $pivot->object_id)->first();
            if (!$object) continue;

            DB::table('project_objects')->insert([
                'project_id'            => $pivot->project_id,
                'name'                  => $pivot->custom_text ?? $object->name,
                'description'           => $object->description,
                'quantity'              => $object->quantity,
                'default_color'         => $pivot->custom_color ?? $object->default_color,
                'default_image_path'    => $object->default_image_path,
                'existing_deck_mapping' => $object->existing_deck_mapping,
                'custom_data'           => $object->custom_data,
                'created_at'            => $pivot->created_at,
                'updated_at'            => $pivot->updated_at,
            ]);
        }

        // Suppression de l'ancien pivot
        Schema::dropIfExists('object_project');
    }

    public function down(): void
    {
        Schema::create('object_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('objects')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('custom_image_id')->nullable();
            $table->string('custom_text')->nullable();
            $table->string('custom_color')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('project_objects');
    }
};