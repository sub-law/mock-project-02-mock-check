<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceAdminListTest extends TestCase
{
    use RefreshDatabase;

    private function adminLogin()
    {
        /** @var \App\Models\Admin */

        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        return $this->actingAs($admin, 'admin');
    }

    /** @test */
    public function test_当日の全ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow('2026-01-17');
        $today = Carbon::parse('2026-01-17');

        $this->adminLogin();

        // 一般ユーザー3名作成
        $userA = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'ユーザーA']);
        $userB = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'ユーザーB']);
        $userC = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'ユーザーC']);

        // 完成した勤怠データを作成（出勤・退勤・休憩・status）
        $attendanceA = Attendance::factory()->create([
            'user_id'   => $userA->id,
            'date' => $today->toDateString(),
            'clock_in' => $today->copy()->setTime(9, 0),
            'clock_out' => $today->copy()->setTime(18, 0),
            'status'    => 1,
        ]);
        $attendanceA->breaks()->create([
            'break_start' => $today->copy()->setTime(12, 0),
            'break_end'   => $today->copy()->setTime(13, 0),
        ]);

        $attendanceB = Attendance::factory()->create([
            'user_id'   => $userB->id,
            'date' => $today->toDateString(),
            'clock_in' => $today->copy()->setTime(10, 0),
            'status'    => 1,
        ]);
        $attendanceB->breaks()->create([
            'break_start' => $today->copy()->setTime(12, 0),
            'break_end'   => $today->copy()->setTime(14, 0),
        ]);

        $attendanceC = Attendance::factory()->create([
            'user_id'   => $userC->id,
            'date' => $today->toDateString(),
            'clock_in' => $today->copy()->setTime(11, 0),
            'status'    => 1,
        ]);
        $attendanceC->breaks()->create([
            'break_start' => $today->copy()->setTime(12, 0),
            'break_end'   => $today->copy()->setTime(15, 0),
        ]);

        //dd(Attendance::all());

        // 管理者勤怠一覧画面へアクセス（date パラメータ不要）
        $response = $this->get('/admin/attendance/list');

        $response->assertSee('2026年01月17日');

        // 全ユーザーの勤怠が表示されていることを確認
        $response->assertSee('ユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('1:00');

        $response->assertSee('ユーザーB');
        $response->assertSee('10:00');
        $response->assertSee('2:00');

        $response->assertSee('ユーザーC');
        $response->assertSee('11:00');
        $response->assertSee('3:00');
    }

    /** @test */
    public function test_勤怠一覧画面にアクセスした際_現在の日付が表示される()
    {
        $this->adminLogin();
        Carbon::setTestNow('2026-01-17');

        $response = $this->get('/admin/attendance/list');

        $response->assertSee('2026年01月17日');
    }

    /** @test */
    public function test_前日ボタン押下時_前日の勤怠情報が表示される()
    {
        Carbon::setTestNow('2026-01-16');
        $yesterday = Carbon::parse('2026-01-16');

        $this->adminLogin();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'ユーザーA'
        ]);

        $yesterday = '2026-01-16';

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => $yesterday,
            'clock_in'  => $yesterday . ' 09:00:00',
            'clock_out' => $yesterday . ' 18:00:00',
            'status'    => 3,
        ]);
        $attendance->breaks()->create([
            'break_start' => $yesterday . ' 12:00:00',
            'break_end'   => $yesterday . ' 13:00:00',
        ]);

        $response = $this->get('/admin/attendance/list?date=' . $yesterday);

        $response->assertSee('2026年01月16日');
        $response->assertSee('ユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    /** @test */
    public function test_翌日ボタン押下時_翌日の勤怠情報が表示される()
    {
        $this->adminLogin();

        Carbon::setTestNow('2026-01-18');
        $tomorrow = Carbon::parse('2026-01-18');

        $response = $this->get('/admin/attendance/list?date=' . $tomorrow);

        $response->assertSee('2026年01月18日');
        $response->assertSee('データがありません');
    }
}
