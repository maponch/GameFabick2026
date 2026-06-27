<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'           => ['nullable', Rule::in(Report::STATUSES)],
            'reportable_type'  => ['nullable', Rule::in(['project', 'comment', 'template'])],
            'per_page'         => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $typeMap = [
            'project' => \App\Models\Project::class,
            'comment' => \App\Models\Comment::class,
            'template' => \App\Models\GameTemplate::class,
        ];

        $query = Report::with([
                    'reporter:id,username',
                    'reviewer:id,username',
                    'reportable' => function (MorphTo $morphTo) {
                        $morphTo->morphWith([
                            Comment::class => ['project:id,name', 'template:id,name,slug'],
                        ]);
                    },
                ])
                ->orderByRaw("FIELD(status, 'pending', 'reviewed', 'dismissed')")
                ->orderByDesc('created_at');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['reportable_type'])) {
            $query->where('reportable_type', $typeMap[$validated['reportable_type']]);
        }

        $reports = $query->paginate($validated['per_page'] ?? 25);

        return response()->json($reports);
    }

    public function show(Report $report): JsonResponse
    {
        $report->load([
                        'reporter:id,username,email',
                        'reviewer:id,username',
                        'reportable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Comment::class => ['project:id,name', 'template:id,name,slug', 'user:id,username'],
                            ]);
                        },
                    ]);

    
        $relatedPending = Report::where('reportable_type', $report->reportable_type)
            ->where('reportable_id', $report->reportable_id)
            ->where('id', '!=', $report->id)
            ->where('status', Report::STATUS_PENDING)
            ->with('reporter:id,username')
            ->get();

        return response()->json([
            'report'          => $report,
            'related_pending' => $relatedPending,
        ]);
    }

    public function update(Request $request, Report $report): JsonResponse
    {
        $validated = $request->validate([
            'status'     => ['required', Rule::in([Report::STATUS_REVIEWED, Report::STATUS_DISMISSED])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($report->status !== Report::STATUS_PENDING) {
            return response()->json(['message' => 'Ce signalement a déjà été traité.'], 422);
        }

        $adminId  = $request->user()->id;
        $autoNote = null;

        if ($validated['status'] === Report::STATUS_REVIEWED) {
            $target = $report->reportable;
            if (! $target) {
                return response()->json(['message' => 'La cible du signalement n\'existe plus.'], 422);
            }

            try {
                $autoNote = $this->actOnTarget($report, $target, $adminId);
            } catch (\RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            // Tous les autres reports pending sur la même cible passent aussi en reviewed
            Report::where('reportable_type', $report->reportable_type)
                ->where('reportable_id', $report->reportable_id)
                ->where('id', '!=', $report->id)
                ->where('status', Report::STATUS_PENDING)
                ->update([
                    'status'      => Report::STATUS_REVIEWED,
                    'reviewed_by' => $adminId,
                    'reviewed_at' => now(),
                    'admin_note'  => $autoNote . ' (via signalement #' . $report->id . ')',
                ]);
        }

        $report->update([
            'status'      => $validated['status'],
            'admin_note'  => $validated['admin_note'] ?? $autoNote,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Signalement traité.',
            'report'  => $report->fresh(['reviewer:id,username']),
        ]);
    }  

    private function actOnTarget(Report $report, $target, int $adminId): string
    {
        if ($target instanceof \App\Models\Project) {
            if ($target->status !== \App\Models\Project::STATUS_PUBLISHED) {
                throw new \RuntimeException('Ce projet n\'est pas/plus publié — utilisez "Rejeter" si non actionnable.');
            }

            $action = \App\Models\ModerationAction::create([
                'project_id'       => $target->id,
                'user_id_targeted' => $target->user_id,
                'admin_id'         => $adminId,
                'action'           => \App\Models\ModerationAction::ACTION_ARCHIVE,
                'reason_code'      => $report->reason_code,
                'reason_text'      => $report->reason_text ?? 'Suite à signalement utilisateur.',
            ]);

            $target->update(['status' => \App\Models\Project::STATUS_ARCHIVED]);
            $target->loadMissing('user');
            \Illuminate\Support\Facades\Mail::to($target->user->email)
                ->send(new \App\Mail\ProjectModerated($target, $target->user, $action));

            return 'Projet archivé suite au signalement.';
        }

        if ($target instanceof \App\Models\Comment) {
            $target->delete();
            return 'Commentaire supprimé suite au signalement.';
        }

        if ($target instanceof \App\Models\GameTemplate) {
            $target->update(['status' => 'archived']);
            return 'Modèle archivé suite au signalement.';
        }

        return 'Action effectuée.';
    }

    private function fakeTargetFor(Report $report): object
    {
        return new class($report->reportable_type, $report->reportable_id) {
            public function __construct(public string $type, public int $id) {}
        };
    }
}