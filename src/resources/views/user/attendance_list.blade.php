@extends('layouts.app_user')

@section('title', '勤怠一覧画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-wrapper">

    <div class="attendance-header">
        <div class="attendance-line"></div>
        <h1 class="attendance-title">勤怠一覧</h1>
    </div>

    <div class="attendance-navigation">
        <button class="nav-button prev-month">
            <img src="{{ asset('images/arrow.png') }}" alt="前月" class="left-icon">
            前月
        </button>
        <span class="calendar-label">カレンダー</span>
        <button class="nav-button prev-month">
            <img src="{{ asset('images/arrow.png') }}" alt="翌月" class="right-icon">
            翌月
        </button>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
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
                <td>12/13(土)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td class="detail-cell"><a href="{{ route('attendance.detail', ['id' => 1]) }}">詳細</a></td>

            </tr>
            <tr>
                <td>12/14(日)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td class="detail-cell"><a href="{{ route('attendance.detail', ['id' => 1]) }}">詳細</a></td>

            </tr>
        </tbody>
    </table>

</div>
@endsection