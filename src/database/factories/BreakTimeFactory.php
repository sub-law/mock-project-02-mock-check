<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;

class BreakTimeFactory extends Factory
{
    public function definition()
    {
        $start = $this->faker->time('H:i:s');
        $end   = $this->faker->time('H:i:s');

        // 開始 < 終了 になるように調整
        if ($start >= $end) {
            $end = date('H:i:s', strtotime($start . ' +1 hour'));
        }

        return [
            'attendance_id' => null,
            'break_start'   => $start,
            'break_end'     => $end,
        ];
    }
}
