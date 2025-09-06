<?php

namespace App\Services;

use App\Models\TestAttempt;
use App\Models\Test;
use App\Models\User;
use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use Carbon\Carbon;

class AttemptService
{
    /**
     * Start test attempt
     */
    public function startAttempt(int $testId, User $user): TestAttempt
    {
        $test = Test::with('questions')->find($testId);
        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }

        // Check if test is available
        if (!$test->is_active || !$test->is_public) {
            throw new ApiException(ErrorCodes::TEST_NOT_ACTIVE, ErrorCodes::TEST_NOT_ACTIVE_MSG);
        }

        // Check if test has questions
        if ($test->questions->count() === 0) {
            throw new ApiException(ErrorCodes::TEST_HAS_NO_QUESTIONS, ErrorCodes::TEST_HAS_NO_QUESTIONS_MSG);
        }

        // Check if user already has an in-progress attempt
        $existingAttempt = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $testId)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            throw new ApiException(ErrorCodes::TEST_ALREADY_STARTED, ErrorCodes::TEST_ALREADY_STARTED_MSG);
        }

        // Create new attempt
        return TestAttempt::create([
            'user_id' => $user->id,
            'test_id' => $testId,
            'started_at' => Carbon::now(),
            'total_questions' => $test->questions->count(),
            'status' => 'in_progress',
        ]);
    }

    public function submitTestAttempt(int $attemptId, array $answers, User $user): TestAttempt
    {
        $attempt = TestAttempt::with('test.questions')->find($attemptId);
        if (!$attempt) {
            throw new ApiException(ErrorCodes::ATTEMPT_NOT_FOUND, ErrorCodes::ATTEMPT_NOT_FOUND_MSG);
        }

        // Check ownership
        if ($attempt->user_id !== $user->id) {
            throw new ApiException(ErrorCodes::INSUFFICIENT_PERMISSIONS, ErrorCodes::INSUFFICIENT_PERMISSIONS_MSG);
        }

        // Check if already completed
        if ($attempt->status === 'completed') {
            throw new ApiException(ErrorCodes::TEST_ALREADY_COMPLETED, ErrorCodes::TEST_ALREADY_COMPLETED_MSG);
        }

        // Check time limit
        $timeLimit = $attempt->test->duration_minutes;
        if ($timeLimit && $attempt->started_at->addMinutes($timeLimit)->isPast()) {
            $attempt->update(['status' => 'expired']);
            throw new ApiException(ErrorCodes::TEST_TIME_EXPIRED, ErrorCodes::TEST_TIME_EXPIRED_MSG);
        }

        // Calculate score
        $score = $this->calculateTestScore($attempt->test, $answers);

        // Update attempt
        $attempt->update([
            'completed_at' => Carbon::now(),
            'answers' => $answers,
            'score' => $score['score'],
            'correct_answers' => $score['correct_answers'],
            'status' => 'completed',
        ]);

        return $attempt->fresh();
    }

    /**
     * Get user attempts
     */
    public function getUserAttempts(User $user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return TestAttempt::with(['test', 'test.category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get test attempts (for teachers/admins)
     */
    public function getTestAttempts(int $testId, User $user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $test = Test::find($testId);
        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }

        // Check permissions
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::INSUFFICIENT_PERMISSIONS, ErrorCodes::INSUFFICIENT_PERMISSIONS_MSG);
        }

        return TestAttempt::with(['user', 'test'])
            ->where('test_id', $testId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    private function calculateTestScore(Test $test, array $answers): array
    {
        $totalQuestions = $test->questions->count();
        $correctAnswers = 0;
        $totalPoints = 0;
        $earnedPoints = 0;

        $answersByQuestionId = collect($answers)
            ->keyBy('question_id')
            ->map(fn ($item) => collect($item['selected_option_ids'] ?? [])->sort()->values());

        foreach ($test->questions as $question) {
            $totalPoints += $question->points ?? 1;

            $userSelected = $answersByQuestionId->get($question->id, collect());
            $correctOptionIds = collect($question->getCorrectOptionIds())->sort()->values();

            if ($userSelected->all() === $correctOptionIds->all()) {
                $correctAnswers++;
                $earnedPoints += $question->points ?? 1;
            }
        }

        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

        return [
            'score' => round($score, 2),
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'earned_points' => $earnedPoints,
            'total_points' => $totalPoints,
        ];
    }

}
