<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
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
}
