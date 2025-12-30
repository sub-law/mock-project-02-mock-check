<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // ① 日付を取得（不正な値なら今日にフォールバック）
        $dateInput = $request->input('date');

        if ($dateInput && Carbon::hasFormat($dateInput, 'Y-m-d')) {
            $date = Carbon::createFromFormat('Y-m-d', $dateInput);
        } else {
            $date = Carbon::today();
        }

        // ② 指定日の勤怠データを取得（ユーザー情報・休憩情報もまとめて取得）
        $attendances = Attendance::with(['user', 'breaks'])
            ->where('date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        // ③ Blade に渡す
        return view('admin.admin_attendance_list', [
            'date' => $date,
            'attendances' => $attendances,
        ]);
    }

    public function detail($id)
    {
        // 勤怠データを取得（存在しない場合は null）
        $attendance = Attendance::with(['user', 'breaks'])->find($id);

        // 勤怠が存在しない場合は空の Attendance を作成
        if (!$attendance) {
            $attendance = new Attendance([
                'date' => now()->toDateString(),
            ]);
            $attendance->setRelation('user', null); // 必要に応じて
            $attendance->setRelation('breaks', collect());
        }

        // 休憩が0件なら空の1件を補完
        if ($attendance->breaks->isEmpty()) {
            $attendance->setRelation('breaks', collect([new \App\Models\BreakTime]));
        }

        return view('admin.attendance_detail', compact('attendance'));
    }

    public function staffAttendance(Request $request, $userId)
    {
        // 対象ユーザー
        $user = User::findOrFail($userId);

        // ?month=2025-01 のような形式で受け取る
        $monthParam = $request->query('month');

        // パラメータがあればその月、なければ今月
        $currentMonth = $monthParam
            ? Carbon::parse($monthParam)->startOfMonth()
            : Carbon::now()->startOfMonth();

        // 月初〜月末
        $start = $currentMonth->copy();
        $end   = $currentMonth->copy()->endOfMonth();

        // 勤怠データ取得（キーを Y-m-d に揃える）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // 月の日付一覧
        $days = [];
        $day = $start->copy();
        while ($day->lte($end)) {
            $days[] = $day->copy();
            $day->addDay();
        }

        return view('admin.admin_attendance_staff', compact(
            'days',
            'attendances',
            'currentMonth',
            'user'
        ));
    }
}
