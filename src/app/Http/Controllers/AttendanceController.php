<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

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
                'status' => 1,
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
            'status' => 2,
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
        $attendance->breaks()->create([
            'break_start' => now(),
        ]);

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
        $activeBreak->update([
            'break_end' => now(),
        ]);

        return back()->with('message', '休憩から戻りました');
    }
}
