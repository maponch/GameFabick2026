@extends('layouts.app')

@section('title', 'Connexion GameFabrick')

@section('content')
    <h1>Créer un compte</h1>

    @if(session('success'))
        <p style="color: green">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <div>
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" value="{{ old('username') }}">
            @error('username')
                <span style="color: red">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email')
                <span style="color: red">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Mot de passe</label>
            <input type="password" name="password">
            @error('password')
                <span style="color: red">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation">
        </div>

        <button type="submit">S'inscrire</button>
    </form>
@endsection
