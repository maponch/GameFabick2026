<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function changeStatus(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()?->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $project->update(['status' => $data['status']]);

        return response()->json([
            'id'     => $project->id,
            'status' => $project->status,
        ]);
    }
    public function recordPlay(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()?->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $data = $request->validate([
            'mode' => ['nullable', 'in:printable,existing_deck'],
        ]);

        \App\Models\PlayHistory::create([
            'user_id'       => $request->user()->id,
            'project_id'    => $project->id,
            'played_at'     => now(),
            'note'          => $data['mode'] ?? null,
            'snapshot_data' => $project->toSnapshot(),
        ]);

        return response()->json(['message' => 'Lecture enregistrée.'], 201);
    }
}