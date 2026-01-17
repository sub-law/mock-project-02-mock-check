<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者でログインするヘルパー
     */
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
    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $this->adminLogin();

        $users = User::factory()->count(3)->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/staff/list');

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $this->adminLogin();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2024-01-10'),
            'clock_in' => Carbon::createFromTime(9, 0),
            'clock_out' => Carbon::createFromTime(18, 0),
        ]);


        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2024-01");

        $response->assertSee('01/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $this->adminLogin();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y/m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonth . '-10',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}?month={$previousMonth}");

        $response->assertSee($previousMonth);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $this->adminLogin();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $currentMonth = Carbon::now()->startOfMonth();
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y/m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth . '-15',
            'clock_in' => '10:00',
            'clock_out' => '19:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}?month={$nextMonth}");

        $response->assertSee($nextMonth);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $this->adminLogin();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2024-01-20',
            'clock_in' => '08:30',
            'clock_out' => '17:30',
        ]);

        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('2024-01-20');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }
}
