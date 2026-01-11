<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_名前が未入力の場合_バリデーションエラーになる()
    {
        $response = $this->post('/register', [
            'name' => '', 
            'email' => 'test@example.com', 
            'password' => 'password123', 
            'password_confirmation' => 'password123',]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください'
            ]);
    }

    /** @test */
    public function test_メールアドレスが未入力の場合_バリデーションエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎', 
            'email' => '', 
            'password' => 'password123', 
            'password_confirmation' => 'password123',]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
            ]);
    }

    /** @test */
    public function test_パスワードが8文字未満の場合_バリデーションエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎', 
            'email' => 'test@example.com', 
            'password' => '1234567', 
            'password_confirmation' => '1234567',
            ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    public function test_パスワードが一致しない場合_バリデーションエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎', 
            'email' => 'test@example.com', 
            'password' => 'password123', 
            'password_confirmation' => 'different',
            ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    public function test_パスワードが未入力の場合_バリデーションエラーになる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎', 
            'email' => 'test@example.com', 
            'password' => '', 
            'password_confirmation' => '',
            ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function test_正しい入力ならユーザーが保存される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎', 
            'email' => 'test@example.com', 
            'password' => 'password123', 
            'password_confirmation' => 'password123',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com',]);
    }
}

