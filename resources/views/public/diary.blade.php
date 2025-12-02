<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $diary->title }} - {{ $user->name }}의 일기</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .diary-article {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .diary-article header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .diary-article h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .diary-meta {
            display: flex;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        .diary-meta a {
            color: #4a90d9;
            text-decoration: none;
        }
        .diary-meta a:hover {
            text-decoration: underline;
        }
        .diary-content {
            line-height: 1.8;
            color: #444;
            font-size: 1.1rem;
            white-space: pre-wrap;
        }
        .back-link {
            display: inline-block;
            margin-top: 3rem;
            color: #666;
            text-decoration: none;
        }
        .back-link:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <article class="diary-article">
            <header>
                <h1>{{ $diary->title }}</h1>
                <div class="diary-meta">
                    <span>{{ $diary->created_at->format('Y년 m월 d일') }}</span>
                    <span>by <a href="{{ route('public.profile', $user->username) }}">{{ $user->name }}</a></span>
                </div>
            </header>

            <div class="diary-content">{{ $diary->body_ciphertext }}</div>

            <a href="{{ route('public.profile', $user->username) }}" class="back-link">← {{ $user->name }}의 일기 목록</a>
        </article>
    </div>
</body>
</html>
