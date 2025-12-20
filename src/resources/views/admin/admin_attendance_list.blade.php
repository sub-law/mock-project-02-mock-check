@extends('layouts.app_admin')

@section('title', '勤怠一覧画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-wrapper">

    <div class="attendance-header">
        <div class="attendance-line"></div>
        <h1 class="attendance-title">{{ now()->format('Y年m月d日') }}の勤怠</h1>
    </div>

    <div class="attendance-navigation">
        <button class="nav-button nav-prev">
            <img src="{{ asset('images/arrow.png') }}" alt="前日" class="left-icon">
            前日
        </button>

        <div class="calendar-wrapper">
            <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" class="calendar-icon">
            <span class="calendar-label">{{ now()->format('Y年m月d日') }}</span>
        </div>

        <button class="nav-button nav-next">
            <img src="{{ asset('images/arrow.png') }}" alt="翌日" class="right-icon">
            翌日
        </button>
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
            {{-- 仮データ --}}
            <tr>
                <td>山田太郎</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td class="detail-cell">詳細</a></td>

            </tr>
            <tr>
                <td>西伶奈</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td class="detail-cell">詳細</a></td>

            </tr>
        </tbody>
    </table>

</div>
@endsection