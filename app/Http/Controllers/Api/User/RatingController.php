<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'integer', 'exists:game_templates,id', 'required_without:project_id'],
            'project_id'  => ['nullable', 'integer', 'exists:projects,id', 'required_without:template_id'],
            'score'       => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $userId = $request->user()->id;

        $rating = Rating::updateOrCreate(
            [
                'user_id'     => $userId,
                'template_id' => $data['template_id'] ?? null,
                'project_id'  => $data['project_id'] ?? null,
            ],
            ['score' => $data['score']]
        );

        return response()->json([
            'id'     => $rating->id,
            'score'  => $rating->score,
        ]);
    }
    public function clear(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'integer', 'exists:game_templates,id', 'required_without:project_id'],
            'project_id'  => ['nullable', 'integer', 'exists:projects,id', 'required_without:template_id'],
        ]);

        Rating::where('user_id', $request->user()->id)
            ->where('template_id', $data['template_id'] ?? null)
            ->where('project_id', $data['project_id'] ?? null)
            ->delete();

        return response()->json(null, 204);
    }
}