<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDummySeeder extends Seeder
{
    public function run()
    {
        // 一般ユーザーのみ
        $users = User::where('role', 0)->get();

        foreach ($users as $user) {

            // 前月と当月
            $months = [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->startOfMonth(),
            ];

            foreach ($months as $monthStart) {

                $date = $monthStart->copy();
                $monthEnd = $monthStart->copy()->endOfMonth();

                // 西 伶奈の特別処理
                $isReina = $user->email === 'reina.n@coachtech.com';

                // 特殊日設定（例として日付を固定）
                $dayOff         = $monthStart->copy()->addDays(3);   // 勤怠なし
                $noBreakDay     = $monthStart->copy()->addDays(5);   // 休憩なし
                $noClockOutDay  = $monthStart->copy()->addDays(7);   // 出勤のみ
                $breakNotEndDay = $monthStart->copy()->addDays(9);   // 休憩終了なし
                $twoBreakDay    = $monthStart->copy()->addDays(11);  // 休憩2回

                while ($date->lte($monthEnd)) {

                    // 土日はスキップ
                    if ($date->isWeekend()) {
                        $date->addDay();
                        continue;
                    }

                    // ④ 西 伶奈：勤怠なしの日
                    if ($isReina && $date->isSameDay($dayOff)) {
                        $date->addDay();
                        continue;
                    }

                    // 勤怠データ作成（基本形）
                    $attendance = Attendance::create([
                        'user_id'   => $user->id,
                        'date'      => $date->toDateString(),
                        'clock_in'  => $date->copy()->setTime(9, 0),
                        'clock_out' => $date->isSameDay($noClockOutDay) && $isReina
                            ? null
                            : $date->copy()->setTime(18, 0),
                        'status'    => 2,
                    ]);

                    // ① 西 伶奈：休憩なしの日
                    if ($isReina && $date->isSameDay($noBreakDay)) {
                        $date->addDay();
                        continue;
                    }

                    // ③ 西 伶奈：休憩終了なしの日
                    if ($isReina && $date->isSameDay($breakNotEndDay)) {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $date->copy()->setTime(12, 0),
                            'break_end'     => null, // 終了なし
                        ]);
                        $date->addDay();
                        continue;
                    }

                    // ⑤ 西 伶奈：休憩2回（合計1:30）
                    if ($isReina && $date->isSameDay($twoBreakDay)) {

                        // 1回目：12:00〜12:45
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $date->copy()->setTime(12, 0),
                            'break_end'     => $date->copy()->setTime(12, 45),
                        ]);

                        // 2回目：15:00〜15:45
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $date->copy()->setTime(15, 0),
                            'break_end'     => $date->copy()->setTime(15, 45),
                        ]);

                        $date->addDay();
                        continue;
                    }

                    // 通常休憩（12:00〜13:00）
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $date->copy()->setTime(12, 0),
                        'break_end'     => $date->copy()->setTime(13, 0),
                    ]);

                    $date->addDay();
                }
            }
        }
    }
}
