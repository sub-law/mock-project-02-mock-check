<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_休憩ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->working()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-in');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function test_休憩は一日に何回でもできる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->working()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-in');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        $this->post('/attendance/break-out');
        $response = $this->get('/attendance');

        $response->assertSee('休憩入');
    }

    /** @test */
    public function test_休憩戻ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->working()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-in');
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/attendance/break-out');
        $response = $this->get('/attendance');

        $response->assertSee('勤務中');
    }

    /** @test */
    public function test_休憩戻は一日に何回でもできる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->working()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web');

        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');
        $this->post('/attendance/break-in');

        $response = $this->get('/attendance');

        $response->assertSee('休憩戻');
    }

    /** @test */
    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->working()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web');

        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $break = $attendance->breaks()->first();

        $start = $break->break_start->format('H:i');
        $end   = $break->break_end->format('H:i');

        $response = $this->get('/attendance/list');

        $response->assertSee($start);
        $response->assertSee($end);
    }
}
