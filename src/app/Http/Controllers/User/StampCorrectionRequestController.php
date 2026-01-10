<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function store(AttendanceRequest $request, $attendanceId)
    {
        $validated = $request->validated();
        $userId = Auth::id();

        // ================================
        // ① 勤怠が存在する場合（attendanceId != new）
        // ================================
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
        } else {

            // ================================
            // ② 勤怠が存在しない日の修正申請
            //    → ここで勤怠を firstOrCreate して作る
            // ================================
            $date = $request->date;

            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $userId,
                    'date'    => $date,
                ],
                [
                    'status' => Attendance::STATUS_WORKING,
                ]
            );
        }

        // ================================
        // ③ 修正申請の作成（共通処理）
        // ================================
        $scr = StampCorrectionRequest::create([
            'user_id' => $userId,
            'attendance_id' => $attendance->id,
            'date' => $date,
            'requested_clock_in' => $date . ' ' . $validated['clock_in'] . ':00',
            'requested_clock_out' => $date . ' ' . $validated['clock_out'] . ':00',
            'note' => $validated['note'],
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        // ================================
        // ④ 休憩修正申請の保存（共通処理）
        // ================================
        $breakStarts = $validated['break_start'] ?? [];
        $breakEnds   = $validated['break_end'] ?? [];

        foreach ($breakStarts as $i => $start) {
            if ($start) {
                $scr->breaks()->create([
                    'break_start' => $date . ' ' . $start . ':00',
                    'break_end'   => !empty($breakEnds[$i])
                        ? $date . ' ' . $breakEnds[$i] . ':00'
                        : null,
                ]);
            }
        }

        return redirect()
            ->route('attendance.detail', [
                'id' => $attendance->id,
                'date' => $date
            ])
            ->with('success', '修正申告を送信しました');
    }

    public function index()
    {
        $pending = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', StampCorrectionRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.correction.request_list', compact('pending', 'approved'));
    }

    public function detail($id)
    {
        $correction = StampCorrectionRequest::where('user_id', Auth::id())
            ->findOrFail($id);

        return view('user.correction.request_detail', [
            'correction' => $correction,
            'breaks' => $correction->breaks,
        ]);
    }
}
