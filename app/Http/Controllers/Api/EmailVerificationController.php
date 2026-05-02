<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    // Envoie le code OTP de vérification
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié.'], 400);
        }

        // Supprime les anciens OTP
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

        return response()->json(['message' => 'Code envoyé par email.']);
    }

    // Vérifie le code OTP
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié.'], 400);
        }

        $record = DB::table('email_verification_otps')
            ->where('email', $user->email)
            ->latest()
            ->first();

        if (!$record || !Hash::check($request->otp, $record->otp)) {
            return response()->json(['errors' => ['otp' => ['Code invalide.']]], 422);
        }

        if (now()->isAfter($record->expires_at)) {
            return response()->json(['errors' => ['otp' => ['Code expiré. Veuillez en demander un nouveau.']]], 422);
        }

        // Marque l'email comme vérifié
        $user->markEmailAsVerified();

        // Supprime l'OTP utilisé
        DB::table('email_verification_otps')->where('email', $user->email)->delete();

        return response()->json(['message' => 'Email vérifié avec succès.', 'user' => $user->fresh()]);
    }
}