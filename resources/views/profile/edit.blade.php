@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<h2>Modifier mon profil</h2>

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PUT')

    <label for="username">Nom d'utilisateur</label>
    <input
        type="text"
        id="username"
        name="username"
        placeholder="{{ old('username', auth()->user()->username) }}"
    >
    @error('username')
        <span class="text-danger">{{ $message }}</span>
    @enderror

    <label for="email">Email (facultatif)</label>
    <input
        type="email"
        id="email"
        name="email"
        placeholder="{{ old('email', auth()->user()->email) }}"
    >
    @error('email')
        <span class="text-danger">{{ $message }}</span>
    @enderror

    <label for="current_password">Mot de passe actuel</label>
    <input
        type="password"
        name="current_password"
        id="current_password"
        placeholder="Confirmez votre mot de passe"
        required
    >
    @error('current_password')
        <span class="text-danger">{{ $message }}</span>
    @enderror

    <button type="submit">Mettre à jour le profil</button>
</form>

<hr>

<h2>Changer mon mot de passe</h2>

<form method="POST" action="{{ route('profile.password.update') }}">
    @csrf
    @method('PUT')

    <label>Mot de passe actuel</label>
    <input type="password" name="current_password" required>

    <label>Nouveau mot de passe</label>
    <input type="password" name="password" required>

    <label>Confirmer le nouveau mot de passe</label>
    <input type="password" name="password_confirmation" required>

    <button type="submit">Changer le mot de passe</button>
</form>
@endsection
