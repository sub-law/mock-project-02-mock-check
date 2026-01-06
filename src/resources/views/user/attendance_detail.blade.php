@extends('layouts.user')

@section('title', '勤怠詳細画面')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')

{{-- 成功メッセージ --}}
@if (session('success'))
<p class="success-message">{{ session('success') }}</p>
@endif

<form action="{{ route('correction.store', ['attendanceId' => $attendance->id ?? 'new']) }}" method="POST">
    @csrf

    {{-- 新規申告時のみ日付を hidden で送る --}}
    @if (!$attendance)
    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
    @endif

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

                            {{-- 出勤 --}}
                            <input
                                type="time"
                                name="clock_in"
                                class="input-field-time"
                                value="{{ old('clock_in', optional($clockIn)->format('H:i')) }}"

                                @if ($isPending) disabled @endif>

                            <span class="time-separator">～</span>

                            {{-- 退勤 --}}
                            <input
                                type="time"
                                name="clock_out"
                                class="input-field-time"
                                value="{{ old('clock_out', optional($clockOut)->format('H:i')) }}"

                                @if ($isPending) disabled @endif>

                        </div>

                        {{-- エラー表示 --}}
                        <div class="error-wrapper">
                            @if ($errors->has('clock_in'))
                            <span class="error-message">{{ $errors->first('clock_in') }}</span>
                            @elseif ($errors->has('clock_out'))
                            <span class="error-message">{{ $errors->first('clock_out') }}</span>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- 休憩（既存行） --}}
                @foreach ($breaks as $index => $break)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td class="time-cell">
                        <div class="time-wrapper">

                            <input
                                type="time"
                                name="break_start[]"
                                class="input-field-time"
                                value="{{ old("break_start.$index", optional($break->break_start)->format('H:i')) }}"
                                @if ($isPending) disabled @endif>

                            <span class="time-separator">～</span>

                            <input
                                type="time"
                                name="break_end[]"
                                class="input-field-time"
                                value="{{ old("break_end.$index", optional($break->break_end)->format('H:i')) }}"
                                @if ($isPending) disabled @endif>

                        </div>

                        {{-- エラー表示（最適化済み） --}}
                        <div class="error-wrapper">
                            @if ($errors->has("break_start.$index"))
                            <span class="error-message">{{ $errors->first("break_start.$index") }}</span>
                            @elseif ($errors->has("break_end.$index"))
                            <span class="error-message">{{ $errors->first("break_end.$index") }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach

                {{-- 新規休憩行 --}}
                @php $nextIndex = $breaks->count(); @endphp

                @if (!$isPending || old("break_start.$nextIndex") || old("break_end.$nextIndex"))
                <tr>
                    <th>休憩{{ $nextIndex + 1 }}</th>
                    <td class="time-cell">
                        <div class="time-wrapper">

                            <input
                                type="time"
                                name="break_start[]"
                                class="input-field-time"
                                value="{{ old("break_start.$nextIndex") }}">

                            <span class="time-separator">～</span>

                            <input
                                type="time"
                                name="break_end[]"
                                class="input-field-time"
                                value="{{ old("break_end.$nextIndex") }}">

                        </div>

                        {{-- エラー表示（最適化済み） --}}
                        <div class="error-wrapper">
                            @if ($errors->has("break_start.$nextIndex"))
                            <span class="error-message">{{ $errors->first("break_start.$nextIndex") }}</span>
                            @elseif ($errors->has("break_end.$nextIndex"))
                            <span class="error-message">{{ $errors->first("break_end.$nextIndex") }}</span>
                            @endif
                        </div>

                    </td>
                </tr>
                @endif

                {{-- 備考 --}}
                <tr>
                    <th>備考</th>
                    <td class="note-cell">
                        <textarea
                            name="note"
                            rows="3"
                            class="input-field-textarea"
                            @if ($isPending) disabled @endif>{{ old('note',
                                $correctionRequest
                                    ? $correctionRequest->note
                                    : ($attendance ? $attendance->note : '')
                            ) }}</textarea>

                        <div class="error-wrapper">
                            @error('note')
                            <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>

        {{-- フッター --}}
        <div class="attendance-footer">
            @if ($isPending)
            <p class="pending-message">※承認待ちのため修正はできません。</p>
            @else
            <button type="submit" class="fix-button">修正</button>
            @endif
        </div>

    </div>

</form>

{{-- 成功メッセージのフェードアウト --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.querySelector('.success-message');
        if (flash) {
            setTimeout(() => {
                flash.style.opacity = '0';
                flash.style.transition = 'opacity 0.5s ease';
            }, 3000);
        }
    });
</script>

@endsection