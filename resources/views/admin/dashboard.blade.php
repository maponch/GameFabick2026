@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    <h2>Bienvenue {{ auth()->user()->username }}</h2>

    <p>Vous êtes connecté en tant qu’administrateur.</p>

    <ul>
        <li>Utilisateurs</li>
        <li>Statistiques</li>
        <li>Contenus</li>
    </ul>
@endsection
