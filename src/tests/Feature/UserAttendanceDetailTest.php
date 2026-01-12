<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;


class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create(['email_verified_at' => now(),]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id, 
            'date' => '2024-01-10',]);

        $this->actingAs($user, 'web');

        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertSee($user->name);
    }

    /** @test */
    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2024-01-10',
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('2024年');
        $response->assertSee('1月10日');
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));
    }

    /** @test */
    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $clockIn  = now()->setTime(9, 0, 0);
        $clockOut = now()->setTime(18, 0, 0);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee($clockIn->format('H:i'));
        $response->assertSee($clockOut->format('H:i'));
    }

    /** @test */
    public function test_「「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create(['email_verified_at' => now(),]); 
        
        $attendance = Attendance::factory()->create([ 'user_id' => $user->id, ]); 
        
        $breakStart = now()->setTime(12, 0, 0); 
        $breakEnd = now()->setTime(13, 0, 0); 
        $break = BreakTime::factory()->create([ 
            'attendance_id' => $attendance->id, 
            'break_start' => $breakStart, 
            'break_end' => $breakEnd, 
            ]); 
            
        $this->actingAs($user, 'web');
        
        $response = $this->get("/attendance/detail/{$attendance->id}"); 
            
        $response->assertSee($breakStart->format('H:i')); 
        $response->assertSee($breakEnd->format('H:i'));
        $this->assertEquals($breakStart, $break->break_start);
        $this->assertEquals($breakEnd, $break->break_end);
    }
}
