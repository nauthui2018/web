<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_teacher' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create teacher user
        User::create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_teacher' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_teacher' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create additional users
        User::factory(10)->create();
    }
}