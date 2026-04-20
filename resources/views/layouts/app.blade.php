<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'GameFabrick')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <header>
        <nav>
            @if(Auth::check())
                <a href="{{ url('/dashboard') }}">Tableau de bord</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit">Déconnexion</button>
                </form>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <li>
                        <a href="{{ route('admin.dashboard') }}">Administration</a>
                    </li>
                @endif
            @else
                <a href="{{ route('login.form') }}">Connexion</a>
                <a href="{{ route('register.form') }}">Inscription</a>
            @endif
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} GameFabrick</p>
    </footer>
</body>
</html>
<script>
    window.user = @json(Auth::user());
</script>
