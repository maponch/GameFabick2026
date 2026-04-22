@extends('layouts.app')

@section('title', 'Créer un compte - GameFabrick')

@section('content')

<div class="auth-wrapper">

    <div class="auth-card">

        <h1 class="auth-title">Créer un compte</h1>

        <p class="auth-subtitle">
            Rejoins GameFabrick 🎲 et commence à créer tes jeux
        </p>

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" class="auth-form" enctype="multipart/form-data">
            @csrf

            <!-- USERNAME -->
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" value="{{ old('username') }}" required>
                @error('username')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

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

            <!-- CONFIRM PASSWORD -->
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required>
            </div>
            {{-- PHOTO --}}
            <div>
                <label class="file-upload">
                    📷 Ajouter une photo
                    <input type="file" name="photo">
                </label>
                <p id="fileName" class="file-name">Aucun fichier sélectionné</p>

                @error('photo')
                    <span style="color:red">{{ $message }}</span>
                @enderror
            </div>

            <!-- BUTTON -->
            <button type="submit" class="btn-primary">
                S'inscrire
            </button>

        </form>

        <p class="auth-footer">
            Déjà un compte ?
            <a href="{{ route('login.form') }}">Se connecter</a>
        </p>

    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('photoInput');
    const fileName = document.getElementById('fileName');

    if (!input) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        fileName.textContent = file ? file.name : 'Aucun fichier sélectionné 555';
    });
});
</script>

@endsection