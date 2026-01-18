<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        /** @var \App\Models\User */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->withSession([])->get('/attendance');

        $date = now()->format('Y年m月d日');
        $time = now()->format('H:i');

        $response->assertSee($date);
        $response->assertSee($time);
    }
}
