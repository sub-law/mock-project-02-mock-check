<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_自分が行った勤怠情報が全て表示されている()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create([
            'email_verified_at' => now()
            ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now()
            ]);

        $this->actingAs($user, 'web');

        // 対象月
        $targetMonth = Carbon::parse('2026-01-01');
        $daysInMonth = $targetMonth->daysInMonth;

        // -----------------------------
        // 自分の勤怠データ作成（ユニーク時刻）

        for ($day = 1; $day <= $daysInMonth; $day++) {

            $date = $targetMonth->copy()->setDay($day)->toDateString();

            $clockIn  = sprintf('09:%02d:00', $day);
            $clockOut = sprintf('18:%02d:00', $day);

            $attendance = Attendance::factory()->create([
                'user_id'   => $user->id,
                'date'      => $date,
                'clock_in'  => "$date $clockIn",
                'clock_out' => "$date $clockOut",
            ]);

            $attendance->breaks()->create([
                'break_start' => "$date 12:00:00",
                'break_end'   => "$date 13:00:00",
            ]);
        }

        // -----------------------------
        // 他人の勤怠データ作成（ユニーク時刻）

        for ($day = 1; $day <= $daysInMonth; $day++) {

            $date = $targetMonth->copy()->setDay($day)->toDateString();

            $clockIn  = sprintf('10:%02d:00', $day);
            $clockOut = sprintf('17:%02d:00', $day);

            $attendance = Attendance::factory()->create([
                'user_id'   => $otherUser->id,
                'date'      => $date,
                'clock_in'  => "$date $clockIn",
                'clock_out' => "$date $clockOut",
            ]);

            $attendance->breaks()->create([
                'break_start' => "$date 14:00:00",
                'break_end'   => "$date 14:30:00",
            ]);
        }

        // -----------------------------
        // 一覧画面へアクセス

        $response = $this->get('/attendance/list?month=2026-01');

        // -----------------------------
        // 自分の勤怠が全件表示されていることを確認

        for ($day = 1; $day <= $daysInMonth; $day++) {

            $response->assertSee(sprintf('09:%02d', $day));
            $response->assertSee(sprintf('18:%02d', $day));
            $response->assertSee('1:00');   // 休憩
            $response->assertSee('8:00');   // 合計
        }

        // -----------------------------
        // 他人の勤怠が表示されないことを確認

        for ($day = 1; $day <= $daysInMonth; $day++) {

            $response->assertDontSee(sprintf('10:%02d', $day));
            $response->assertDontSee(sprintf('17:%02d', $day));
        }
    }

    /** @test */
    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get('/attendance/list');

        $currentMonth = Carbon::now()->format('Y/m');

        $response->assertSee($currentMonth);
    }

    /** @test */
    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $thisMonth = Carbon::now();
        $thisMonthDate = $thisMonth->copy()->setDay(10)->toDateString();
        Attendance::factory()->create([
            'user_id' => $user->id, 
            'date' => $thisMonthDate, 
            'clock_in' => $thisMonthDate . ' 09:00:00', 
            'clock_out' => $thisMonthDate . ' 18:00:00',
            ]);

        $lastMonth = Carbon::now()->subMonth();
        $date = $lastMonth->copy()->setDay(10)->toDateString();
         // 前月の勤怠データを作成 

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id, 
            'date' => $date, 
            'clock_in' => $date . ' 09:00:00', 
            'clock_out' => $date . ' 18:00:00', 
            ]); 
             
        $attendance->breaks()->create([ 
            'break_start' => $date . ' 12:00:00', 
            'break_end' => $date . ' 13:00:00', 
        ]); 
        // 前月の一覧画面へアクセス 
        $response = $this->get('/attendance/list?month=' . $lastMonth->format('Y-m'));

        $response->assertSee($lastMonth->format('Y/m')); 
        $response->assertSee($date);                     

        $response->assertSee('09:00'); 
        $response->assertSee('18:00'); 
        $response->assertSee('1:00'); 
        $response->assertSee('8:00'); 
         
        $response->assertDontSee($thisMonthDate); 
    }

    /** @test */
    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $nextMonth = Carbon::now()->addMonth();
        $nextMonthYm = $nextMonth->format('Y/m');
        $nextMonthDate = $nextMonth->copy()->setDay(10)->locale('ja')->isoFormat('MM/DD(dd)');
        
        $response = $this->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertSee($nextMonthDate);
        $response->assertSee($nextMonthYm);
        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
        $response->assertDontSee('1:00');
        $response->assertDontSee('8:00');
    }

    /** @test */
    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-15',
        ]);

        $this->get('/attendance/list');
        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);

        $expectedYear = Carbon::parse($attendance->date)->format('Y年');
        $expectedDay  = Carbon::parse($attendance->date)->format('n月j日');

        $response->assertSee($expectedYear);
        $response->assertSee($expectedDay);
    }
}
