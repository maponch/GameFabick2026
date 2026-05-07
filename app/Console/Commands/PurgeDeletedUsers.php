<?php

namespace App\Console\Commands;

use App\Models\SuspensionHistory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeDeletedUsers extends Command
{
    protected $signature   = 'users:purge';
    protected $description = 'Supprime définitivement les comptes après 30 jours';

    public function handle(): void
    {
        $users = User::onlyTrashed()
            ->where('scheduled_deletion_at', '<=', now())
            ->get();

        foreach ($users as $user) {
            // Conserve le hash si historique de suspension
            $emailHash = hash('sha256', strtolower($user->email));
            $history = SuspensionHistory::where('email_hash', $emailHash)->first();
            if ($history) {
                $history->update(['user_id' => null]);
            }

            // Supprime la photo
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Supprime définitivement
            $user->forceDelete();
        }

        $this->info("✅ {$users->count()} compte(s) purgé(s).");
    }
}