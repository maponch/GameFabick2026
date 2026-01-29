@extends('layouts.app')

@section('title', 'Connexion GameFabrick')

@section('content')
    <h1>Connexion</h1>

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span style="color:red">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Mot de passe</label>
            <input type="password" name="password" required>
            @error('password')
                <span style="color:red">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">Se connecter</button>
    </form>

    <p>
        Pas encore de compte ? <a href="{{ route('register.form') }}">S'inscrire</a>
    </p>
@endsection
