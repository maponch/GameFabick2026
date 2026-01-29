@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Tableau de bord de {{ auth()->user()->username }}</h1>
        <a href="{{ route('profile.edit') }}" class="profile-icon" title="Modifier mon profil">
          <i class="fa-solid fa-user-gear"></i>
          ⚙️
        </a>

    <p><strong>Nom d'utilisateur :</strong> {{ $user->username }}</p>
    <p><strong>Email :</strong> {{ $user->email }}</p>
    <p><strong>Rôle :</strong> {{ $user->role }}</p>

    <hr>

    <p>Bienvenue sur ton espace personnel GameFabrick 🎲</p>
@endsection
