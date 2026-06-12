<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@tcg.com', 'role' => 'admin'],
            ['name' => 'PM User', 'email' => 'pm@tcg.com', 'role' => 'pm'],
            ['name' => 'Engineer User', 'email' => 'engineer@tcg.com', 'role' => 'engineer'],
            ['name' => 'Viewer User', 'email' => 'viewer@tcg.com', 'role' => 'viewer'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bcrypt('password'),
                'role' => $user['role'],
            ]);
        }
    }
}
