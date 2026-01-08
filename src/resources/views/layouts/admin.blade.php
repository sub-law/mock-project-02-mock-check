<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'COACHTECH 管理者')</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    @yield('styles')
</head>

<body>
    <header>
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECHロゴ">

        <nav class="nav-menu admin-nav">
            <ul>
                <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                <li><a href="{{ route('admin.correction.list') }}">申請一覧</a></li>

                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    @yield('content')
</body>

</html>