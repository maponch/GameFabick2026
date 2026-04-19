@extends('layouts.app')

@section('title', 'Connexion GameFabrick')

@section('content')
<div class="auth-wrapper">

    <div class="auth-card">

        <h1 class="auth-title">Connexion</h1>

        <p class="auth-subtitle">
            Accède à ton espace GameFabrick 🎲
        </p>

        <form method="POST" action="{{ route('login.submit') }}" class="auth-form">
            @csrf

            <!-- EMAIL -->
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <!-- PASSWORD -->
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <!-- BUTTON -->
            <button type="submit" class="btn-primary">
                Se connecter
            </button>

        </form>

        <p class="auth-footer">
            Pas encore de compte ?
            <a href="{{ route('register.form') }}">S'inscrire</a>
        </p>

    </div>

</div>

@endsection
