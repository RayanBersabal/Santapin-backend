<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 🧑‍ Regular User
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'), // or bcrypt()
            'phone' => '081234567890',
            'address' => 'User Address',
            'role' => Role::USER,
        ]);

        // 👨‍💼 Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone' => '089876543210',
            'address' => 'Admin Address',
            'role' => Role::ADMIN,
        ]);
    }
}


/*
    User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@santapin.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);
 */
