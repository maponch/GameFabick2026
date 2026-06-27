<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = Report::create([
            'reporter_id'     => $request->user()->id,
            'reportable_type' => $request->resolvedReportableClass(),
            'reportable_id'   => (int) $request->input('reportable_id'),
            'reason_code'     => $request->input('reason_code'),
            'reason_text'     => $request->input('reason_text'),
            'status'          => Report::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Signalement enregistré. Merci, un administrateur examinera votre demande.',
            'report'  => [
                'id'         => $report->id,
                'status'     => $report->status,
                'created_at' => $report->created_at,
            ],
        ], 201);
    }
}