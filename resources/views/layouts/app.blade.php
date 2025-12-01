<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Encrypted Diary')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        @if(auth()->check())
        <nav class="navbar">
            <div class="navbar-brand">Encrypted Diary</div>
            <div class="navbar-menu">
                <span class="navbar-user">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-logout">Logout</button>
                </form>
            </div>
        </nav>
        @endif

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/crypto.js') }}"></script>
    @yield('scripts')
</body>
</html>
