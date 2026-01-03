<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
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
        // 日付
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        // 勤怠データ取得
        $attendance = Attendance::with(['user', 'breaks', 'correctionRequest'])->find($id);

        // 勤怠が存在しない場合（新規）
        if (!$attendance) {
            $attendance = new Attendance(['date' => $date]);
            $attendance->setRelation('user', null);
            $attendance->setRelation('breaks', collect());
            $attendance->setRelation('correctionRequest', null);
        }

        // 修正申請
        $correctionRequest = $attendance->correctionRequest;

        // 承認待ちかどうか
        $isPending = $correctionRequest && $correctionRequest->status === StampCorrectionRequest::STATUS_PENDING;

        // 出勤・退勤（修正申請優先）
        $clockIn = $correctionRequest && $correctionRequest->requested_clock_in
            ? $correctionRequest->requested_clock_in
            : $attendance->clock_in;

        $clockOut = $correctionRequest && $correctionRequest->requested_clock_out
            ? $correctionRequest->requested_clock_out
            : $attendance->clock_out;

        // 休憩（修正申請優先）
        $breaks = ($correctionRequest && $correctionRequest->breaks->count() > 0)
            ? $correctionRequest->breaks
            : $attendance->breaks;

        // 空行を1つ追加
        $breaks = $breaks->filter(fn($b) => $b->break_start && $b->break_end);
        $breaks->push(new BreakTime(['break_start' => null, 'break_end' => null]));

        return view('admin.attendance_detail', [
            'attendance' => $attendance,
            'correctionRequest' => $correctionRequest,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaks' => $breaks,
            'isPending' => $isPending,
        ]);
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
