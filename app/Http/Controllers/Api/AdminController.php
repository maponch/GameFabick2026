<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Suspension;
use App\Models\SuspensionHistory;
use Illuminate\Http\Request;

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
            User::select('id', 'username', 'email', 'role', 'created_at', 'profile_photo')
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
            'reason'     => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now',
            'permanent'  => 'boolean',
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous suspendre vous-même.'], 403);
        }

        // Désactive les suspensions actives précédentes
        Suspension::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Crée la nouvelle suspension
        Suspension::create([
            'user_id'    => $user->id,
            'admin_id'   => $request->user()->id,
            'reason'     => $request->reason,
            'expires_at' => $request->permanent ? null : $request->expires_at,
            'is_active'  => true,
        ]);

        // Met à jour l'historique
        $emailHash = hash('sha256', strtolower($user->email));
        $history = SuspensionHistory::firstOrCreate(
            ['email_hash' => $emailHash],
            ['user_id' => $user->id, 'suspension_count' => 0]
        );

        $history->increment('suspension_count');

        // Ban permanent si 3 suspensions ou si permanent demandé
        if ($request->permanent || $history->suspension_count >= 3) {
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

        // Conserve le hash pour les bans permanents
        $emailHash = hash('sha256', strtolower($user->email));
        $history = SuspensionHistory::where('email_hash', $emailHash)->first();

        if ($history) {
            // Dissocie le user_id mais garde le hash
            $history->update(['user_id' => null]);
        }

        if ($user->profile_photo) {
            \Storage::disk('public')->delete($user->profile_photo);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}