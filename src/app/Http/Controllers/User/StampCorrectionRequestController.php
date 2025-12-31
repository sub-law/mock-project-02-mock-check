<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestBreak;
use Illuminate\Support\Facades\Auth;


class StampCorrectionRequestController extends Controller
{
    public function store(AttendanceRequest $request, $attendanceId)
    {
        $validated = $request->validated();

        if ($attendanceId !== 'new') {

            // 二重申請防止
            $exists = StampCorrectionRequest::where('attendance_id', $attendanceId)
                ->where('status', StampCorrectionRequest::STATUS_PENDING)
                ->exists();

            if ($exists) {
                return back()->with('error', 'すでに承認待ちの修正申請があります。');
            }

            $attendance = Attendance::findOrFail($attendanceId);
            $date = $attendance->date->format('Y-m-d');

            $scr = StampCorrectionRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
                'date' => $date,
                'requested_clock_in' => $date . ' ' . $validated['clock_in'] . ':00',
                'requested_clock_out' => $date . ' ' . $validated['clock_out'] . ':00',
                'note' => $validated['note'],
                'status' => StampCorrectionRequest::STATUS_PENDING,
            ]);


            // ★ 休憩修正申請の保存 
            $breakStarts = $validated['break_start'] ?? [];
            $breakEnds = $validated['break_end'] ?? [];

            foreach ($breakStarts as $i => $start) {
                if ($start) {
                    $scr->breaks()->create([
                        'break_start' => $date . ' ' . $start . ':00',
                        'break_end' => !empty($breakEnds[$i])
                            ? $date . ' ' . $breakEnds[$i] . ':00'
                            : null,
                    ]);
                }
            }

            return redirect()->route('attendance.detail', ['id' => $attendance->id])->with('success', '修正申告を送信しました');
        }

        $date = $request->date;

        $scr = StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => null,
            'date' => $date,
            'requested_clock_in' => $date . ' ' . $validated['clock_in'] . ':00',
            'requested_clock_out' => $date . ' ' . $validated['clock_out'] . ':00',
            'note' => $validated['note'],
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $breakStarts = $validated['break_start'] ?? [];
        $breakEnds = $validated['break_end'] ?? [];

        foreach ($breakStarts as $i => $start) {
            if ($start) {
                $scr->breaks()->create([
                    'break_start' => $date . ' ' . $start . ':00',
                    'break_end' => !empty($breakEnds[$i])
                        ? $date . ' ' . $breakEnds[$i] . ':00'
                        : null,
                ]);
            }
        }

        return redirect()->route('attendance.detail', ['id' => 'new', 'date' => $date])->with('success', '修正申告を送信しました');
    }
}
