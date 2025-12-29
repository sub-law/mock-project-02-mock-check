@extends('layouts.app')

@section('title', '勤怠登録画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

    @if (session('message'))
    <div class="flash-message">
        {{ session('message') }}
    </div>
    @endif

    {{-- ステータス表示 --}}
    <p class="attendance-status">
        {{ $attendance ? $attendance->getStatusLabel() : '勤務外' }}
    </p>

    {{-- 年月日表示 --}}
    <p class="attendance-date">{{ now()->format('Y年m月d日') }}</p>

    {{-- 時間表示 --}}
    <p class="attendance-time">{{ now()->format('H:i') }}</p>

    <div class="attendance-actions">

        {{-- 出勤前 --}}
        @if (!$attendance || $attendance->status === \App\Models\Attendance::STATUS_NONE)

        <form method="POST" action="{{ route('attendance.clockIn') }}">
            @csrf
            <button type="submit" class="attendance-button start-button">出勤</button>
        </form>

        {{-- 出勤中 --}}
        @elseif ($attendance->status === \App\Models\Attendance::STATUS_WORKING)

        <div class="attendance-buttons-row">
            <form method="POST" action="{{ route('attendance.clockOut') }}">
                @csrf
                <button type="submit" class="attendance-button end-button">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance.breakIn') }}">
                @csrf
                <button type="submit" class="attendance-button break-button">休憩入</button>
            </form>
        </div>

        {{-- 休憩中 --}}
        @elseif ($attendance->status === \App\Models\Attendance::STATUS_BREAK)

        <form method="POST" action="{{ route('attendance.breakOut') }}">
            @csrf
            <button type="submit" class="attendance-button break-button">休憩戻</button>
        </form>

        {{-- 退勤後 --}}
        @elseif ($attendance->status === \App\Models\Attendance::STATUS_DONE)

        <p class="attendance-message">お疲れ様でした</p>

        @endif


    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.querySelector('.flash-message');
        if (flash) {
            setTimeout(() => {
                flash.style.opacity = '0';
                flash.style.transition = 'opacity 0.5s ease';
            }, 3000);
        }
    });
</script>

@endsection