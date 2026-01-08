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
        $users = User::all();

        foreach ($users as $user) {

            $months = [
                Carbon::now()->subMonth()->startOfMonth(), // 前月（特殊日あり）
                Carbon::now()->startOfMonth(),             // 当月（通常のみ）
            ];

            foreach ($months as $monthStart) {

                $date = $monthStart->copy();

                // 当月は昨日まで
                if ($monthStart->isSameMonth(Carbon::now())) {
                    $yesterday = Carbon::now()->subDay();

                    if ($yesterday->lt($monthStart)) {
                        continue;
                    }

                    $monthEnd = $yesterday;
                } else {
                    $monthEnd = $monthStart->copy()->endOfMonth();
                }

                $isLastMonth = $monthStart->isSameMonth(Carbon::now()->subMonth());
                $isReina = $user->email === 'reina.n@coachtech.com';

                // 土日なら次の平日にずらす
                $nextWeekday = function (Carbon $d) {
                    while ($d->isWeekend()) {
                        $d->addDay();
                    }
                    return $d;
                };

                // 特殊日設定（前月のみ）
                if ($isLastMonth) {
                    $dayOff         = $nextWeekday($monthStart->copy()->addDays(3));
                    $noBreakDay     = $nextWeekday($monthStart->copy()->addDays(5));
                    $noClockOutDay  = $nextWeekday($monthStart->copy()->addDays(7));
                    $breakNotEndDay = $nextWeekday($monthStart->copy()->addDays(9));
                    $twoBreakDay    = $nextWeekday($monthStart->copy()->addDays(11));
                }

                while ($date->lte($monthEnd)) {

                    // ①〜⑤ 特殊日（前月のみ）
                    if ($isReina && $isLastMonth) {

                        // ④ 勤怠なし
                        if ($date->isSameDay($dayOff)) {
                            Attendance::where('user_id', $user->id)
                                ->where('date', $date->toDateString())
                                ->delete();

                            BreakTime::whereHas('attendance', function ($q) use ($user, $date) {
                                $q->where('user_id', $user->id)
                                    ->where('date', $date->toDateString());
                            })->delete();

                            $date->addDay();
                            continue;
                        }

                        // ① 休憩なし
                        if ($date->isSameDay($noBreakDay)) {

                            $attendance = Attendance::updateOrCreate(
                                [
                                    'user_id' => $user->id,
                                    'date'    => $date->toDateString(),
                                ],
                                [
                                    'clock_in'  => $date->toDateString() . ' 09:00:00',
                                    'clock_out' => $date->toDateString() . ' 18:00:00',
                                    'status'    => Attendance::STATUS_DONE,
                                ]
                            );

                            BreakTime::where('attendance_id', $attendance->id)->delete();

                            $date->addDay();
                            continue;
                        }

                        // ③ 休憩終了なし（休憩入だけ・退勤なし）
                        if ($date->isSameDay($breakNotEndDay)) {

                            $attendance = Attendance::updateOrCreate(
                                [
                                    'user_id' => $user->id,
                                    'date'    => $date->toDateString(),
                                ],
                                [
                                    'clock_in'  => $date->toDateString() . ' 09:00:00',
                                    'clock_out' => null,
                                    'status' => Attendance::STATUS_BREAK,
                                ]
                            );

                            BreakTime::where('attendance_id', $attendance->id)->delete();

                            BreakTime::create([
                                'attendance_id' => $attendance->id,
                                'break_start'   => $date->toDateString() . ' 12:00:00',
                                'break_end'     => null,
                            ]);

                            $date->addDay();
                            continue;
                        }

                        // ⑤ 休憩2回
                        if ($date->isSameDay($twoBreakDay)) {

                            $attendance = Attendance::updateOrCreate(
                                [
                                    'user_id' => $user->id,
                                    'date'    => $date->toDateString(),
                                ],
                                [
                                    'clock_in'  => $date->toDateString() . ' 09:00:00',
                                    'clock_out' => $date->toDateString() . ' 18:00:00',
                                    'status'    => Attendance::STATUS_DONE,
                                ]
                            );

                            BreakTime::where('attendance_id', $attendance->id)->delete();

                            BreakTime::create([
                                'attendance_id' => $attendance->id,
                                'break_start'   => $date->toDateString() . ' 12:00:00',
                                'break_end'     => $date->toDateString() . ' 12:45:00',
                            ]);

                            BreakTime::create([
                                'attendance_id' => $attendance->id,
                                'break_start'   => $date->toDateString() . ' 15:00:00',
                                'break_end'     => $date->toDateString() . ' 15:45:00',
                            ]);

                            $date->addDay();
                            continue;
                        }
                    }

                    // 土日スキップ
                    if ($date->isWeekend()) {
                        $date->addDay();
                        continue;
                    }

                    // 通常勤怠
                    $attendance = Attendance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date'    => $date->toDateString(),
                        ],
                        [
                            'clock_in'  => $date->toDateString() . ' 09:00:00',
                            'clock_out' => $date->toDateString() . ' 18:00:00',
                            'status'    => Attendance::STATUS_DONE,
                        ]
                    );

                    BreakTime::where('attendance_id', $attendance->id)->delete();

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $date->toDateString() . ' 12:00:00',
                        'break_end'     => $date->toDateString() . ' 13:00:00',
                    ]);

                    $date->addDay();
                }
            }
        }
    }
}
