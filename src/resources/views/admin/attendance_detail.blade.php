@extends('layouts.app_admin')

@section('title', '勤怠詳細画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')

<div class="attendance-header">
    <div class="attendance-line"></div>
    <h1 class="attendance-title">勤怠詳細</h1>
</div>

<div class="attendance-detail-wrapper">
    <table class="attendance-detail-table">
        <tbody>
            <tr class="name-cell">
                <th>名前</th>
                <td>西 伶奈</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span>2025年</span>
                    <span>12月13日</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time" name="start_time" value="09:00" class="input-field-time">
                        <span class="time-separator">～</span>
                        <input type="time" name="end_time" value="18:00" class="input-field-time">
                    </div>
                </td>

            </tr>

            <tr>
                <th>休憩</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time" name="start_time" value="09:00" class="input-field-time">
                        <span class="time-separator">～</span>
                        <input type="time" name="end_time" value="18:00" class="input-field-time">
                    </div>
                </td>

            </tr>
            <tr>
                <th>休憩2</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time" name="start_time" value="09:00" class="input-field-time">
                        <span class="time-separator">～</span>
                        <input type="time" name="end_time" value="18:00" class="input-field-time">
                    </div>
                </td>

            </tr>
            <tr>
                <th>備考</th>
                <td class="note-cell">
                    <textarea name="note" rows="3" class="input-field-textarea">電車遅延のため</textarea>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="attendance-footer">
        <button type="submit" class="fix-button">修正</button>
    </div>

    {{--<form action="{{ route('attendance.update', $attendance->id ?? 1) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="user-info">
        <p><strong>名前：</strong> 西伶奈</p>
        <p><strong>日付：</strong> 2025/12/13(土)</p>
    </div>

    <div class="form-group">
        <label for="start_time">出勤・退勤</label>
        <input type="time" id="start_time" name="start_time" value="09:00">
        <input type="time" id="end_time" name="end_time" value="18:00">
    </div>

    <div class="form-group">
        <label for="break_time">休憩</label>
        <input type="time" id="start_break_time" name="start_break_time" value="01:00">
        <input type="time" id="end_break_time" name="end_break_time" value="01:00">
    </div>

    <div class="form-group">
        <label for="break_time">休憩</label>
        <input type="time" id="start_break_time" name="start_break_time" value="01:00">
        <input type="time" id="end_break_time" name="end_break_time" value="01:00">
    </div>

    <div class="form-group">
        <label for="note">備考</label>
        <textarea id="note" name="note" rows="3">遅延のため修正申請</textarea>
    </div>

    <button type="submit" class="fix-button">修正</button>
    </form>--}}

</div>
@endsection