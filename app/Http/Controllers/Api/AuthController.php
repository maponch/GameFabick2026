<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Mail\TwoFactorMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        $throttleKey = 'login.' . strtolower($credentials['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            throw ValidationException::withMessages([
                'password' => ["Trop de tentatives. Réessayez dans {$minutes} minute(s)."],
            ]);
        }
        $deletedUser = User::withTrashed()
            ->where('email', $credentials['email'])
            ->whereNotNull('scheduled_deletion_at')
            ->first();

        if ($deletedUser && Hash::check($credentials['password'], $deletedUser->password)) {
            $deletionDate = $deletedUser->scheduled_deletion_at->format('d/m/Y');

            if ($deletedUser->deletion_initiator === 'self') {
                throw ValidationException::withMessages([
                    'email' => ["Votre compte est en cours de suppression jusqu'au {$deletionDate}. Vous pouvez le restaurer via le lien reçu par email."],
                ]);
            } else {
                throw ValidationException::withMessages([
                    'email' => ["Votre compte a été supprimé par un administrateur. Contactez le support pour toute contestation."],
                ]);
            }
        }

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, 300); // ✅ 300s = 5 min de blocage
            throw ValidationException::withMessages([
                'password' => ['mot de passe ou email invalide.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

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
        if (!$user->hasVerifiedEmail()) {
            DB::table('email_verification_otps')->where('email', $user->email)->delete();

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('email_verification_otps')->insert([
                'email'      => $user->email,
                'otp'        => Hash::make($otp),
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Mail::to($user->email)->send(new OtpMail($otp));
        }
        // Si 2FA activé, demande la vérification
        if ($user->two_factor_enabled) {
            Auth::guard('web')->logout();

            // Si méthode email, envoie automatiquement le code
            if ($user->two_factor_method === 'email') {
                DB::table('email_verification_otps')->where('email', $user->email)->delete();
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                DB::table('email_verification_otps')->insert([
                    'email'      => $user->email,
                    'otp'        => Hash::make($otp),
                    'expires_at' => now()->addMinutes(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Mail::to($user->email)->send(new TwoFactorMail($otp));
            }

            return response()->json([
                'two_factor_required' => true,
                'method'              => $user->two_factor_method,
                'email'               => $user->email,
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
            'cgu_accepted'  => 'required|accepted',
        ], [
            'cgu_accepted.accepted' => 'Vous devez accepter les CGU pour créer un compte.',
            'cgu_accepted.required' => 'Vous devez accepter les CGU pour créer un compte.',
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
            'cgu_accepted_at'      => now(),
            'cgu_version_accepted' => config('app.cgu_version'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        DB::table('email_verification_otps')->insert([
            'email'      => $user->email,
            'otp'        => Hash::make($otp),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json(['user' => $user], 201);
    }
}