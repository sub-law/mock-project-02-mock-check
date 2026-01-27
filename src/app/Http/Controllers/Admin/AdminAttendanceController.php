<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\AdminAttendanceRequest;
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
        $dateInput = $request->input('date');

        if ($dateInput && Carbon::hasFormat($dateInput, 'Y-m-d')) {
            $date = Carbon::createFromFormat('Y-m-d', $dateInput);
        } else {
            $date = Carbon::now()->startOfDay();
        }

        // 全ユーザー取得
        $users = User::orderBy('id')->get();

        // その日の勤怠を user_id をキーにして取得
        $attendances = Attendance::with(['breaks'])
            ->whereDate('date', $date->toDateString())
            ->get()
            ->keyBy('user_id');

        return view('admin.admin_attendance_list', [
            'date' => $date,
            'users' => $users,
            'attendances' => $attendances,
        ]);
    }


    public function detail($id, Request $request)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        $user = $attendance->user;
        $date = $attendance->date;

        $correctionRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->latest()
            ->first();

        if ($correctionRequest) {
            $correctionRequest->load('breaks');
        }

        $isPending = (bool)$correctionRequest;

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

    public function create(Request $request)
    {
        $userId = $request->query('user_id');
        $date   = Carbon::parse($request->query('date'));

        $user = User::findOrFail($userId);

        // ★ ダミーの Attendance（exists = false）
        $attendance = new Attendance([
            'user_id' => $userId,
            'date' => $date,
            'clock_in' => null,
            'clock_out' => null,
            'note' => null,
        ]);

        // ★ 空の休憩1行だけ
        $breaks = collect([
            new BreakTime(['break_start' => null, 'break_end' => null])
        ]);

        return view('admin.attendance_detail', [
            'attendance' => $attendance,
            'correctionRequest' => null,
            'clockIn' => null,
            'clockOut' => null,
            'breaks' => $breaks,
            'user' => $user,
            'date' => $date,
            'isPending' => false,
        ]);
    }


    public function staffattendance(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $monthParam = $request->query('month');

        $currentMonth = $monthParam
            ? Carbon::parse($monthParam)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $start = $currentMonth->copy();
        $end   = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

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
        $attendance = Attendance::create([
            'user_id'   => $request->user_id,
            'date'      => $request->date,
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
            'status'    => $request->clock_out ? Attendance::STATUS_DONE : Attendance::STATUS_WORKING,
        ]);

        $this->saveBreakTimes($attendance, $request);

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

    public function update(AdminAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
            'status'    => $request->clock_out ? Attendance::STATUS_DONE : Attendance::STATUS_WORKING,
        ]);

        $this->saveBreakTimes($attendance, $request);

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

    private function saveBreakTimes(Attendance $attendance, Request $request)
    {
        $attendance->breaks()->delete();

        $starts = $request->break_start ?? [];
        $ends   = $request->break_end ?? [];

        foreach ($starts as $i => $start) {
            $end = $ends[$i] ?? null;

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

                fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row));
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}
