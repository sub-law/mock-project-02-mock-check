<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;


class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        $activeBreak = $attendance?->breaks()->whereNull('break_end')->first();

        return view('user.attendance', compact('attendance', 'activeBreak'));
    }

    public function clockIn()
    {
        Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => today(),
            ],
            [
                'clock_in' => now(),
                'status' => Attendance::STATUS_WORKING,
            ]
        );

        return redirect()
            ->route('attendance.index')
            ->with('message', '出勤しました');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        if (!$attendance) {
            return redirect()
                ->route('attendance.index')
                ->with('message', '出勤記録がありません');
        }

        if ($attendance->clock_out) {
            return redirect()
                ->route('attendance.index')
                ->with('message', 'すでに退勤済みです');
        }

        $attendance->update([
            'clock_out' => now(),
            'status' => Attendance::STATUS_DONE,
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakIn()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        if (!$attendance) {
            return back()->with('message', '出勤記録がありません');
        }

        // まだ終了していない休憩がある場合はガード
        $activeBreak = $attendance->breaks()->whereNull('break_end')->first();
        if ($activeBreak) {
            return back()->with('message', 'すでに休憩中です');
        }

        // 新しい休憩レコードを作成
        $attendance->breaks()->create(['break_start' => now(),]);
        $attendance->update(['status' => Attendance::STATUS_BREAK,]);

        return back()->with('message', '休憩に入りました');
    }

    public function breakOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        if (!$attendance) {
            return back()->with('message', '出勤記録がありません');
        }

        // 終了していない休憩を取得
        $activeBreak = $attendance->breaks()->whereNull('break_end')->first();
        if (!$activeBreak) {
            return back()->with('message', '休憩中ではありません');
        }

        // 休憩終了
        $activeBreak->update(['break_end' => now(),]);
        $attendance->update(['status' => Attendance::STATUS_WORKING,]);

        return back()->with('message', '休憩から戻りました');
    }

    public function detail($id, Request $request)
    {
        $userId = Auth::id();

        // ★ 新規申告（attendance が存在しないケース）
        if ($id === 'new') {
            $date = Carbon::parse($request->date);

            $cr = StampCorrectionRequest::with('breaks')
                ->where('user_id', $userId)
                ->whereNull('attendance_id')
                ->whereDate('date', $date)
                ->latest()
                ->first();

            // 修正申請の休憩 or 空コレクション
            $breaks = $cr ? $cr->breaks : collect();

            // ★ 空の休憩行を除外
            $breaks = $breaks->filter(fn($b) => $b->break_start && $b->break_end);

            // ★ 共通ロジック
            [$requestedIn, $requestedOut, $isPending] = $this->extractCorrectionMeta($cr);

            return view('user.attendance_detail', [
                'attendance' => null,
                'correction_request' => $cr,
                'breaks' => $breaks,
                'date' => $date,
                'user' => Auth::user(),
                'requestedIn' => $requestedIn,
                'requestedOut' => $requestedOut,
                'isPending' => $isPending,
            ]);
        }

        // ★ 既存勤怠（attendance が存在するケース）
        $attendance = Attendance::with([
            'breaks',
            'user',
            'correction_request' => fn($q) => $q->latest(),
            'correction_request.breaks'
        ])
            ->where('user_id', $userId)
            ->findOrFail($id);

        $cr = $attendance->correction_request;

        // 修正申請の休憩があれば優先、なければ勤怠の休憩
        $breaks = ($cr && $cr->breaks->count() > 0)
            ? $cr->breaks
            : $attendance->breaks;

        // ★ 空の休憩行を除外
        $breaks = $breaks->filter(fn($b) => $b->break_start && $b->break_end);

        // ★ 共通ロジック
        [$requestedIn, $requestedOut, $isPending] = $this->extractCorrectionMeta($cr);

        return view('user.attendance_detail', [
            'attendance' => $attendance,
            'correction_request' => $cr,
            'breaks' => $breaks,
            'date' => $attendance->date,
            'user' => $attendance->user,
            'requestedIn' => $requestedIn,
            'requestedOut' => $requestedOut,
            'isPending' => $isPending,
        ]);
    }

    /**
     * 修正申請のメタ情報を抽出（重複排除）
     */
    private function extractCorrectionMeta($cr)
    {
        $requestedIn  = $cr?->requested_clock_in;
        $requestedOut = $cr?->requested_clock_out;
        $isPending    = $cr && $cr->status === StampCorrectionRequest::STATUS_PENDING;

        return [$requestedIn, $requestedOut, $isPending];
    }
}
