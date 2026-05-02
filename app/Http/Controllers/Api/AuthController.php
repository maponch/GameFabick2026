<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        $user = Auth::user();

        // ✅ Vérifie si le compte est suspendu
        $suspension = $user->activeSuspension();
        if ($suspension) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => [
                    $suspension->expires_at
                        ? 'Compte suspendu jusqu\'au ' . $suspension->expires_at->format('d/m/Y à H:i') . '. Raison : ' . $suspension->reason
                        : 'Compte banni définitivement. Raison : ' . $suspension->reason
                ],
            ]);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $user]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnecté']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username'      => 'required|string|max:255|unique:users,username',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        // ✅ Vérifie si l'email est banni définitivement
        $emailHash = hash('sha256', strtolower($data['email']));
        $history = \App\Models\SuspensionHistory::where('email_hash', $emailHash)->first();

        if ($history?->is_permanently_banned) {
            throw ValidationException::withMessages([
                'email' => ['Cette adresse email est bannie définitivement.'],
            ]);
        }

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')
                ->store('profile_photos', 'public');
        }

        $user = User::create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password'      => $data['password'],
            'profile_photo' => $data['profile_photo'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user], 201);
    }
}