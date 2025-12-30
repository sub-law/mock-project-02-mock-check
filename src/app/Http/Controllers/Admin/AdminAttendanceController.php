<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
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

    public function detail($id, Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $attendance = Attendance::with(['user', 'breaks'])->find($id);

        if (!$attendance) {
            $attendance = new Attendance([
                'date' => $date,
            ]);
            $attendance->setRelation('user', null);
            $attendance->setRelation('breaks', collect());
        }

        // ★ 既存の休憩 + 空1件を必ず渡す
        $breaks = $attendance->breaks->isEmpty()
            ? collect()
            : $attendance->breaks;

        $breaks->push(new \App\Models\BreakTime([
            'break_start' => null,
            'break_end' => null,
        ]));

        $attendance->setRelation('breaks', $breaks);

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
