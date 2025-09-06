<?php

namespace Database\Factories;

use App\Models\TestAttempt;
use App\Models\Test;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestAttempt>
 */
class TestAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['in_progress', 'completed', 'expired']);
        $totalQuestions = $this->faker->numberBetween(5, 20);
        $correctAnswers = $status === 'completed' ? $this->faker->numberBetween(0, $totalQuestions) : 0;
        $score = $status === 'completed' ? ($correctAnswers / $totalQuestions) * 100 : 0;

        $startedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $completedAt = $status === 'completed' ? 
            $this->faker->dateTimeBetween($startedAt, 'now') : 
            null;

        return [
            'user_id' => User::factory(),
            'test_id' => Test::factory(),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'score' => round($score, 2),
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'answers' => $this->generateAnswers($totalQuestions),
            'status' => $status,
            'attempt_type' => $this->faker->randomElement(['normal', 'preview']),
        ];
    }

    /**
     * Generate sample answers.
     */
    private function generateAnswers(int $totalQuestions): array
    {
        $answers = [];
        for ($i = 1; $i <= $totalQuestions; $i++) {
            $answers[$i] = $this->faker->randomElement(['A', 'B', 'C', 'D', 'True', 'False']);
        }
        return $answers;
    }

    /**
     * Create a completed attempt.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $totalQuestions = $attributes['total_questions'] ?? 10;
            $correctAnswers = $this->faker->numberBetween(0, $totalQuestions);
            $score = ($correctAnswers / $totalQuestions) * 100;
            
            return [
                'status' => 'completed',
                'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'] ?? '-1 day', 'now'),
                'score' => round($score, 2),
                'correct_answers' => $correctAnswers,
                'answers' => $this->generateAnswers($totalQuestions),
            ];
        });
    }

    /**
     * Create an in-progress attempt.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completed_at' => null,
            'score' => 0,
            'correct_answers' => 0,
            'answers' => [],
        ]);
    }

    /**
     * Create an expired attempt.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'completed_at' => null,
            'score' => 0,
            'correct_answers' => 0,
        ]);
    }

    /**
     * Set the user for this attempt.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set the test for this attempt.
     */
    public function forTest(Test $test): static
    {
        return $this->state(fn (array $attributes) => [
            'test_id' => $test->id,
        ]);
    }

    /**
     * Create a high score attempt.
     */
    public function highScore(): static
    {
        return $this->state(function (array $attributes) {
            $totalQuestions = $attributes['total_questions'] ?? 10;
            $correctAnswers = $this->faker->numberBetween(
                (int) ceil($totalQuestions * 0.8), 
                $totalQuestions
            );
            $score = ($correctAnswers / $totalQuestions) * 100;
            
            return [
                'status' => 'completed',
                'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'] ?? '-1 day', 'now'),
                'score' => round($score, 2),
                'correct_answers' => $correctAnswers,
                'answers' => $this->generateAnswers($totalQuestions),
            ];
        });
    }

    /**
     * Create a low score attempt.
     */
    public function lowScore(): static
    {
        return $this->state(function (array $attributes) {
            $totalQuestions = $attributes['total_questions'] ?? 10;
            $correctAnswers = $this->faker->numberBetween(
                0, 
                (int) floor($totalQuestions * 0.4)
            );
            $score = ($correctAnswers / $totalQuestions) * 100;
            
            return [
                'status' => 'completed',
                'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'] ?? '-1 day', 'now'),
                'score' => round($score, 2),
                'correct_answers' => $correctAnswers,
                'answers' => $this->generateAnswers($totalQuestions),
            ];
        });
    }

    /**
     * Set the attempt type.
     */
    public function practiceType(): static
    {
        return $this->state(fn (array $attributes) => [
            'attempt_type' => 'practice',
        ]);
    }

    /**
     * Set the attempt type as exam.
     */
    public function examType(): static
    {
        return $this->state(fn (array $attributes) => [
            'attempt_type' => 'exam',
        ]);
    }
}
