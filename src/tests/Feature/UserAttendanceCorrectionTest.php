<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;

class UserAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([ 
            'user_id' => $user->id, 
            'clock_in' => now()->setTime(9, 0), 
            'clock_out' => now()->setTime(18, 0), 
            ]); 
            
        $this->actingAs($user, 'web'); 

        $response = $this->post("/attendance/{$attendance->id}/correction", [ 
            'clock_in' => '19:00', 
            'clock_out' => '18:00', 
            'note' => 'テスト用の備考', 
              ]); 

        $response->assertSessionHasErrors([ 'clock_in' => '出勤時間が不適切な値です', ]);
    }

    /** @test */
    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([ 
            'user_id' => $user->id, 
            'clock_in' => now()->setTime(9, 0), 
            'clock_out' => now()->setTime(18, 0), ]); 
            
            $this->actingAs($user, 'web'); 
            
        $response = $this->post("/attendance/{$attendance->id}/correction", [ 
            'clock_in' => '09:00', 
            'clock_out' => '18:00', 
            'break_start' => ['19:00'], 
            'break_end' => ['19:30'], 
            'note' => 'テスト用の備考', 
        ]); 

        $response->assertSessionHasErrors([ 'break_start.0' => '休憩時間が不適切な値です', ]);
    }

    /** @test */
    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'    => $user->id,
            'clock_in'   => '09:00',
            'clock_out'  => '18:00',
        ]);

        $this->actingAs($user, 'web');

        $response = $this->post("/attendance/{$attendance->id}/correction", [
            'clock_in'     => '09:00',
            'clock_out'    => '18:00',
            'break_start'  => ['12:00'],
            'break_end'    => ['19:30'], 
            'note'         => 'テスト用の備考',
        ]);

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user, 'web');

        $response = $this->post("/attendance/{$attendance->id}/correction", [
            'clock_in'    => '09:00',
            'clock_out'   => '18:00',
            'break_start' => ['12:00'],
            'break_end'   => ['13:00'],
            'note'        => '', // ★ 未入力
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }


    /** @test */
    public function test_修正申請処理が実行される()
    {
        /** @var \App\Models\User */
        // 一般ユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\Admin */
        // 管理者ユーザー作成
        $admin = Admin::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        // 一般ユーザーとしてログイン
        $this->actingAs($user, 'web');

        // 修正申請を送信
        $response = $this->post("/attendance/{$attendance->id}/correction", [
            'clock_in'     => '09:30',
            'clock_out'    => '18:00',
            'break_start'  => ['12:00'],
            'break_end'    => ['13:00'],
            'note'         => '修正申請テスト',
        ]);

        // 正常にリダイレクトされること
        $response->assertRedirect();

        // ★ DB に修正申請が作成されていることを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'note'          => '修正申請テスト',
            'status'        => 0,
        ]);

        // ★ 作成された修正申請の ID を取得
        $request = \App\Models\StampCorrectionRequest::first();
        $requestId = $request->id;

        // 一般ユーザーをログアウト
        auth()->logout();

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // ① 修正申請一覧画面に表示されること
        $response = $this->get(route('admin.correction.list'));
        $response->assertSee('修正申請テスト');

        // ② 修正申請の詳細画面に表示されること
        $response = $this->get(route('admin.correction.detail', $requestId));
        $response->assertSee('修正申請テスト');
    }



    /** @test */
    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        /** @var \App\Models\User */
        // 一般ユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        // 一般ユーザーとしてログイン
        $this->actingAs($user, 'web');

        // 修正申請を送信
        $this->post("/attendance/{$attendance->id}/correction", [
            'clock_in'     => '09:30',
            'clock_out'    => '18:00',
            'break_start'  => ['12:00'],
            'break_end'    => ['13:00'],
            'note'         => '承認待ちテスト',
        ])->assertRedirect();

        // ★ DB に保存された申請を取得
        $request = \App\Models\StampCorrectionRequest::first();

        // ★ 一般ユーザー側の申請一覧画面へアクセス
        $response = $this->get(route('stamp.correction.request.list'));

        // ★ 自分の申請が一覧に表示されていること
        $response->assertSee('承認待ちテスト');
        $response->assertSee((string)$request->id);
    }


    /** @test */
    public function test_承認済みに管理者が承認した修正申請が表示される()
    {
        /** @var \App\Models\User */
        // 一般ユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        // 一般ユーザーとしてログイン
        $this->actingAs($user, 'web');

        // 修正申請を送信
        $this->post("/attendance/{$attendance->id}/correction", [
            'clock_in'     => '09:30',
            'clock_out'    => '18:00',
            'break_start'  => ['12:00'],
            'break_end'    => ['13:00'],
            'note'         => '承認済みテスト',
        ])->assertRedirect();

        // ★ DB に保存された申請を取得
        $request = \App\Models\StampCorrectionRequest::first();
        $requestId = $request->id;

        // 一般ユーザーをログアウト
        auth()->logout();

        /** @var \App\Models\Admin */
        // 管理者ユーザー作成
        $admin = Admin::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // ★ 管理者が承認処理を実行
        $this->post(route('admin.correction.detail.approve', $requestId))
            ->assertStatus(200);

        // ★ DB が承認済み（status = 1）になっていることを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $requestId,
            'status' => 1,
        ]);

        // 管理者ログアウト
        auth()->logout();

        // ★ 一般ユーザーとして再ログイン
        $this->actingAs($user, 'web');

        // ★ 一般ユーザー側の「承認済み一覧」画面へアクセス
        // ※ ルート名は実装に合わせて変更
        $response = $this->get(route('stamp.correction.request.list'));

        // ★ 承認済みの申請が表示されていること
        $response->assertSee('承認済みテスト');
        $response->assertSee((string)$requestId);
    }


    /** @test */
    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user, 'web');

        $this->post("/attendance/{$attendance->id}/correction", [
            'clock_in'     => '09:30',
            'clock_out'    => '18:00',
            'break_start'  => ['12:00'],
            'break_end'    => ['13:00'],
            'note'         => '詳細画面テスト',
        ])->assertRedirect();

        $request = \App\Models\StampCorrectionRequest::first();
        $requestId = $request->id;

        $this->get(route('stamp.correction.request.list'))
            ->assertStatus(200);

        $response = $this->get(route('stamp.correction.request.detail', $requestId));

        $response->assertStatus(200);

        // 申請内容が表示されていること
        $response->assertSee('09:30');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('詳細画面テスト');
        $response->assertSee('※承認待ちのため修正はできません。');
    }
}
