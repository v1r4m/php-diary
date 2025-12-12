<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }}의 일기 - Diary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fafafa;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 2rem 0;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        .header .username {
            color: #888;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Calendar */
        .calendar {
            flex: 1;
            padding: 1rem 0;
        }
        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom: 0.5rem;
        }
        .weekday {
            text-align: center;
            font-size: 0.7rem;
            color: #999;
            padding: 8px 0;
        }
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }
        .day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .day:hover {
            background: rgba(0, 0, 0, 0.05);
        }
        .day.empty {
            cursor: default;
        }
        .day.empty:hover {
            background: transparent;
        }
        .day .emoji {
            font-size: 1.5rem;
            line-height: 1;
        }
        .day .placeholder {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.08);
        }
        .day .date {
            font-size: 0.65rem;
            color: #888;
            margin-top: 4px;
        }
        .day.has-diary {
            cursor: pointer;
        }
        .day.has-diary:hover {
            background: rgba(0, 0, 0, 0.08);
            transform: scale(1.05);
        }
        .day.today .date {
            color: #4a90d9;
            font-weight: 600;
        }

        /* Navigation */
        .nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            padding: 1rem 0;
        }
        .nav button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s;
        }
        .nav button:hover {
            background: rgba(0, 0, 0, 0.08);
        }
        .nav button svg {
            width: 24px;
            height: 24px;
            color: #333;
        }
        .nav .month-label {
            font-size: 1rem;
            color: #333;
            min-width: 140px;
            text-align: center;
        }

        /* Bottom Sheet Overlay */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* Bottom Sheet */
        .bottom-sheet {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 16px 16px 0 0;
            z-index: 101;
            max-height: 80vh;
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
            pointer-events: none;
        }
        .bottom-sheet.active {
            transform: translateY(0);
            pointer-events: auto;
        }
        .sheet-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .sheet-header h2 {
            font-size: 1.5rem;
        }
        .sheet-header .date {
            font-size: 0.85rem;
            color: #888;
        }
        .sheet-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #888;
            padding: 4px;
        }
        .sheet-content {
            padding: 1.5rem;
            overflow-y: auto;
            max-height: calc(80vh - 80px);
            line-height: 1.8;
            color: #444;
            white-space: pre-wrap;
        }

        /* Back link */
        .back-link {
            text-align: center;
            padding: 1rem;
        }
        .back-link a {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link a:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>{{ $user->name }}의 일기</h1>
            <p class="username">@{{ $user->username }}</p>
        </header>

        <div class="calendar">
            <div class="weekdays">
                @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                    <div class="weekday">{{ $day }}</div>
                @endforeach
            </div>
            <div class="days">
                @php
                    $firstDay = \Carbon\Carbon::create($year, $month, 1);
                    $daysInMonth = $firstDay->daysInMonth;
                    $startDayOfWeek = $firstDay->dayOfWeek;
                    $today = now()->format('Y-m-d');
                @endphp

                {{-- Empty days before month starts --}}
                @for($i = 0; $i < $startDayOfWeek; $i++)
                    <div class="day empty"></div>
                @endfor

                {{-- Days of month --}}
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                        $diary = $diaries->get($date);
                        $isToday = $date === $today;
                    @endphp

                    @if($diary)
                        <div class="day has-diary {{ $isToday ? 'today' : '' }}"
                             data-title="{{ $diary->title }}"
                             data-date="{{ $diary->created_at->format('Y년 m월 d일') }}"
                             data-content="{{ $diary->body_ciphertext }}">
                            <span class="emoji">{{ $diary->title }}</span>
                            <span class="date">{{ $day }}</span>
                        </div>
                    @else
                        <div class="day {{ $isToday ? 'today' : '' }}">
                            <div class="placeholder"></div>
                            <span class="date">{{ $day }}</span>
                        </div>
                    @endif
                @endfor
            </div>
        </div>

        <nav class="nav">
            @php
                $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
                $nextMonth = \Carbon\Carbon::create($year, $month, 1)->addMonth();
            @endphp
            <a href="?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}">
                <button type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.72 12.53a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 1 1 1.06 1.06L9.31 12l6.97 6.97a.75.75 0 1 1-1.06 1.06l-7.5-7.5Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </a>
            <span class="month-label">{{ \Carbon\Carbon::create($year, $month, 1)->format('Y년 n월') }}</span>
            <a href="?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}">
                <button type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </a>
        </nav>

        <div class="back-link">
            <a href="/">← 홈으로</a>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="bottom-sheet" id="bottomSheet">
        <div class="sheet-header">
            <div>
                <h2 id="sheetTitle"></h2>
                <p class="date" id="sheetDate"></p>
            </div>
            <button class="sheet-close" id="sheetClose">×</button>
        </div>
        <div class="sheet-content" id="sheetContent"></div>
    </div>

    <script src="{{ asset('js/calendar.js') }}"></script>
</body>
</html>
