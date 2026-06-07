<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProjectRequest;
use App\Models\Project;

class ProjectController extends Controller
{
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        $formatIds = $data['format_ids'] ?? null;
        unset($data['format_ids']);

        $project->update($data);

        if ($formatIds !== null) {
            $project->formats()->sync($formatIds);
        }

        return response()->json([
            'id'      => $project->id,
            'message' => 'Projet mis à jour.',
        ]);
    }
}