@extends('layouts.app')

@section('title', '勤怠詳細画面（管理者）')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
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
                <td>{{ optional($attendance->user)->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span>{{ $attendance->date->format('Y年') }}</span>
                    <span>{{ $attendance->date->format('m月d日') }}</span>
                </td>

            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time" class="input-field-time"
                            value="{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}">

                        <span class="time-separator">～</span>

                        <input type="time" class="input-field-time"
                            value="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}">

                    </div>
                </td>

            </tr>

            @foreach($attendance->breaks as $i => $break)
            <tr>
                <th>休憩{{ $i + 1 }}</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time" class="input-field-time"
                            value="{{ $break->break_start ? $break->break_start->format('H:i') : '' }}">

                        <span class="time-separator">～</span>

                        <input type="time" class="input-field-time"
                            value="{{ $break->break_end ? $break->break_end->format('H:i') : '' }}">

                    </div>
                </td>
            </tr>
            @endforeach


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
</div>
@endsection