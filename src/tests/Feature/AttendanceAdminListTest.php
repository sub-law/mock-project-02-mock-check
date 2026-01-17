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

    /** @test */
    public function test_当日の全ユーザーの勤怠情報が正しく表示される()
    {
        // 今日の日付を固定（画面とテストデータを一致させる）
        Carbon::setTestNow('2026-01-17');

        /** @var \App\Models\Admin */
        $admin = Admin::factory()->create();

        // 一般ユーザー3名作成
        $userA = User::factory()->create(['name' => 'ユーザーA']);
        $userB = User::factory()->create(['name' => 'ユーザーB']);
        $userC = User::factory()->create(['name' => 'ユーザーC']);

        // 画面が参照する日付（＝今日）
        $targetDate = Carbon::today()->format('Y-m-d');

        // 完成した勤怠データを作成（出勤・退勤・休憩・status）
        $attendanceA = Attendance::factory()->create([
            'user_id'   => $userA->id,
            'date'      => $targetDate,
            'clock_in'  => $targetDate . ' 09:00:00',
            'clock_out' => $targetDate . ' 18:00:00',
            'status'    => 3,
        ]);
        $attendanceA->breaks()->create([
            'break_start' => $targetDate . ' 12:00:00',
            'break_end'   => $targetDate . ' 13:00:00',
        ]);

        $attendanceB = Attendance::factory()->create([
            'user_id'   => $userB->id,
            'date'      => $targetDate,
            'clock_in'  => $targetDate . ' 10:00:00',
            'clock_out' => $targetDate . ' 19:00:00',
            'status'    => 3,
        ]);
        $attendanceB->breaks()->create([
            'break_start' => $targetDate . ' 13:00:00',
            'break_end'   => $targetDate . ' 14:00:00',
        ]);

        $attendanceC = Attendance::factory()->create([
            'user_id'   => $userC->id,
            'date'      => $targetDate,
            'clock_in'  => $targetDate . ' 11:00:00',
            'clock_out' => $targetDate . ' 20:00:00',
            'status'    => 3,
        ]);
        $attendanceC->breaks()->create([
            'break_start' => $targetDate . ' 14:00:00',
            'break_end'   => $targetDate . ' 15:00:00',
        ]);

        // 管理者ログイン
        $this->actingAs($admin, 'admin');

        // 管理者勤怠一覧画面へアクセス（date パラメータ不要）
        $response = $this->get('/admin/attendance/list');

        // 全ユーザーの勤怠が表示されていることを確認
        $response->assertSee('ユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('ユーザーB');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertSee('ユーザーC');
        $response->assertSee('11:00');
        $response->assertSee('20:00');
    }

    /** @test */
    public function test_勤怠一覧画面にアクセスした際_現在の日付が表示される()
    {
        Carbon::setTestNow('2026-01-17');

        /** @var \App\Models\Admin */
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        $response->assertSee('2026年01月17日');
    }

    /** @test */
    public function test_前日ボタン押下時_前日の勤怠情報が表示される()
    {
        Carbon::setTestNow('2026-01-17');

        /** @var \App\Models\Admin */
        $admin = Admin::factory()->create();

        $user = User::factory()->create(['name' => 'ユーザーA']);

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

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list?date=' . $yesterday);

        $response->assertSee('ユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('2026年01月16日');
    }

    /** @test */
    public function test_翌日ボタン押下時_翌日の勤怠情報が表示される()
    {
        Carbon::setTestNow('2026-01-17');

        /** @var \App\Models\Admin */
        $admin = Admin::factory()->create();

        $user = User::factory()->create(['name' => 'ユーザーA']);

        $tomorrow = '2026-01-18';

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => $tomorrow,
            'clock_in'  => $tomorrow . ' 09:00:00',
            'clock_out' => $tomorrow . ' 18:00:00',
            'status'    => 3,
        ]);
        $attendance->breaks()->create([
            'break_start' => $tomorrow . ' 12:00:00',
            'break_end'   => $tomorrow . ' 13:00:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list?date=' . $tomorrow);

        $response->assertSee('ユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('2026年01月18日');
    }
}
