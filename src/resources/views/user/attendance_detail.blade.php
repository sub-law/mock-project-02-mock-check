@extends('layouts.app')

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

            {{-- 名前 --}}
            <tr class="name-cell">
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>

            {{-- 日付 --}}
            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span>{{ $date->format('Y年') }}</span>
                    <span>{{ $date->format('n月j日') }}</span>
                </td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time"
                            name="clock_in"
                            class="input-field-time"
                            value="{{ $attendance ? optional($attendance->clock_in)->format('H:i') : '' }}">

                        @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <span class="time-separator">～</span>

                        <input type="time"
                            name="clock_out"
                            class="input-field-time"
                            value="{{ $attendance ? optional($attendance->clock_out)->format('H:i') : '' }}">

                        @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                </td>
            </tr>

            {{-- 休憩（複数対応） --}}
            @php
            $breaks = $attendance?->breaks ?? [];
            @endphp

            @foreach ($breaks as $index => $break)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time"
                            name="break_start[]"
                            class="input-field-time"
                            value="{{ optional($break->break_start)->format('H:i') }}">

                        @error("break_start.$index")
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <span class="time-separator">～</span>

                        <input type="time"
                            name="break_end[]"
                            class="input-field-time"
                            value="{{ optional($break->break_end)->format('H:i') }}">

                        @error("break_end.$index")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </td>
            </tr>
            @endforeach

            <tr>
                <th>休憩{{ ($attendance?->breaks?->count() ?? 0) + 1 }}</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time" name="break_start[]" class="input-field-time" value="">

                        @error('break_start.*')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <span class="time-separator">～</span>
                        <input type="time" name="break_end[]" class="input-field-time" value="">

                        @error('break_end.*')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                    </div>
                </td>
            </tr>

            {{-- 備考 --}}
            <tr>
                <th>備考</th>
                <td class="note-cell">
                    <textarea name="note" rows="3" class="input-field-textarea">
                    {{ $attendance ? ($attendance->note ?? '') : '' }}
                    </textarea>

                    @error('note')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

        </tbody>
    </table>

    <div class="attendance-footer">
        <button type="submit" class="fix-button">修正</button>
    </div>
</div>
@endsection