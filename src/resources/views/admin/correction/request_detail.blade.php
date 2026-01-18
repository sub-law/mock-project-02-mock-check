@extends('layouts.admin')

@section('title', '修正申請承認画面')

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
                <td>{{ $correction->user->name }}</td>
            </tr>

            {{-- 日付 --}}
            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span>{{ $correction->date->format('Y年') }}</span>
                    <span>{{ $correction->date->format('m月d日') }}</span>
                </td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time"
                            value="{{ optional($correction->requested_clock_in)->format('H:i') }}"
                            class="input-field-time"
                            disabled>

                        <span class="time-separator">～</span>

                        <input type="time"
                            value="{{ optional($correction->requested_clock_out)->format('H:i') }}"
                            class="input-field-time"
                            disabled>
                    </div>
                </td>
            </tr>

            {{-- 休憩（複数対応） --}}
            @foreach ($breaks as $i => $break)
            <tr>
                <th>休憩{{ $i + 1 }}</th>
                <td class="time-cell">
                    <div class="time-wrapper">
                        <input type="time"
                            value="{{ $break->break_start->format('H:i') }}"
                            class="input-field-time"
                            disabled>

                        <span class="time-separator">～</span>

                        <input type="time"
                            value="{{ $break->break_end->format('H:i') }}"
                            class="input-field-time"
                            disabled>
                    </div>
                </td>
            </tr>
            @endforeach

            {{-- 備考 --}}
            <tr>
                <th>備考</th>
                <td class="note-cell">
                    <textarea class="input-field-textarea" rows="3" disabled>{{ $correction->note }}
                    </textarea>
                </td>
            </tr>

        </tbody>
    </table>

    <div class="attendance-footer">
        @if ($correction->status === \App\Models\StampCorrectionRequest::STATUS_APPROVED)
        <button class="fix-button approved" disabled>承認済み</button>
        @else
        <button id="approveButton" class="fix-button">承認</button>
        @endif
    </div>

    <script>
        const approveBtn = document.getElementById('approveButton');

        if (approveBtn) {
            approveBtn.addEventListener('click', function() {

                fetch("{{ route('admin.correction.detail.approve', $correction->id) }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json",
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "approved") {
                            approveBtn.textContent = "承認済み";
                            approveBtn.disabled = true;
                            approveBtn.classList.add("approved");
                        }
                    });
            });
        }
    </script>

</div>

@endsection