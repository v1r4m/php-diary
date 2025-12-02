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
            <div class="navbar-brand">
                <a href="{{ route('diary.index') }}">Encrypted Diary</a>
            </div>
            <div class="navbar-menu">
                <a href="{{ route('diary.index') }}" class="navbar-link">내 일기</a>
                <a href="{{ route('settings.index') }}" class="navbar-link">설정</a>
                <span class="navbar-user">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="logout-form" id="logout-form">
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
    <script>
    // Clear encryption keys on logout
    document.addEventListener('DOMContentLoaded', function() {
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (typeof DiaryEncryption !== 'undefined') {
                    await DiaryEncryption.clearAll();
                    DiaryEncryption.clearDiaryToken();
                }
                logoutForm.submit();
            });
        }
    });
    </script>
    @yield('scripts')
</body>
</html>
