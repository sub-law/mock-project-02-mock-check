<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
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
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        // ★ 一般ユーザーIDを取得（必須）
        $userId = $request->query('user_id');
        $user = User::find($userId);

        $attendance = Attendance::with([
            'user',
            'breaks',
            'correctionRequest' => fn($q) => $q->latest()->limit(1),
            'correctionRequest.breaks'
        ])->find($id);

        if (!$attendance) {
            $attendance = new Attendance(['date' => $date]);
            $attendance->setRelation('user', $user); // ★ ここで user をセット
            $attendance->setRelation('breaks', collect());
            $attendance->setRelation('correctionRequest', null);
        }

        $correctionRequest = $attendance->correctionRequest;

        $isPending = $correctionRequest && $correctionRequest->status === StampCorrectionRequest::STATUS_PENDING;

        $clockIn = $correctionRequest?->requested_clock_in ?? $attendance->clock_in;
        $clockOut = $correctionRequest?->requested_clock_out ?? $attendance->clock_out;

        $breaks = ($correctionRequest && $correctionRequest->breaks->count() > 0)
            ? $correctionRequest->breaks
            : $attendance->breaks;

        $breaks = $breaks->filter(fn($b) => $b->break_start && $b->break_end);
        $breaks->push(new BreakTime(['break_start' => null, 'break_end' => null]));

        return view('admin.attendance_detail', [
            'attendance'        => $attendance,
            'correctionRequest' => $correctionRequest,
            'clockIn'           => $clockIn,
            'clockOut'          => $clockOut,
            'breaks'            => $breaks,
            'isPending'         => $isPending,
            'user'              => $user, 
            'date'              => $date,
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

    public function store(AttendanceRequest $request)
    {
        // ① 勤怠の新規作成
        $attendance = Attendance::create([
            'user_id'   => $request->user_id,
            'date'      => $request->date,
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
            'status'    => $request->clock_out ? Attendance::STATUS_DONE : Attendance::STATUS_WORKING,
        ]);

        // ② 休憩の保存 
        $this->saveBreakTimes($attendance, $request);

        // ③ 修正申請があれば反映済みにする（任意）
        StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->update(['status' => StampCorrectionRequest::STATUS_APPROVED]);

        return redirect()
            ->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('success', '勤怠を新規登録しました');
    }

    public function update(AttendanceRequest $request, $id)
    { // ① 既存勤怠を取得 
        $attendance = Attendance::findOrFail($id);

        // ② 勤怠の更新 
        $attendance->update(['clock_in' => $request->clock_in, 'clock_out' => $request->clock_out, 'note' => $request->note, 'status' => $request->clock_out ? Attendance::STATUS_DONE : Attendance::STATUS_WORKING,]);

        // ③ 休憩の更新（全削除 → 再作成） 
        $this->saveBreakTimes($attendance, $request);

        // ④ 修正申請があれば反映済みにする 
        StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->update(['status' => StampCorrectionRequest::STATUS_APPROVED]);

        return redirect()
            ->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('success', '勤怠を修正しました');
    }

    private function saveBreakTimes(Attendance $attendance, AttendanceRequest $request)
    {
        // 既存休憩を削除
        $attendance->breaks()->delete();

        $starts = $request->break_start ?? [];
        $ends   = $request->break_end ?? [];

        foreach ($starts as $i => $start) {
            $end = $ends[$i] ?? null;

            // 両方ある場合のみ保存
            if ($start && $end) {
                $attendance->breaks()->create([
                    'break_start' => $start,
                    'break_end'   => $end,
                ]);
            }
        }
    }
}
