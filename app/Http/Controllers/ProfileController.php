<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validation basique des champs
        $validated = $request->validate([
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id)
            ],
            'email' => [
                'nullable',
                'email:rfc,dns',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'current_password' => 'required',
        ]);

        // Vérifier que le mot de passe actuel est correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        // Vérifier qu'au moins un champ est renseigné
        if (empty($validated['username']) && empty($validated['email'])) {
            throw ValidationException::withMessages([
                'username' => 'Vous devez modifier au moins un champ (username ou email).',
                'email' => 'Vous devez modifier au moins un champ (username ou email).',
            ]);
        }

        // Mise à jour des champs fournis
        if (!empty($validated['username'])) {
            $user->username = $validated['username'];
        }

        if (!empty($validated['email'])) {
            $user->email = $validated['email'];
        }

        $user->save();

        return redirect()->route('dashboard')->with('success', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'confirmed',
                Password::min(10)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard')->with('success', 'Mot de passe mis à jour');
    }

}