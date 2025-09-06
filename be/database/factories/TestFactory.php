<?php

namespace Database\Factories;

use App\Models\Test;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Test>
 */
class TestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'duration_minutes' => $this->faker->numberBetween(15, 180), // 15 minutes to 3 hours
            'category_id' => Category::factory(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_public' => $this->faker->boolean(70), // 70% chance of being public
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the test is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the test is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the test is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the test is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the test is available (active and public).
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_public' => true,
        ]);
    }

    /**
     * Indicate the test creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Indicate the test category.
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Create a short test (15-30 minutes).
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => $this->faker->numberBetween(15, 30),
        ]);
    }

    /**
     * Create a long test (2-3 hours).
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => $this->faker->numberBetween(120, 180),
        ]);
    }
}
