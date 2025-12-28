<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

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

        return view('user.attendance_list', compact('days', 'attendances', 'currentMonth'));
    }
}
