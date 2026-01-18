<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\StampCorrectionRequest;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequestBreak;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
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
    public function test_承認待ちの修正申請が一覧に全て表示される()
    {
        $user1 = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user1->id,
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);
        StampCorrectionRequest::factory()->create([
            'user_id' => $user2->id,
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $this->adminLogin();

        $response = $this->get(route('admin.correction.list'));

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /** @test */
    public function test_承認済みの修正申請が全て表示されている()
    {
        $user1 = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user1->id,
            'status' => StampCorrectionRequest::STATUS_APPROVED,
        ]);
        StampCorrectionRequest::factory()->create([
            'user_id' => $user2->id,
            'status' => StampCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->adminLogin();

        $response = $this->get(route('admin.correction.list'));

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /** @test */
    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $correction = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id, 
            'attendance_id' => $attendance->id, 
            'date' => '2026-01-01', 
            'requested_clock_in' => '2026-01-01 09:30:00', 'requested_clock_out' => '2026-01-01 18:00:00', 
            'note' => '承認済みテスト', 
            'status' => StampCorrectionRequest::STATUS_PENDING,]);

        StampCorrectionRequestBreak::create([
            'stamp_correction_request_id' => $correction->id,
            'break_start' => '2026-01-01 12:00:00',
            'break_end' => '2026-01-01 13:00:00',
        ]);


        $this->adminLogin();

        $response = $this->get(route('admin.correction.detail', $correction->id));

        $response->assertStatus(200); 
        $response->assertSee($user->name);
        $response->assertSee('2026年');
        $response->assertSee('01月01日');
        $response->assertSee('09:30'); 
        $response->assertSee('18:00'); 
        $response->assertSee('12:00'); 
        $response->assertSee('13:00'); 
        $response->assertSee('承認済みテスト');
}

    /** @test */
    public function test_修正申請の承認処理が正しく行われる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => '2026-01-01',
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'note'      => '元の備考',
        ]);

        $correction = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => '2026-01-01',
            'requested_clock_in' => '2026-01-01 09:30:00',
            'requested_clock_out' => '2026-01-01 18:30:00',
            'note' => '修正後の備考',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        StampCorrectionRequestBreak::create([
            'stamp_correction_request_id' => $correction->id,
            'break_start' => '2026-01-01 12:00:00',
            'break_end' => '2026-01-01 13:00:00',
        ]);

        $this->adminLogin();

        $response = $this->post(route('admin.correction.detail.approve', $correction->id));

        $response->assertStatus(200);
        $response->assertJson(['status' => 'approved']);
        
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $correction->id,
            'status' => 
        StampCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2026-01-01 09:30:00',
            'clock_out' => '2026-01-01 18:30:00',
            'note' => '修正後の備考',
        ]);

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-01-01 12:00:00',
            'break_end' => '2026-01-01 13:00:00',
        ]);
    }
}
