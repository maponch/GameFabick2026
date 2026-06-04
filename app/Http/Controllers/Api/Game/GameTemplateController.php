<?php
namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Models\GameTemplate;

class GameTemplateController extends Controller
{
    public function index()
    {
        $templates = GameTemplate::with(['type', 'objects', 'formats'])
            ->published()
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'name'           => $t->name,
                'slug'           => $t->slug,
                'description'    => $t->description,
                'type'           => $t->type?->name,
                'min_players'    => $t->min_players,
                'max_players'    => $t->max_players,
                'duration_min'   => $t->duration_min,
                'duration_max'   => $t->duration_max,
                'formats'        => $t->formats->map(fn($f) => [
                    'id'   => $f->id,
                    'name' => $f->name,
                    'slug' => $f->slug,
                ]),
                'objects_count'  => $t->objects->count(),
            ]);
        return response()->json($templates);
    }

    public function show(string $slug)
    {
        $template = GameTemplate::with(['type', 'objects', 'formats'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
        return response()->json([
            'id'           => $template->id,
            'name'         => $template->name,
            'slug'         => $template->slug,
            'description'  => $template->description,
            'rules'        => $template->rules,
            'type'         => $template->type?->name,
            'min_players'  => $template->min_players,
            'max_players'  => $template->max_players,
            'duration_min' => $template->duration_min,
            'duration_max' => $template->duration_max,
            'formats'      => $template->formats->map(fn($f) => [
                'id'   => $f->id,
                'name' => $f->name,
                'slug' => $f->slug,
            ]),
            'objects'      => $template->objects->map(fn($o) => [
                'id'                    => $o->id,
                'name'                  => $o->name,
                'description'           => $o->description,
                'quantity'              => $o->quantity,
                'default_color'         => $o->default_color,
                'default_image_path'    => $o->default_image_path,
                'existing_deck_mapping' => $o->existing_deck_mapping,
            ]),
        ]);
    }
}