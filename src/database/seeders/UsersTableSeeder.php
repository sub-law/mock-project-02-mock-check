<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN, 
                'email_verified_at' => now(), 
            ],
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(), 
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(),
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(), 
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(), 
            ],
            [
                'name' => '秋田 朋美',
                'email' => 'tomomi.a@coachtech.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(), 
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(), 
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
