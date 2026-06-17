<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProjectModerated;
use App\Models\ModerationAction;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ModerationController extends Controller
{
    public function index()
    {
        $projects = Project::where('status', Project::STATUS_PUBLISHED)
            ->with(['user:id,username,email', 'type', 'template:id,name'])
            ->withCount(['moderationActions as moderation_count'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'description'      => $p->description,
                'status'           => $p->status,
                'type'             => $p->type?->name,
                'template'         => $p->template?->name,
                'user'             => [
                    'id'       => $p->user->id,
                    'username' => $p->user->username,
                    'email'    => $p->user->email,
                ],
                'moderation_count' => $p->moderation_count,
                'updated_at'       => $p->updated_at,
            ]);

        return response()->json($projects);
    }

    public function moderate(Request $request, Project $project)
    {
        $data = $request->validate([
            'reason_code' => ['required', 'in:spam,inappropriate,low_quality,copyright,other'],
            'reason_text' => ['nullable', 'string', 'max:1000', 'required_if:reason_code,other'],
        ], [
            'reason_text.required_if' => 'Une précision est obligatoire pour le motif "Autre".',
        ]);

        if ($project->status !== Project::STATUS_PUBLISHED) {
            return response()->json([
                'message' => 'Seuls les projets publiés peuvent être modérés.',
            ], 422);
        }

        $action = ModerationAction::create([
            'project_id'       => $project->id,
            'user_id_targeted' => $project->user_id,
            'admin_id'         => $request->user()->id,
            'action'           => ModerationAction::ACTION_ARCHIVE,
            'reason_code'      => $data['reason_code'],
            'reason_text'      => $data['reason_text'] ?? null,
        ]);

        $project->update(['status' => Project::STATUS_ARCHIVED]);

        $project->loadMissing('user');
        Mail::to($project->user->email)->send(new ProjectModerated($project, $project->user, $action));

        return response()->json([
            'message' => 'Projet modéré et notification envoyée.',
            'project' => [
                'id'     => $project->id,
                'status' => $project->status,
            ],
        ]);
    }
}