<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => today(),
            'clock_in' => null,
            'clock_out' => null,
            'status' => Attendance::STATUS_NONE,
        ];
    }

    // 出勤中
    public function working()
    {
        return $this->state(function () {
            return [
                'clock_in' => now(),
                'status' => Attendance::STATUS_WORKING,
            ];
        });
    }

    // 休憩中
    public function break()
    {
        return $this->state(function () {
            return [
                'clock_in' => now()->subHour(),
                'status' => Attendance::STATUS_BREAK,
            ];
        });
    }

    // 退勤済
    public function done()
    {
        return $this->state(function () {
            return [
                'clock_in' => now()->subHours(8),
                'clock_out' => now(),
                'status' => Attendance::STATUS_DONE,
            ];
        });
    }
}
