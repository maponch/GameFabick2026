<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreProjectObjectRequest;
use App\Http\Requests\User\UpdateProjectObjectRequest;
use App\Models\Project;
use App\Models\ProjectObject;

class ProjectObjectController extends Controller
{
    public function index(Project $project)
    {
        $this->authorizeOwnership($project);

        return response()->json($project->objects->map(fn ($o) => $this->serialize($o)));
    }

    public function store(StoreProjectObjectRequest $request, Project $project)
    {
        $object = $project->objects()->create($request->validated());

        return response()->json($this->serialize($object), 201);
    }

    public function update(UpdateProjectObjectRequest $request, Project $project, ProjectObject $object)
    {
        if ($object->project_id !== $project->id) {
            return response()->json(['message' => 'Cet objet n\'appartient pas à ce projet.'], 404);
        }

        $object->update($request->validated());

        return response()->json($this->serialize($object));
    }

    public function destroy(Project $project, ProjectObject $object)
    {
        $this->authorizeOwnership($project);

        if ($object->project_id !== $project->id) {
            return response()->json(['message' => 'Cet objet n\'appartient pas à ce projet.'], 404);
        }

        $object->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwnership(Project $project): void
    {
        abort_unless($project->user_id === request()->user()?->id, 403);
    }

    private function serialize(ProjectObject $object): array
    {
        return [
            'id'                    => $object->id,
            'name'                  => $object->name,
            'description'           => $object->description,
            'quantity'              => $object->quantity,
            'default_color'         => $object->default_color,
            'default_image_path'    => $object->default_image_path,
            'existing_deck_mapping' => $object->existing_deck_mapping,
            'custom_data'           => $object->custom_data,
        ];
    }
}