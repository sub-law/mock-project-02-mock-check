<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2024-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

    private function adminLogin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function 勤怠詳細画面に選択したデータが表示される()
    {
        $response = $this->adminLogin()
            ->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200)
            ->assertSee($this->user->name)
            ->assertSee('2024年')
            ->assertSee('1月10日')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('12:00')
            ->assertSee('13:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合_エラーになる()
    {
        $response = $this->adminLogin()
            ->post("/admin/attendance/{$this->attendance->id}", [
                '_method' => 'PUT',
                'clock_in' => '20:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => 'test',
            ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合_エラーになる()
    {
        $response = $this->adminLogin()
            ->post("/admin/attendance/{$this->attendance->id}", [
                '_method' => 'PUT',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['20:00'],
                'note' => 'test',
            ]);

        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合_エラーになる()
    {
        $response = $this->adminLogin()
            ->post("/admin/attendance/{$this->attendance->id}", [
                '_method' => 'PUT',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['19:00'],
                'note' => 'test',
            ]);

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合_エラーになる()
    {
        $response = $this->adminLogin()
            ->post("/admin/attendance/{$this->attendance->id}", [
                '_method' => 'PUT',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
