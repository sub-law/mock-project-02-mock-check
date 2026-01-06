@extends('layouts.admin')

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
            @php
            $breakMin = $attendance->getTotalBreakMinutes();
            $totalMin = $attendance->getTotalWorkMinutes();
            @endphp

            <tr>
                {{-- 名前 --}}
                <td>{{ optional($attendance->user)->name }}</td>

                {{-- 出勤 --}}
                <td>{{ $attendance->clock_in?->format('H:i') ?? '-' }}</td>

                {{-- 退勤 --}}
                <td>{{ $attendance->clock_out?->format('H:i') ?? '-' }}</td>

                {{-- 休憩 --}}
                <td>
                    @if ($breakMin > 0)
                    {{ floor($breakMin / 60) }}:{{ sprintf('%02d', $breakMin % 60) }}
                    @else
                    -
                    @endif
                </td>

                {{-- 合計 --}}
                <td>
                    @if ($totalMin > 0)
                    {{ floor($totalMin / 60) }}:{{ sprintf('%02d', $totalMin % 60) }}
                    @else
                    -
                    @endif
                </td>

                {{-- 詳細 --}}
                <td class="detail-cell">
                    <a href="{{ route('admin.attendance.detail', [
                            'id' => $attendance->id,
                            'user_id' => $attendance->user_id,
                            'date' => $attendance->date->format('Y-m-d')
                        ]) }}" class="detail-link">
                        詳細
                    </a>
                </td>
            </tr>

            @empty
            <tr>
                <td colspan="6" class="no-data">データがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        document.getElementById('calendarTrigger').addEventListener('click', () => {
            document.getElementById('datePicker').showPicker();
        });

        document.getElementById('datePicker').addEventListener('change', function() {
            const date = this.value;
            const baseUrl = "{{ url('/admin/attendance/list') }}";
            window.location.href = baseUrl + "?date=" + date;
        });
    </script>

</div>
@endsection