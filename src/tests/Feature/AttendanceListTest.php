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
        
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user);

        // 対象月（例：2026年1月）
        $targetMonth = '2026-01';
        $daysInMonth = Carbon::parse($targetMonth . '-01')->daysInMonth;

        // -----------------------------
        // 自分の当月分の勤怠を全件作成（9:00〜18:00、休憩1h）
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%02d', $targetMonth, $day);

            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date,
                'clock_in' => $date . ' 09:00:00',
                'clock_out' => $date . ' 18:00:00',
            ]);

            $attendance->breaks()->create([
                'break_start' => $date . ' 12:00:00',
                'break_end'   => $date . ' 13:00:00',
            ]);
        }

        // -----------------------------
        // 他人の当月分の勤怠を全件作成（10:00〜17:00、休憩30m）
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%02d', $targetMonth, $day);

            $attendance = Attendance::factory()->create([
                'user_id' => $otherUser->id,
                'date' => $date,
                'clock_in' => $date . ' 10:00:00',
                'clock_out' => $date . ' 17:00:00',
            ]);

            $attendance->breaks()->create([
                'break_start' => $date . ' 14:00:00',
                'break_end'   => $date . ' 14:30:00',
            ]);
        }

        // -----------------------------
        // 一覧画面へアクセス
        
        $response = $this->get('/attendance/list?month=' . $targetMonth);

        // -----------------------------
        // 自分の勤怠が全件表示されていることを確認
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $response->assertSee('09:00');
            $response->assertSee('18:00');
            $response->assertSee('1:00');   // 休憩
            $response->assertSee('8:00');   // 合計
        }

        // -----------------------------
        // 他人の勤怠が一切表示されないことを確認
        
        $response->assertDontSee('10:00');
        $response->assertDontSee('17:00');
        $response->assertDontSee('0:30');
        $response->assertDontSee('6:30');
    }

    /** @test */
    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $currentMonth = Carbon::now()->format('Y/m');

        $response->assertSee($currentMonth);
    }

    /** @test */
    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create();
        $this->actingAs($user);

        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $lastMonth . '-10',
        ]);

        // 前月ボタン押下 → GET /attendance/list?month=YYYY-MM
        $response = $this->get('/attendance/list?month=' . $lastMonth);

        $response->assertSee($lastMonth . '-10');
    }

    /** @test */
    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth . '-05',
        ]);

        // 翌月ボタン押下 → GET /attendance/list?month=YYYY-MM
        $response = $this->get('/attendance/list?month=' . $nextMonth);

        $response->assertSee($nextMonth . '-05');
    }

    /** @test */
    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-15',
        ]);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);

        $expectedYear = Carbon::parse($attendance->date)->format('Y年');
        $expectedDay  = Carbon::parse($attendance->date)->format('n月j日');

        $response->assertSee($expectedYear);
        $response->assertSee($expectedDay);
    }
}
