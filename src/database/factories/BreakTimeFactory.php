<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => null, // テスト側で指定する
            'break_start'   => $this->faker->dateTime(),
            'break_end'     => $this->faker->dateTime(),
        ];
    }
}
