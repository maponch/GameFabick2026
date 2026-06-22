<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('status', Project::STATUS_PUBLISHED)
            ->with(['user:id,username', 'type', 'template:id,name', 'formats', 'objects', 'ratings'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id'                => $p->id,
                'name'              => $p->name,
                'description'       => $p->description,
                'mode'              => $p->mode,
                'type'              => $p->type?->name,
                'template'          => $p->template?->name,
                'allow_duplication' => $p->allow_duplication,
                'duration_min'      => $p->duration_min,
                'duration_max'      => $p->duration_max,
                'min_players'       => $p->min_players,
                'max_players'       => $p->max_players,
                'formats'           => $p->formats->map(fn ($f) => [
                    'id'   => $f->id,
                    'name' => $f->name,
                    'slug' => $f->slug,
                ]),
                'objects_count'     => $p->objects->count(),
                'author'            => $p->user ? [
                    'id'       => $p->user->id,
                    'username' => $p->user->username,
                ] : null,
                'publishable'       => $p->publishabilityReport(),
                'average_rating' => $p->averageRating(),
                'ratings_count'  => $p->ratingsCount(),
                'my_rating'      => $p->ratings->firstWhere('user_id', $request->user()->id)?->score,
                'created_at'        => $p->created_at,
                'updated_at'        => $p->updated_at,
            ]);

        return response()->json($projects);
    }

    public function show(Request $request, Project $project)
    {
        if ($project->status !== Project::STATUS_PUBLISHED) {
            return response()->json(['message' => 'Projet introuvable ou non publié.'], 404);
        }

        $project->load(['user:id,username', 'type', 'template:id,name,slug', 'formats', 'objects', 'ratings']);

        return response()->json([
            'id'                => $project->id,
            'name'              => $project->name,
            'description'       => $project->description,
            'rules'             => $project->rules,
            'mode'              => $project->mode,
            'type_id'           => $project->type_id,
            'type'              => $project->type?->name,
            'card_schema'       => $project->card_schema,
            'card_layout'       => $project->card_layout ? [
                'slug' => $project->card_layout,
                'name' => \App\Models\CardLayout::where('slug', $project->card_layout)->value('name'),
            ] : null,
            'duration_min'      => $project->duration_min,
            'duration_max'      => $project->duration_max,
            'min_players'       => $project->min_players,
            'max_players'       => $project->max_players,
            'allow_duplication' => $project->allow_duplication,
            'formats'           => $project->formats->map(fn ($f) => [
                'id'   => $f->id,
                'name' => $f->name,
                'slug' => $f->slug,
            ]),
            'objects'           => $project->objects->map(fn ($o) => [
                'id'                    => $o->id,
                'name'                  => $o->name,
                'description'           => $o->description,
                'quantity'              => $o->quantity,
                'default_color'         => $o->default_color,
                'default_image_path'    => $o->default_image_path,
                'existing_deck_mapping' => $o->existing_deck_mapping,
                'custom_data'           => $o->custom_data,
            ]),
            'author'            => $project->user ? [
                'id'       => $project->user->id,
                'username' => $project->user->username,
            ] : null,
            'template'          => $project->template ? [
                'id'   => $project->template->id,
                'name' => $project->template->name,
                'slug' => $project->template->slug,
            ] : null,
            'average_rating' => $project->averageRating(),
            'ratings_count'  => $project->ratingsCount(),
            'my_rating'      => $project->ratings->firstWhere('user_id', $request->user()?->id)?->score,
        ]);
    }
    public function duplicate(Request $request, Project $project)
    {
        if ($project->status !== Project::STATUS_PUBLISHED) {
            return response()->json(['message' => 'Seuls les projets publiés peuvent être dupliqués.'], 403);
        }

        if (!$project->allow_duplication) {
            return response()->json(['message' => 'L\'auteur n\'a pas autorisé la duplication de ce projet.'], 403);
        }

        $project->load(['formats', 'objects']);

        $duplicate = \App\Models\Project::create([
            'user_id'             => $request->user()->id,
            'type_id'             => $project->type_id,
            'template_id'         => $project->template_id,
            'based_on_project_id' => $project->id,
            'name'                => $project->name . ' (copie)',
            'description'         => $project->description,
            'rules'               => $project->rules,
            'card_schema'         => $project->card_schema,
            'card_layout'         => $project->card_layout,
            'mode'                => $project->mode,
            'duration_min'        => $project->duration_min,
            'duration_max'        => $project->duration_max,
            'min_players'         => $project->min_players,
            'max_players'         => $project->max_players,
            'status'              => \App\Models\Project::STATUS_DRAFT,
            'allow_duplication'   => true,
        ]);

        $duplicate->formats()->sync($project->formats->pluck('id')->all());
    
        foreach ($project->objects as $object) {
            $duplicate->objects()->create([
                'name'                  => $object->name,
                'description'           => $object->description,
                'quantity'              => $object->quantity,
                'default_color'         => $object->default_color,
                'default_image_path'    => $object->default_image_path,
                'existing_deck_mapping' => $object->existing_deck_mapping,
                'custom_data'           => $object->custom_data,
            ]);
        }

        \App\Models\PlayHistory::create([
            'user_id'       => $request->user()->id,
            'project_id'    => $duplicate->id,
            'played_at'     => now(),
            'note'          => 'duplicated_from_' . $project->id,
            'snapshot_data' => $duplicate->toSnapshot(),
        ]);

        return response()->json([
            'id'      => $duplicate->id,
            'message' => 'Projet dupliqué dans vos projets.',
        ], 201);
    }
    public function recordPlay(Request $request, Project $project)
    {
        if ($project->status !== Project::STATUS_PUBLISHED) {
            return response()->json(['message' => 'Projet introuvable ou non publié.'], 404);
        }

        $data = $request->validate([
            'mode' => ['nullable', 'in:printable,existing_deck'],
        ]);

        \App\Models\PlayHistory::create([
            'user_id'    => $request->user()->id,
            'project_id' => $project->id,
            'played_at'  => now(),
            'note'       => $data['mode'] ?? null,
        ]);

        return response()->json(['message' => 'Lecture enregistrée.'], 201);
    }
}