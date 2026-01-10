<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminStampCorrectionRequestListController extends Controller
{
    public function index()
    {
        $pending = StampCorrectionRequest::with('user')
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = StampCorrectionRequest::with('user')
            ->where('status', StampCorrectionRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.correction.request_list', compact('pending', 'approved'));
    }

    public function detail($id)
    {
        // 修正申告データを取得（ユーザー＋休憩リレーションも読み込む）
        $correction = StampCorrectionRequest::with(['user', 'breaks'])->findOrFail($id);

        // 対象日の元の勤怠データ（なければ null）
        $attendance = $correction->attendance;

        // 修正申告の休憩データ（リレーション）
        $breaks = $correction->breaks;

        return view('admin.correction.request_detail', compact(
            'correction',
            'attendance',
            'breaks'
        ));
    }

    public function approve($id)
    {
        $correction = StampCorrectionRequest::findOrFail($id);

        DB::transaction(function () use ($correction) {

            // 勤怠更新
            $attendance = Attendance::firstOrNew([
                'user_id' => $correction->user_id,
                'date' => $correction->date,
            ]);

            $attendance->clock_in = $correction->requested_clock_in;
            $attendance->clock_out = $correction->requested_clock_out;
            $attendance->note = $correction->note;
            $attendance->save();

            // 休憩更新
            BreakTime::where('attendance_id', $attendance->id)->delete();

            foreach ($correction->breaks as $break) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            }

            // ステータス更新
            $correction->status = StampCorrectionRequest::STATUS_APPROVED;
            $correction->save();
        });

        return response()->json(['status' => 'approved']);
    }
}
