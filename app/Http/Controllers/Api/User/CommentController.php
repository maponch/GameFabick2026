<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\GameTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function indexForTemplate(Request $request, GameTemplate $template)
    {
        if ($template->status !== GameTemplate::STATUS_PUBLISHED) {
            return response()->json(['message' => 'Template non publié.'], 404);
        }

        return $this->respondWithComments(
            Comment::where('template_id', $template->id)->with('user:id,username')->latest()->get()
        );
    }

    public function indexForProject(Request $request, Project $project)
    {
        $userId = $request->user()->id;
        $isOwner = $project->user_id === $userId;

        if (!$isOwner && $project->status !== Project::STATUS_PUBLISHED) {
            return response()->json(['message' => 'Projet non visible.'], 404);
        }

        return $this->respondWithComments(
            Comment::where('project_id', $project->id)->with('user:id,username')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'integer', 'exists:game_templates,id', 'required_without:project_id'],
            'project_id'  => ['nullable', 'integer', 'exists:projects,id', 'required_without:template_id'],
            'content'     => ['required', 'string', 'max:1000'],
        ]);

        $userId = $request->user()->id;

        $comment = Comment::updateOrCreate(
            [
                'user_id'     => $userId,
                'template_id' => $data['template_id'] ?? null,
                'project_id'  => $data['project_id'] ?? null,
            ],
            ['content' => $data['content']]
        );

        $comment->load('user:id,username');

        return response()->json($this->serialize($comment), 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();
        if ($comment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }
        $comment->delete();
        return response()->json(null, 204);
    }

    private function respondWithComments($comments)
    {
        return response()->json($comments->map(fn ($c) => $this->serialize($c)));
    }

    private function serialize(Comment $comment): array
    {
        return [
            'id'         => $comment->id,
            'content'    => $comment->content,
            'user_id'    => $comment->user_id,
            'user'       => [
                'id'       => $comment->user->id,
                'username' => $comment->user->username,
            ],
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
        ];
    }
}