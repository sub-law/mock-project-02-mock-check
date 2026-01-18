<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => null, // 必要なら Attendance::factory() に変更
            'date' => Carbon::today(),

            'requested_clock_in' => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(18, 0),

            'note' => 'テスト用の修正申請',
            'status' => StampCorrectionRequest::STATUS_PENDING,
            'admin_comment' => null,
        ];
    }
}
