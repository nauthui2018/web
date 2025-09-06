<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $categories = [
            [
                'name' => 'Programming',
                'description' => 'Programming and software development related tests',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Mathematics and logical reasoning tests',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Science',
                'description' => 'General science and technology tests',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'English',
                'description' => 'English language and literature tests',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}