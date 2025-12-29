@extends('layouts.app')

@section('title', '勤怠一覧画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-wrapper">

    <div class="attendance-header">
        <div class="attendance-line"></div>
        <h1 class="attendance-title">{{ $date->format('Y年m月d日') }}の勤怠</h1>
    </div>

    <div class="attendance-navigation">

        {{-- 前日 --}}
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->toDateString()]) }}"
            class="nav-button nav-prev">
            <img src="{{ asset('images/arrow.png') }}" class="left-icon">
            前日
        </a>

        {{-- カレンダー --}}
        <div class="calendar-wrapper" id="calendarTrigger">
            <img src="{{ asset('images/calendar.png') }}" class="calendar-icon">
            <span class="calendar-label">{{ $date->format('Y年m月d日') }}</span>

            <input type="date"
                id="datePicker"
                class="date-picker-hidden"
                value="{{ $date->toDateString() }}">
        </div>

        {{-- 翌日 --}}
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->toDateString()]) }}"
            class="nav-button nav-next">
            <img src="{{ asset('images/arrow.png') }}" class="right-icon">
            翌日
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="status-col">名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>

                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>

                <td>{{ $attendance->breaks->count() > 0 ? '1:00' : '-' }}</td>

                <td>{{ $attendance->clock_in && $attendance->clock_out ? '8:00' : '-' }}</td>

                <td class="detail-cell">
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="no-data">データがありません</td>
            </tr>
            @endforelse
        </tbody>

        <script>
            // カレンダーアイコン＋日付ラベルをクリックしたら datePicker を開く
            document.getElementById('calendarTrigger').addEventListener('click', () => {
                document.getElementById('datePicker').showPicker();
            });

            // 日付を選んだらその日の勤怠一覧へ遷移
            document.getElementById('datePicker').addEventListener('change', function() {
                const date = this.value;
                const baseUrl = "{{ url('/admin/attendance/list') }}";
                window.location.href = baseUrl + "?date=" + date;
            });
        </script>

    </table>
</div>
@endsection