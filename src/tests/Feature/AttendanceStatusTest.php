<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function test_勤務外の場合_勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test */
    public function test_出勤中の場合_勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->working()->create(['user_id' => $user->id,]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function test_休憩中の場合_勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->break()->create(['user_id' => $user->id,]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function test_退勤済の場合_勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->done()->create(['user_id' => $user->id,]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');

        $response->assertSee('退勤済');
    }
}
