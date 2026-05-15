<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function updateUsername(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $request->user()->id,
        ]);

        $request->user()->update(['username' => $data['username']]);

        return response()->json(['user' => $request->user()->fresh()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',     
                'regex:/[0-9]/',     
                'regex:/[^A-Za-z0-9]/', 
            ],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $request->user()->update(['password' => $request->password]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->profile_photo) {
            \Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        $user->update(['profile_photo' => $path]);

        return response()->json(['user' => $user->fresh()]);
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        // Vérifie le mot de passe avant suppression
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['password' => ['Mot de passe incorrect.']]], 422);
        }

        $deletionDate = now()->addDays(30)->format('d/m/Y');

        $user->scheduleDeletion('self');

        Mail::to($user->email)->send(new AccountDeletionMail($user->username, $deletionDate, $user->email, 'self'));

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Compte supprimé. Vous avez 30 jours pour annuler.']);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->first();


        if (!$user || !$user->trashed()) {
            return response()->json(['message' => 'Aucun compte supprimé trouvé.'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['password' => ['Mot de passe incorrect.']]], 422);
        }

        if ($user->deletion_initiator === 'admin') {
            return response()->json([
                'message' => 'Cette suppression a été effectuée par un administrateur et ne peut être annulée. Contactez le support.'
            ], 403);
        }

        if ($user->scheduled_deletion_at && now()->isAfter($user->scheduled_deletion_at)) {
            return response()->json(['message' => 'Délai de restauration dépassé.'], 403);
        }

        $user->restoreAccount();

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['message' => 'Compte restauré.', 'user' => $user->fresh()]);
    }
}