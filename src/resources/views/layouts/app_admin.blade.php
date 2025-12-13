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
            <ul>
                {{-- <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li> --}}
                <li><a href="{{ url('/admin/attendance/list') }}">勤怠一覧</a></li>

                {{-- <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li> --}}
                <li><a href="{{ url('/admin/staff/list') }}">スタッフ一覧</a></li>

                {{-- <li><a href="{{ route('stamp.correction.request.admin') }}">申請一覧</a></li> --}}
                <li><a href="{{ url('/admin/request/list') }}">申請一覧</a></li>

                {{-- <li><a href="{{ route('logout') }}">ログアウト</a></li> --}}
                <li><a href="{{ url('/logout') }}">ログアウト</a></li>
            </ul>
        </nav>
    </header>


    @yield('content')
</body>

</html>