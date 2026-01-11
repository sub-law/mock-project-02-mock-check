<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_退勤ボタンが正しく機能する()
    {

        /** @var \App\Models\User */

        $user = User::factory()->create();

        Attendance::factory()->state([
            'user_id' => $user->id,
            'status' => 1, // working
        ])->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->post('/attendance/clock-out');
        $response->assertStatus(302);

        $attendance = Attendance::first();
        $this->assertEquals(3, $attendance->status);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {

        /** @var \App\Models\User */

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/attendance/clock-in');
        $this->post('/attendance/clock-out');

        $attendance = Attendance::first();
        $this->assertNotNull($attendance->clock_out);

        $response = $this->get('/attendance/list');

        $formattedClockOut = Carbon::parse($attendance->clock_out)->format('H:i');

        $response->assertSee($formattedClockOut);
    }
}
