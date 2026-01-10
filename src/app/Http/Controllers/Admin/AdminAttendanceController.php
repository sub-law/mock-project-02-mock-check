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
        // ① 勤怠を取得（存在しない場合は 404）
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        $user = $attendance->user;
        $date = $attendance->date;

        // ② 修正申請（承認待ち）を取得
        $correctionRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->latest()
            ->first();

        if ($correctionRequest) {
            $correctionRequest->load('breaks');
        }

        // ③ 承認待ちフラグ
        $isPending = (bool)$correctionRequest;

        // ④ 表示用の値
        $clockIn  = $correctionRequest?->requested_clock_in  ?? $attendance->clock_in;
        $clockOut = $correctionRequest?->requested_clock_out ?? $attendance->clock_out;

        $breaks = $correctionRequest?->breaks->count()
            ? $correctionRequest->breaks
            : $attendance->breaks;

        $breaks = $breaks->filter(fn($b) => $b->break_start && $b->break_end);
        $breaks->push(new BreakTime(['break_start' => null, 'break_end' => null]));

        return view('admin.attendance_detail', compact(
            'attendance',
            'correctionRequest',
            'clockIn',
            'clockOut',
            'breaks',
            'user',
            'date',
            'isPending'
        ));
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
            ->route('admin.attendance.detail', [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'date' => $attendance->date,
            ])
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
            ->route('admin.attendance.detail', [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,   
                'date' => $attendance->date,         
            ])
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

    public function exportCsv(Request $request, $userId)
    {
        $month = $request->query('month');
        $user = User::findOrFail($userId);

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $fileName = "{$user->name}_{$month}_attendance.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // ヘッダー行（Shift_JIS に変換）
            $header = ['日付', '出勤', '退勤', '休憩合計', '勤務合計'];
            fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $header));

            foreach ($attendances as $a) {
                $break = $a->getTotalBreakMinutes();
                $total = $a->getTotalWorkMinutes();

                $row = [
                    $a->date,
                    optional($a->clock_in)?->format('H:i'),
                    optional($a->clock_out)?->format('H:i'),
                    sprintf('%d:%02d', floor($break / 60), $break % 60),
                    sprintf('%d:%02d', floor($total / 60), $total % 60),
                ];

                // 各行も Shift_JIS に変換
                fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row));
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}
