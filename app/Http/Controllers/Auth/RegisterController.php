<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email'    => 'required|email:rfc,dns|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',              // minimum 8 caractères
                'confirmed',          // doit matcher password_confirmation
                'regex:/[a-z]/',      // au moins une lettre minuscule
                'regex:/[A-Z]/',      // au moins une lettre majuscule
                'regex:/[0-9]/',      // au moins un chiffre
                'regex:/[@$!%*#?&]/', // au moins un caractère spécial
            ],
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password, // hashé automatiquement dans le modèle
            'role'     => 'user', // par défaut
        ]);

        // Authentification immédiate
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Bienvenue ! Votre compte a été créé.');
    }
}
