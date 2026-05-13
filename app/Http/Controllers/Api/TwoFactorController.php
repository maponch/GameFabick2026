<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    // Étape 1 : génère le secret TOTP et retourne le QR code
    public function generateTotp(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        // Stocke temporairement le secret (pas encore activé)
        $user->update([
            'two_factor_secret'  => $secret,
            'two_factor_enabled' => false, // pas encore confirmé
        ]);

        // Génère le QR code en SVG
        $qrUrl = $google2fa->getQRCodeUrl(
            'GameFabrick',
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($qrUrl);

        return response()->json([
            'qr_code' => $qrCode,
            'secret'  => $secret, // affiché si scan impossible
        ]);
    }

    // Envoie un code OTP par email
    public function sendEmailOtp(Request $request)
    {
        $user = $request->user();

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

        return response()->json(['message' => 'Code envoyé par email.']);
    }

    // Étape 2 : active le 2FA après vérification
    public function enable(Request $request)
    {
        $request->validate([
            'method' => 'required|in:totp,email',
            'code'   => 'required|string|size:6',
        ]);

        $user = $request->user();
        $valid = false;

        if ($request->method === 'totp') {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);
        } else {
            $record = DB::table('email_verification_otps')
                ->where('email', $user->email)
                ->latest()
                ->first();

            if ($record && Hash::check($request->code, $record->otp) && now()->isBefore($record->expires_at)) {
                $valid = true;
                DB::table('email_verification_otps')->where('email', $user->email)->delete();
            }
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide.'],
            ]);
        }

        // Génère 8 codes de secours
        $recoveryCodes = collect(range(1, 8))
            ->map(fn() => strtoupper(bin2hex(random_bytes(5))))
            ->toArray();

        $user->update([
            'two_factor_method'         => $request->method,
            'two_factor_enabled'        => true,
            'two_factor_recovery_codes' => $recoveryCodes,
            // Pour email, on n'a pas besoin du secret
            'two_factor_secret'         => $request->method === 'totp' ? $user->two_factor_secret : null,
        ]);

        return response()->json([
            'message'        => '2FA activé avec succès.',
            'recovery_codes' => $recoveryCodes, // ✅ affiché UNE SEULE FOIS
        ]);
    }

    // Désactivation du 2FA
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe incorrect.'],
            ]);
        }

        // Bloque la désactivation pour les admins
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Les administrateurs ne peuvent pas désactiver le 2FA.'
            ], 403);
        }

        $user->update([
            'two_factor_method'         => null,
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_recovery_codes' => null,
        ]);

        return response()->json(['message' => '2FA désactivé.']);
    }

    // Vérification du code 2FA au login (route publique)
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !$user->two_factor_enabled) {
            throw ValidationException::withMessages([
                'code' => ['Erreur de vérification.'],
            ]);
        }

        $valid = false;
        $usedRecoveryCode = false;

        // Vérifie d'abord si c'est un code de secours
        if (in_array(strtoupper($request->code), $user->two_factor_recovery_codes ?? [])) {
            $valid = true;
            $usedRecoveryCode = true;
            // Retire le code utilisé
            $user->update([
                'two_factor_recovery_codes' => array_values(array_diff(
                    $user->two_factor_recovery_codes,
                    [strtoupper($request->code)]
                ))
            ]);

        } elseif ($user->two_factor_method === 'totp') {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);
        } elseif ($user->two_factor_method === 'email') {
            $record = DB::table('email_verification_otps')
                ->where('email', $user->email)
                ->latest()
                ->first();

            if ($record && Hash::check($request->code, $record->otp) && now()->isBefore($record->expires_at)) {
                $valid = true;
                DB::table('email_verification_otps')->where('email', $user->email)->delete();
            }
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide.'],
            ]);
        }

        // Connecte l'utilisateur
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
            'used_recovery_code' => $usedRecoveryCode,
            'remaining_recovery_codes' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }
}