<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Models\GameTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->projects()
            ->with(['template', 'type', 'formats', 'objects'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'description'  => $p->description,
                'mode'         => $p->mode,
                'status'       => $p->status,
                'duration_min' => $p->duration_min,
                'duration_max' => $p->duration_max,
                'min_players'  => $p->min_players,
                'max_players'  => $p->max_players,
                'type'         => $p->type?->name,
                'template'     => $p->template?->name,
                'publishable'  => $p->publishabilityReport(),
                'created_at'   => $p->created_at,
                'updated_at'   => $p->updated_at,
            ]);

        return response()->json($projects);
    }

    // Création d'un projet à partir d'un template
    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:game_templates,id',
            'name'        => 'required|string|max:191',
            'mode'        => 'required|in:printable,existing_deck',
            'players'     => 'required|integer|min:1',
        ]);

        $template = GameTemplate::with(['objects', 'formats'])->findOrFail($data['template_id']);

        // Vérifie cohérence avec le template
        if ($data['players'] < $template->min_players || $data['players'] > $template->max_players) {
            return response()->json([
                'errors' => ['players' => ["Le nombre de joueurs doit être entre {$template->min_players} et {$template->max_players}."]]
            ], 422);
        }

        if ($data['mode'] === 'existing_deck') {
            $hasCartesClassiques = $template->formats()->where('slug', 'cartes-classiques')->exists();
            if (!$hasCartesClassiques) {
                return response()->json([
                    'errors' => ['mode' => ['Ce jeu ne supporte pas le mode jeu de cartes existant.']]
                ], 422);
            }
        }

        // Crée le projet
        $project = Project::create([
            'user_id'      => $request->user()->id,
            'type_id'      => $template->type_id,
            'template_id'  => $template->id,
            'name'         => $data['name'],
            'description'  => $template->description,
            'rules'        => $template->rules,
            'card_schema'  => $template->card_schema,
            'card_layout'  => $template->card_layout,
            'mode'         => $data['mode'],
            'duration_min' => $template->duration_min,
            'duration_max' => $template->duration_max,
            'min_players'  => $template->min_players,
            'max_players'  => $template->max_players,
            'status'       => Project::STATUS_DRAFT,
        ]);
        $project->formats()->sync($template->formats->pluck('id')->all());
        foreach ($template->objects as $object) {
            $project->objects()->create([
                'name'                  => $object->name,
                'description'           => $object->description,
                'quantity'              => $object->quantity,
                'default_color'         => $object->default_color,
                'default_image_path'    => $object->default_image_path,
                'existing_deck_mapping' => $object->existing_deck_mapping,
                'custom_data'           => $object->custom_data,
            ]);
        }

        return response()->json([
            'id'      => $project->id,
            'message' => 'Projet créé avec succès.',
        ], 201);
    }

    public function show(Request $request, Project $project)
    {
        // Sécurité : seul le propriétaire peut voir son projet
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $project->load(['template', 'type', 'objects',  'formats']);

        return response()->json([
            'id'           => $project->id,
            'name'         => $project->name,
            'type_id'      => $project->type_id,
            'card_schema'  => $project->card_schema,
            'card_layout'  => $project->card_layout ? [
                'slug' => $project->card_layout,
                'name' => \App\Models\CardLayout::where('slug', $project->card_layout)->value('name'),
            ] : null,
            'formats'      => $project->formats->map(fn ($f) => [
                'id'   => $f->id,
                'name' => $f->name,
                'slug' => $f->slug,
            ]),
            'description'  => $project->description,
            'rules'        => $project->rules,
            'mode'         => $project->mode,
            'status'       => $project->status,
            'duration_min' => $project->duration_min,
            'duration_max' => $project->duration_max,
            'min_players'  => $project->min_players,
            'max_players'  => $project->max_players,
            'template'     => $project->template ? [
                'id'    => $project->template->id,
                'name'  => $project->template->name,
                'slug'  => $project->template->slug,
                'rules' => $project->template->rules,
            ] : null,
            'based_on'     => $project->template_id ? [
                    'id'   => $project->template->id,
                    'name' => $project->template->name,
                    'slug' => $project->template->slug,
                ] : null,
            'objects' => $project->objects->map(fn ($o) => [
                'id'                    => $o->id,
                'name'                  => $o->name,
                'description'           => $o->description,
                'quantity'              => $o->quantity,
                'default_color'         => $o->default_color,
                'default_image_path'    => $o->default_image_path,
                'existing_deck_mapping' => $o->existing_deck_mapping,
                'custom_data'           => $o->custom_data,
            ]),
            'created_at'   => $project->created_at,
            'publishable'  => $project->publishabilityReport(),
            'allow_duplication' => $project->allow_duplication,
        ]);
    }
    public function findSimilar(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:game_templates,id',
            'mode'        => 'required|in:printable,existing_deck',
        ]);

        $similar = $request->user()->projects()
            ->where('template_id', $data['template_id'])
            ->where('mode', $data['mode'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'created_at']);

        return response()->json($similar);
    }

    // Suppression d'un projet
    public function destroy(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Projet supprimé.']);
    }
}