<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - @yield('title', 'Dashboard')</title>
</head>
<body>

    <header>
        <h1>🛠️ Administration</h1>

        <nav>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('dashboard') }}">Retour site</a>

            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Déconnexion</button>
            </form>
        </nav>

        <hr>
    </header>

    <main>
        @yield('content')
    </main>

</body>
</html>
