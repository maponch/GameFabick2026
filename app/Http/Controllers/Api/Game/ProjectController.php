<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Models\GameTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Liste des projets de l'utilisateur connecté
    public function index(Request $request)
    {
        $projects = $request->user()->projects()
            ->with(['template', 'type'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'title'        => $p->title,
                'description'  => $p->description,
                'mode'         => $p->mode,
                'status'       => $p->status,
                'duration'     => $p->duration,
                'min_players'  => $p->min_players,
                'max_players'  => $p->max_players,
                'template'     => $p->template?->name,
                'created_at'   => $p->created_at,
            ]);

        return response()->json($projects);
    }

    // Création d'un projet à partir d'un template
    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:game_templates,id',
            'title'       => 'required|string|max:191',
            'mode'        => 'required|in:printable,existing_deck',
            'players'     => 'required|integer|min:1',
        ]);

        $template = GameTemplate::with('objects')->findOrFail($data['template_id']);

        // Vérifie cohérence avec le template
        if ($data['players'] < $template->min_players || $data['players'] > $template->max_players) {
            return response()->json([
                'errors' => ['players' => ["Le nombre de joueurs doit être entre {$template->min_players} et {$template->max_players}."]]
            ], 422);
        }

        if ($data['mode'] === 'existing_deck' && !$template->supports_existing_deck) {
            return response()->json([
                'errors' => ['mode' => ['Ce jeu ne supporte pas le mode jeu de cartes existant.']]
            ], 422);
        }

        // Crée le projet
        $project = Project::create([
            'user_id'      => $request->user()->id,
            'type_id'      => $template->type_id,
            'template_id'  => $template->id,
            'title'        => $data['title'],
            'description'  => $template->description,
            'mode'         => $data['mode'],
            'duration'     => $template->duration_min,
            'min_players'  => $template->min_players,
            'max_players'  => $template->max_players,
            'status'       => 'brouillon',
            'is_published' => false,
        ]);

        // Attache les objets du template au projet (avec leurs valeurs par défaut)
        foreach ($template->objects as $object) {
            $project->objects()->attach($object->id);
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

        $project->load(['template', 'type', 'objects']);

        return response()->json([
            'id'           => $project->id,
            'title'        => $project->title,
            'description'  => $project->description,
            'mode'         => $project->mode,
            'status'       => $project->status,
            'duration'     => $project->duration,
            'min_players'  => $project->min_players,
            'max_players'  => $project->max_players,
            'is_published' => $project->is_published,
            'template'     => $project->template ? [
                'id'          => $project->template->id,
                'name'        => $project->template->name,
                'slug'        => $project->template->slug,
                'rules'       => $project->template->rules,
                'card_schema' => $project->template->card_schema,
                'card_layout' => $project->template->card_layout ? [
                    'slug' => $project->template->card_layout,
                    'name' => \App\Models\CardLayout::where('slug', $project->template->card_layout)->value('name'),
                ] : null,
            ] : null,
            'objects'      => $project->objects->map(fn($o) => [
                'id'                    => $o->id,
                'name'                  => $o->name,
                'description'           => $o->description,
                'quantity'              => $o->quantity,
                'default_color'         => $o->default_color,
                'default_image_path'    => $o->default_image_path,
                'existing_deck_mapping' => $o->existing_deck_mapping,
                'custom_image_id'       => $o->pivot->custom_image_id,
                'custom_text'           => $o->pivot->custom_text,
                'custom_color'          => $o->pivot->custom_color,
                'custom_data'           => $o->custom_data,
            ]),
            'created_at'   => $project->created_at,
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
            ->get(['id', 'title', 'created_at']);

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