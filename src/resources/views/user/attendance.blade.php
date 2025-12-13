@extends('layouts.app_user')

@section('title', '勤怠登録画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

    {{-- ステータス表示 --}}
    <p class="attendance-status">勤務外</p>

    {{-- 年月日表示 --}}
    <p class="attendance-date">{{ now()->format('Y年m月d日') }}</p>

    {{-- 時間表示 --}}
    <p class="attendance-time">{{ now()->format('H:i') }}</p>

    {{-- 出勤前は出勤ボタンのみ表示 --}}
    <div class="attendance-actions">
        <button type="submit" class="attendance-button start-button">出勤</button>
        {{-- 仮置きフォーム（後でコントローラとルートが整ったら使用） --}}
        {{--
        <form method="POST" action="{{ route('attendance.start') }}">
        @csrf
        <button type="submit" class="attendance-button start-button">出勤</button>
        </form>
        --}}
    </div>

</div>
@endsection