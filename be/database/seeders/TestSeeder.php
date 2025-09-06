<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;
use App\Models\Category;
use App\Models\User;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::where('is_teacher', true)->get();
        $categories = Category::all();

        if ($teachers->count() === 0 || $categories->count() === 0) {
            return;
        }

        $tests = [
            [
                'title' => 'Introduction to Programming',
                'description' => 'Basic programming concepts and fundamentals',
                'duration_minutes' => 60,
                'category_id' => $categories->where('name', 'Programming')->first()->id ?? $categories->first()->id,
                'is_active' => true,
                'is_public' => true,
                'created_by' => $teachers->first()->id,
            ],
            [
                'title' => 'Advanced Mathematics',
                'description' => 'Complex mathematical problems and theories',
                'duration_minutes' => 90,
                'category_id' => $categories->where('name', 'Mathematics')->first()->id ?? $categories->first()->id,
                'is_active' => true,
                'is_public' => true,
                'created_by' => $teachers->first()->id,
            ],
            [
                'title' => 'General Science Quiz',
                'description' => 'Basic science knowledge test',
                'duration_minutes' => 45,
                'category_id' => $categories->where('name', 'Science')->first()->id ?? $categories->first()->id,
                'is_active' => true,
                'is_public' => false,
                'created_by' => $teachers->first()->id,
            ],
        ];

        foreach ($tests as $test) {
            Test::create($test);
        }
    }
}
