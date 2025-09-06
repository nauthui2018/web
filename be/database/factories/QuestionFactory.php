<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionType = $this->faker->randomElement(['multiple_choice', 'true_false', 'short_answer']);
        
        return [
            'test_id' => Test::factory(),
            'question_text' => $this->faker->sentence() . '?',
            'question_type' => $questionType,
            'options' => $this->generateOptions($questionType),
            'correct_answer' => $this->generateCorrectAnswer($questionType),
            'points' => $this->faker->numberBetween(1, 10),
            'order' => $this->faker->numberBetween(1, 20),
        ];
    }

    /**
     * Generate options based on question type.
     */
    private function generateOptions(string $questionType): array
    {
        switch ($questionType) {
            case 'multiple_choice':
                return [
                    'A' => $this->faker->sentence(4),
                    'B' => $this->faker->sentence(4),
                    'C' => $this->faker->sentence(4),
                    'D' => $this->faker->sentence(4),
                ];
            case 'true_false':
                return [
                    'True' => 'True',
                    'False' => 'False',
                ];
            case 'short_answer':
                return [];
            default:
                return [];
        }
    }

    /**
     * Generate correct answer based on question type.
     */
    private function generateCorrectAnswer(string $questionType): string
    {
        switch ($questionType) {
            case 'multiple_choice':
                return $this->faker->randomElement(['A', 'B', 'C', 'D']);
            case 'true_false':
                return $this->faker->randomElement(['True', 'False']);
            case 'short_answer':
                return $this->faker->words(3, true);
            default:
                return 'A';
        }
    }

    /**
     * Create a multiple choice question.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'multiple_choice',
            'options' => [
                'A' => $this->faker->sentence(4),
                'B' => $this->faker->sentence(4),
                'C' => $this->faker->sentence(4),
                'D' => $this->faker->sentence(4),
            ],
            'correct_answer' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
        ]);
    }

    /**
     * Create a true/false question.
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'true_false',
            'options' => [
                'True' => 'True',
                'False' => 'False',
            ],
            'correct_answer' => $this->faker->randomElement(['True', 'False']),
        ]);
    }

    /**
     * Create a short answer question.
     */
    public function shortAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'short_answer',
            'options' => [],
            'correct_answer' => $this->faker->words(3, true),
        ]);
    }

    /**
     * Set the test for this question.
     */
    public function forTest(Test $test): static
    {
        return $this->state(fn (array $attributes) => [
            'test_id' => $test->id,
        ]);
    }

    /**
     * Set the order for this question.
     */
    public function order(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }

    /**
     * Set the points for this question.
     */
    public function points(int $points): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => $points,
        ]);
    }
}
