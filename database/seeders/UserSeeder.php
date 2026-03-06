<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者（全店舗アクセス可）
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'store_id' => null,
        ]);

        // 各店舗のマネージャー
        User::create([
            'name' => '田中太郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'store_id' => 1,
        ]);

        User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'store_id' => 4,
        ]);

        // スタッフ
        User::create([
            'name' => '佐藤一郎',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'store_id' => 1,
        ]);
    }
}