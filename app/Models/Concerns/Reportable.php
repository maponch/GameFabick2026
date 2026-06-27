<?php

namespace App\Models\Concerns;

use App\Models\Report;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Reportable
{
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function pendingReportsCount(): int
    {
        return $this->reports()
            ->where('status', Report::STATUS_PENDING)
            ->count();
    }
}