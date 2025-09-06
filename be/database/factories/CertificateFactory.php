<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $score = $this->faker->numberBetween(70, 100);
        $passingScore = $this->faker->numberBetween(60, 80);
        $completedAt = $this->faker->dateTimeBetween('-1 year', 'now');
        $issuedAt = $this->faker->dateTimeBetween($completedAt, 'now');

        return [
            'user_id' => User::factory(),
            'test_id' => Test::factory(),
            'test_attempt_id' => TestAttempt::factory(),
            'certificate_number' => 'CERT-' . date('Y') . '-' . strtoupper($this->faker->bothify('??######')),
            'user_name' => $this->faker->name(),
            'test_title' => $this->faker->sentence(3),
            'score' => $score,
            'passing_score' => $passingScore,
            'completed_at' => $completedAt,
            'issued_at' => $issuedAt,
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('now', '+2 years'),
            'certificate_template' => $this->faker->randomElement(['default', 'modern', 'classic', 'professional']),
            'metadata' => [
                'total_questions' => $this->faker->numberBetween(10, 50),
                'correct_answers' => $this->faker->numberBetween(7, 45),
                'test_duration' => $this->faker->numberBetween(30, 120),
                'category' => $this->faker->randomElement(['Programming', 'Mathematics', 'Science', 'Language']),
                'issued_by_system' => true,
            ],
            'is_valid' => true,
            'revoked_at' => null,
            'revoked_reason' => null,
        ];
    }

    /**
     * Indicate that the certificate is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
            'revoked_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'revoked_reason' => $this->faker->randomElement([
                'Test content modified',
                'User request',
                'Fraudulent activity detected',
                'Administrative decision'
            ]),
        ]);
    }

    /**
     * Indicate that the certificate is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the certificate never expires.
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
