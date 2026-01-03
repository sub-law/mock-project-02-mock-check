@extends('layouts.admin')

@section('title', '勤怠詳細画面（管理者）')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')

{{-- 成功メッセージ --}}
@if (session('success'))
<p class="success-message">{{ session('success') }}</p>
@endif

{{-- 管理者修正機能実装時に復活させる --}}
{{-- <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST">
@csrf
--}}

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
                <td>{{ optional($attendance->user)->name }}</td>
            </tr>

            {{-- 日付 --}}
            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span>{{ $attendance->date->format('Y年') }}</span>
                    <span>{{ $attendance->date->format('m月d日') }}</span>
                </td>
            </tr>

            {{-- 出勤・退勤（修正申請優先） --}}
            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <div class="time-wrapper">

                        {{-- 出勤 --}}
                        <input type="time"
                            class="input-field-time"
                            value="{{ optional($clockIn)->format('H:i') }}"
                            @if($isPending) disabled @endif>

                        <span class="time-separator">～</span>

                        {{-- 退勤 --}}
                        <input type="time"
                            class="input-field-time"
                            value="{{ optional($clockOut)->format('H:i') }}"
                            @if($isPending) disabled @endif>

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

            {{-- 休憩（修正申請優先） --}}
            @foreach($breaks as $i => $break)
            <tr>
                <th>休憩{{ $i + 1 }}</th>
                <td class="time-cell">
                    <div class="time-wrapper">

                        {{-- 休憩開始 --}}
                        <input type="time"
                            class="input-field-time"
                            value="{{ optional($break->break_start)->format('H:i') }}"
                            @if($isPending) disabled @endif>

                        <span class="time-separator">～</span>

                        {{-- 休憩終了 --}}
                        <input type="time"
                            class="input-field-time"
                            value="{{ optional($break->break_end)->format('H:i') }}"
                            @if($isPending) disabled @endif>

                    </div>

                    {{-- エラー表示 --}}
                    <div class="error-wrapper">
                        @if ($errors->has("break_start.$i"))
                        <span class="error-message">{{ $errors->first("break_start.$i") }}</span>
                        @elseif ($errors->has("break_end.$i"))
                        <span class="error-message">{{ $errors->first("break_end.$i") }}</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach

            {{-- 備考（修正申請優先） --}}
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
                                : $attendance->note
                        ) }}</textarea>

                    {{-- エラー表示 --}}
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

{{-- </form> --}}

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