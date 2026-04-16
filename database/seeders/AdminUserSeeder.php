<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'isoftrosolutions@gmail.com'],
            [
                'name' => 'Devbarat Prasad Patel',
                'password' => bcrypt('Admin@ChatBot2026'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
