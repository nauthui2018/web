<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Test;
use App\Models\User;
use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;

class QuestionService
{
    /**
     * Create question
     */
    public function createQuestion(array $data, User $user): Question
    {
        $test = Test::find($data['test_id']);
        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }

        // Check ownership
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::INSUFFICIENT_PERMISSIONS, ErrorCodes::INSUFFICIENT_PERMISSIONS_MSG);
        }

        // Set order if not provided
        if (!isset($data['order'])) {
            $data['order'] = $test->questions()->max('order') + 1;
        }

        return Question::create($data);
    }

    /**
     * Update question
     */
    public function updateQuestion(int $questionId, array $data, User $user): Question
    {
        $question = $this->findQuestionById($questionId);
        $test = $question->test;

        // Check ownership
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::INSUFFICIENT_PERMISSIONS, ErrorCodes::INSUFFICIENT_PERMISSIONS_MSG);
        }

        // Check if test has attempts (prevent modification)
        if ($test->attempts()->exists() && !$user->isAdmin()) {
            throw new ApiException(ErrorCodes::CANNOT_MODIFY_PUBLISHED_TEST, ErrorCodes::CANNOT_MODIFY_PUBLISHED_TEST_MSG);
        }

        $question->update($data);
        return $question->fresh();
    }

    /**
     * Delete question
     */
    public function deleteQuestion(int $questionId, User $user): bool
    {
        $question = $this->findQuestionById($questionId);
        $test = $question->test;

        // Check ownership
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::INSUFFICIENT_PERMISSIONS, ErrorCodes::INSUFFICIENT_PERMISSIONS_MSG);
        }

        // Check if test has attempts
        if ($test->attempts()->exists() && !$user->isAdmin()) {
            throw new ApiException(ErrorCodes::CANNOT_MODIFY_PUBLISHED_TEST, ErrorCodes::CANNOT_MODIFY_PUBLISHED_TEST_MSG);
        }

        return $question->forceDelete();
    }

    /**
     * Get questions for test
     */
    public function getTestQuestions(int $testId): \Illuminate\Database\Eloquent\Collection
    {
        $test = Test::find($testId);
        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }

        return $test->questions()->orderBy('order')->get();
    }

    /**
     * Reorder questions
     */
    public function reorderQuestions(int $testId, array $questionIds, User $user): bool
    {
        $test = Test::find($testId);
        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }

        // Check ownership
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::INSUFFICIENT_PERMISSIONS, ErrorCodes::INSUFFICIENT_PERMISSIONS_MSG);
        }

        foreach ($questionIds as $index => $questionId) {
            Question::where('id', $questionId)
                ->where('test_id', $testId)
                ->update(['order' => $index + 1]);
        }

        return true;
    }

    /**
     * Find question by ID
     */
    private function findQuestionById(int $questionId): Question
    {
        $question = Question::with('test')->find($questionId);
        if (!$question) {
            throw new ApiException(ErrorCodes::QUESTION_NOT_FOUND, ErrorCodes::QUESTION_NOT_FOUND_MSG);
        }
        return $question;
    }
}