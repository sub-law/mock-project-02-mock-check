<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'COACHTECH')</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    @yield('styles')
</head>

<body>
    <header>
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECHロゴ">

        <nav class="nav-menu user-nav">

            @if(Auth::guard('web')->check())

            @php
            $isClockedOut = Auth::user()->isClockedOutToday();
            @endphp

            <ul>
                @if(!$isClockedOut)
                <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('stamp.correction.request.list') }}">申請</a></li>
                @else
                <li><a href="{{ route('attendance.list') }}">今月の出勤一覧</a></li>
                <li><a href="{{ route('stamp.correction.request.list') }}">申請一覧</a></li>
                @endif

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">ログアウト</button>
                    </form>
                </li>
            </ul>

            @endif

        </nav>

    </header>

    @yield('content')
</body>

</html>