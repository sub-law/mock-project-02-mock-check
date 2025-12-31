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

        <nav class="admin-nav">

            {{-- 管理者ログイン時 --}}
            @if(Auth::guard('admin')->check())
            <ul>
                <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                <li><a href="{{ url('/stamp_correction_request_list') }}">申請一覧</a></li>
                {{--<li><a href="{{ route('stamp.correction.request.list') }}">申請一覧</a></li>--}}

                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit">ログアウト</button>
                    </form>
                </li>
            </ul>

            {{-- 一般ユーザーログイン時 --}}
            @elseif(Auth::guard('web')->check())
            <ul>
                <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ url('/stamp_correction_request_list') }}">申請一覧</a></li>
                {{--<li><a href="{{ route('stamp.correction.request.list') }}">申請一覧</a></li>--}}

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