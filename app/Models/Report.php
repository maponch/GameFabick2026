<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_REVIEWED  = 'reviewed';
    public const STATUS_DISMISSED = 'dismissed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_REVIEWED,
        self::STATUS_DISMISSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING   => 'En attente',
        self::STATUS_REVIEWED  => 'Traité',
        self::STATUS_DISMISSED => 'Rejeté',
    ];

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason_code',
        'reason_text',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reasonLabel(): string
    {
        return ModerationAction::REASON_LABELS[$this->reason_code] ?? $this->reason_code;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
    public static function markPendingAsReviewedFor($reportable, int $adminId, string $autoNote): int
    {
        return static::where('reportable_type', get_class($reportable))
            ->where('reportable_id', $reportable->id)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status'      => self::STATUS_REVIEWED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'admin_note'  => $autoNote,
            ]);
    }
}