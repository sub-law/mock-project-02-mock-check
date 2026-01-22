<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_出勤ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->post('/attendance/clock-in');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');   
    }

    /** @test */
    public function test_出勤は一日一回のみできる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->done()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');

        $response->assertDontSee('attendance/clock-in');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $this->post('/attendance/clock-in');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->first();

        $clockIn = $attendance->clock_in->format('H:i');

        $response = $this->get('/attendance/list');

        $response->assertSee($clockIn);
    }
}
