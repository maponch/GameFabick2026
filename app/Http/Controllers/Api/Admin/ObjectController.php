<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreObjectRequest;
use App\Http\Requests\Admin\UpdateObjectRequest;
use App\Models\GameObject;
use App\Models\GameTemplate;

class ObjectController extends Controller
{
    public function index(GameTemplate $template)
    {
        $objects = $template->objects()->orderBy('name')->get()->map(fn ($o) => [
            'id'                    => $o->id,
            'name'                  => $o->name,
            'description'           => $o->description,
            'quantity'              => $o->quantity,
            'default_color'         => $o->default_color,
            'default_image_path'    => $o->default_image_path,
            'existing_deck_mapping' => $o->existing_deck_mapping,
            'custom_data'           => $o->custom_data,
        ]);

        return response()->json($objects);
    }

    public function store(StoreObjectRequest $request, GameTemplate $template)
    {
        $object = GameObject::create($request->validated());
        $template->objects()->attach($object->id);

        return response()->json(['id' => $object->id], 201);
    }

    public function update(UpdateObjectRequest $request, GameTemplate $template, GameObject $object)
    {
        if (!$template->objects()->where('objects.id', $object->id)->exists()) {
            return response()->json(['message' => 'Cet objet n\'appartient pas à ce template.'], 404);
        }

        $object->update($request->validated());

        return response()->json(['id' => $object->id]);
    }

    public function destroy(GameTemplate $template, GameObject $object)
    {
        if ($template->status === GameTemplate::STATUS_PUBLISHED) {
            return response()->json([
                'message' => 'Ce template est publié. Repassez-le en brouillon pour modifier ses cartes.',
            ], 422);
        }
        if (!$template->objects()->where('objects.id', $object->id)->exists()) {
            return response()->json(['message' => 'Cet objet n\'appartient pas à ce template.'], 404);
        }

        $usedInProjects = $object->projects()->exists();

        $template->objects()->detach($object->id);

        if (!$usedInProjects && $object->templates()->count() === 0) {
            $object->delete();
        }

        return response()->json(null, 204);
    }
}