<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeletionMail;
use App\Mail\RecoveryCodesMail;
use App\Mail\TwoFactorDisabledMail;
use App\Models\Suspension;
use App\Models\SuspensionHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_users'   => User::count(),
            'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'admins'        => User::where('role', 'admin')->count(),
            'suspended'     => Suspension::where('is_active', true)->count(),
        ]);
    }

    public function users()
    {
        return response()->json(
            User::withTrashed()
                ->select('id', 'username', 'email', 'role', 'created_at', 'profile_photo', 'scheduled_deletion_at', 'deleted_at')
                ->with(['suspensionHistory', 'suspensions' => fn($q) => $q->where('is_active', true)->latest()])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($u) => [
                    'id'               => $u->id,
                    'username'         => $u->username,
                    'email'            => $u->email,
                    'role'             => $u->role,
                    'created_at'       => $u->created_at,
                    'photo_profile_url' => $u->photo_profile_url,
                    'is_suspended'     => $u->suspensions->isNotEmpty(),
                    'suspension_count' => $u->suspensionHistory?->suspension_count ?? 0,
                    'is_permanently_banned' => $u->suspensionHistory?->is_permanently_banned ?? false,
                    'is_pending_deletion'   => !is_null($u->scheduled_deletion_at),
                    'scheduled_deletion_at' => $u->scheduled_deletion_at?->format('d/m/Y'),
                ])
        );
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas modifier votre propre rôle.'], 403);
        }

        $user->update(['role' => $request->role]);

        return response()->json(['user' => $user->fresh()]);
    }

    public function suspend(Request $request, User $user)
    {
        $request->validate([
            'reason'   => 'required|string|max:500',
            'duration' => 'required|in:1_day,7_days,1_month,permanent',
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous suspendre vous-même.'], 403);
        }

        // Calcul automatique de expires_at
        $expiresAt = match($request->duration) {
            '1_day'   => now()->addDay(),
            '7_days'  => now()->addDays(7),
            '1_month' => now()->addMonth(),
            'permanent' => null,
        };

        // Désactive les suspensions actives précédentes
        Suspension::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        Suspension::create([
            'user_id'    => $user->id,
            'admin_id'   => $request->user()->id,
            'reason'     => $request->reason,
            'expires_at' => $expiresAt,
            'is_active'  => true,
        ]);

        // Met à jour l'historique
        $emailHash = hash('sha256', strtolower($user->email));
        $history = SuspensionHistory::firstOrCreate(
            ['email_hash' => $emailHash],
            ['user_id' => $user->id, 'suspension_count' => 0]
        );

        $history->increment('suspension_count');

        if ($request->duration === 'permanent') {
            $history->update(['is_permanently_banned' => true]);
        }

        return response()->json(['message' => 'Utilisateur suspendu.']);
    }

    public function unsuspend(User $user)
    {
        Suspension::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return response()->json(['message' => 'Suspension levée.']);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $deletionDate = now()->addDays(30)->format('d/m/Y')  ;

        // Conserve le hash si banni
        $emailHash = hash('sha256', strtolower($user->email));
        $history = SuspensionHistory::where('email_hash', $emailHash)->first();
        if ($history) {
            $history->update(['user_id' => null]);
        }

        Mail::to($user->email)->send(new AccountDeletionMail($user->username, $deletionDate, $user->email, 'admin'));

        $user->scheduleDeletion('admin');

        return response()->json(['message' => 'Suppression programmée. L\'utilisateur a 30 jours pour annuler.']);
    }
    public function show($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->load(['suspensions.admin', 'suspensionHistory']);

        return response()->json([
            'id'               => $user->id,
            'username'         => $user->username,
            'email'            => $user->email,
            'role'             => $user->role,
            'created_at'       => $user->created_at,
            'email_verified_at' => $user->email_verified_at,
            'photo_profile_url' => $user->photo_profile_url,
            'is_suspended'     => $user->isSuspended(),
            'is_permanently_banned' => $user->suspensionHistory?->is_permanently_banned ?? false,
            'suspension_count' => $user->suspensionHistory?->suspension_count ?? 0,
            'is_pending_deletion'   => !is_null($user->scheduled_deletion_at),
            'scheduled_deletion_at' => $user->scheduled_deletion_at?->format('d/m/Y'),
            'suspensions'      => $user->suspensions->map(fn($s) => [
                'id'         => $s->id,
                'reason'     => $s->reason,
                'expires_at' => $s->expires_at?->format('d/m/Y à H:i'),
                'is_active'  => $s->is_active,
                'created_at' => $s->created_at->format('d/m/Y à H:i'),
                'admin'      => $s->admin?->username ?? 'Système',
            ]),
            'two_factor_enabled' => $user->two_factor_enabled,
            'two_factor_method'  => $user->two_factor_method,
        ]);
    }
    public function disableTwoFactor(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->update([
            'two_factor_method'         => null,
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_recovery_codes' => null,
        ]);

        // Notification à l'utilisateur
        Mail::to($user->email)->send(new TwoFactorDisabledMail(
            $user->username,
            $request->reason,
            $request->user()->username
        ));

        return response()->json(['message' => '2FA désactivée et utilisateur notifié.']);
    }
    public function regenerateRecoveryCodes(Request $request, User $user)
    {
        if (!$user->two_factor_enabled) {
            return response()->json(['message' => 'Cet utilisateur n\'a pas activé le 2FA.'], 400);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn() => strtoupper(bin2hex(random_bytes(5))))
            ->toArray();

        $user->update(['two_factor_recovery_codes' => $recoveryCodes]);

        Mail::to($user->email)->send(new RecoveryCodesMail(
            $user->username,
            $recoveryCodes,
            $request->user()->username
        ));

        return response()->json([
            'message'        => 'Codes de secours régénéréset envoyés par email à l\'utilisateur.',
            'recovery_codes' => $recoveryCodes,
        ]);
    }
    public function cancelDeletion(Request $request, User $user)
    {
        if (!$user->scheduled_deletion_at) {
            return response()->json(['message' => 'Aucune suppression en cours.'], 400);
        }

        $user->restore(); // si soft deleted
        $user->update([
            'scheduled_deletion_at' => null,
            'deletion_initiator'    => null,
        ]);

        return response()->json(['message' => 'Suppression annulée.', 'user' => $user->fresh()]);
    }
}