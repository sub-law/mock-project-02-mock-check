@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧画面（管理者）')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-wrapper">

    <div class="attendance-header">
        <div class="attendance-line"></div>
        <h1 class="attendance-title">{{ $user->name }} さんの勤怠</h1>
    </div>

    <div class="attendance-navigation">
        {{-- 前月 --}}
        <a href="{{ route('admin.staff.attendance', [
                'id' => $user->id,
                'month' => $currentMonth->copy()->subMonth()->format('Y-m')
            ]) }}" class="nav-button nav-prev">
            <img src="{{ asset('images/arrow.png') }}" alt="前月" class="left-icon">
            前月
        </a>

        <div class="calendar-wrapper" id="calendarTrigger">
            <img src="{{ asset('images/calendar.png') }}" class="calendar-icon">
            <span class="calendar-label">{{ $currentMonth->format('Y/m') }}</span>

            <input type="month"
                id="monthPicker"
                class="month-picker-hidden">
        </div>

        {{-- 翌月 --}}
        <a href="{{ route('admin.staff.attendance', [
                'id' => $user->id,
                'month' => $currentMonth->copy()->addMonth()->format('Y-m')
            ]) }}" class="nav-button nav-next">

            <img src="{{ asset('images/arrow.png') }}" alt="翌月" class="right-icon">
            翌月
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="status-col">日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $day)
            @php
            $attendance = $attendances[$day->format('Y-m-d')] ?? null;
            @endphp

            <tr>
                {{-- 日付 --}}
                <td>{{ $day->locale('ja')->isoFormat('MM/DD(dd)') }}</td>

                {{-- 出勤 --}}
                <td>
                    {{ $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}
                </td>

                {{-- 退勤 --}}
                <td>
                    {{ $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}
                </td>

                {{-- 休憩 --}}
                <td>
                    @if ($attendance)
                    @php
                    $break = $attendance->getTotalBreakMinutes();
                    $breakH = floor($break / 60);
                    $breakM = $break % 60;
                    @endphp
                    {{ $breakH }}:{{ sprintf('%02d', $breakM) }}
                    @else

                    @endif
                </td>

                {{-- 合計 --}}
                <td>
                    @if ($attendance)
                    @php
                    $total = $attendance->getTotalWorkMinutes();
                    $totalH = floor($total / 60);
                    $totalM = $total % 60;
                    @endphp
                    {{ $totalH }}:{{ sprintf('%02d', $totalM) }}
                    @else

                    @endif
                </td>

                <td class="detail-cell">
                    <a href="{{ route('admin.attendance.detail', [
                            'id' => $attendance ? $attendance->id : 'new',
                            'date' => $day->format('Y-m-d')
                        ]) }}" class="detail-link">
                        詳細
                    </a>
                </td>

            </tr>
            @endforeach
        </tbody>

        <script>
            document.getElementById('calendarTrigger').addEventListener('click', function() {
                document.getElementById('monthPicker').showPicker();
            });

            document.getElementById('monthPicker').addEventListener('change', function() {
                const month = this.value;
                if (month) {
                    window.location.href = "{{ route('admin.staff.attendance', ['id' => $user->id]) }}?month=" + month;
                }
            });
        </script>

    </table>
</div>
@endsection