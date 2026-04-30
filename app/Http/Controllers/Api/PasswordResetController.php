<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    // Étape 1 : envoie l'OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Supprime les anciens OTP pour cet email
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_otps')->insert([
            'email'      => $request->email,
            'otp'        => Hash::make($otp), // ✅ hashé en base
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'Code envoyé par email.']);
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|string|size:6',
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->latest()
            ->first();

        if (!$record || !Hash::check($request->otp, $record->otp)) { // ✅ Hash::check
            return response()->json(['errors' => ['otp' => ['Code invalide.']]], 422);
        }

        if (now()->isAfter($record->expires_at)) {
            return response()->json(['errors' => ['otp' => ['Code expiré. Veuillez en demander un nouveau.']]], 422);
        }

        return response()->json(['message' => 'Code valide.']);
    }

    // Étape 2 : vérifie l'OTP et réinitialise le mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'otp'                   => 'required|string|size:6',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->latest()
            ->first();

        // Vérifie existence, validité et expiration
        if (!$record || !Hash::check($request->otp, $record->otp)) {
            return response()->json(['errors' => ['otp' => ['Code invalide.']]], 422);
        }

        if (now()->isAfter($record->expires_at)) {
            return response()->json(['errors' => ['otp' => ['Code expiré. Veuillez en demander un nouveau.']]], 422);
        }

        // Met à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = $request->password;
        $user->save();

        // Supprime l'OTP utilisé
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}