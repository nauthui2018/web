<?php

namespace App\Services;

use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class TestService
{
    /**
     * Get paginated tests
     */
    public function getPaginatedTests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Test::with(['category', 'creator'])
            ->withCount('questions');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create test
     */
    public function createTest(array $data): Test
    {
        return Test::create($data);
    }

    /**
     * Create test with questions in a single transaction
     */
    public function createTestWithQuestions(array $testData, array $questionsData, User $user): Test
    {
        return DB::transaction(function () use ($testData, $questionsData, $user) {
            // Create the test
            $test = Test::create($testData);

            // Create questions for the test
            foreach ($questionsData as $index => $questionData) {
                $questionData['test_id'] = $test->id;
                $questionData['order'] = $questionData['order'] ?? ($index + 1);

                // Validate question ownership through test ownership
                $test->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'],
                    'points' => $questionData['points'],
                    'order' => $questionData['order']
                ]);
            }

            return $test->load(['questions', 'category', 'creator']);
        });
    }

    /**
     * Update test
     */
    public function updateTest(int $testId, array $data, User $user): Test
    {
        $test = $this->findTestById($testId);

        // Check ownership
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::CANNOT_EDIT_OTHER_USER_TEST, ErrorCodes::CANNOT_EDIT_OTHER_USER_TEST_MSG);
        }

        $test->update($data);
        return $test->fresh();
    }

    public function updateTestAndQuestions(int $testId, array $data, User $user): Test
    {
        $questionsData = $data['questions'];
        unset($data['questions']);

        return DB::transaction(function () use ($testId, $data, $questionsData, $user) {
            $test = $this->findTestById($testId);

            if (!$user->isAdmin() && $test->created_by !== $user->id) {
                throw new ApiException(ErrorCodes::CANNOT_EDIT_OTHER_USER_TEST, ErrorCodes::CANNOT_EDIT_OTHER_USER_TEST_MSG);
            }

            // Update test basic info
            $test->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'category_id' => $data['category_id'],
                'duration_minutes' => $data['duration_minutes'],
                'is_active' => $data['is_active'],
                'is_public' => $data['is_public'],
                'passing_score' => $data['passing_score'] ?? null,
                'show_correct_answer' => $data['show_correct_answer'] ?? false,
                'difficulty_level' => $data['difficulty_level'] ?? 'Beginner'
            ]);

            // Handle questions - delete all existing questions first
            $test->questions()->delete();

            // Create all questions as new
            foreach ($questionsData as $index => $questionData) {
                $questionData['test_id'] = $test->id;
                $questionData['order'] = $questionData['order'] ?? ($index + 1);

                // Validate question ownership through test ownership
                $test->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'],
                    'points' => $questionData['points'],
                    'order' => $questionData['order']
                ]);
            }

            return $test->load(['questions', 'category']);
        });
    }

    /**
     * Delete test
     */
    public function deleteTest(int $testId, User $user): bool
    {
        $test = $this->findTestById($testId);

        // Check ownership
        if (!$user->isAdmin() && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::CANNOT_DELETE_OTHER_USER_TEST, ErrorCodes::CANNOT_DELETE_OTHER_USER_TEST_MSG);
        }

        // Check if test has attempts
        if ($test->attempts()->exists() && !$user->isAdmin()) {
            throw new ApiException(ErrorCodes::CANNOT_DELETE_PUBLISHED_TEST, ErrorCodes::CANNOT_DELETE_PUBLISHED_TEST_MSG);
        }

        return $test->forceDelete();
    }

    /**
     * Get test by ID
     */
    public function getTestById(int $testId, bool $publicOnly = false): Test
    {
        if ($publicOnly) {
            $test = Test::query()
            ->with(['category', 'questions'])
            ->where('id', $testId)
            ->first();
            if (!$test) {
                throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
            }
            return $test;
        }

        return $this->findTestById($testId);
    }

    /**
     * Get available tests for users
     */
    public function getAvailableTests(int $perPage = 15): LengthAwarePaginator
    {
        return Test::with(['category', 'creator'])
            ->available()
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get test with questions
     */
    public function getTestWithQuestions(int $testId): Test
    {
        $test = $this->findTestById($testId);
        $test->load('questions');
        $test->load('category');
        return $test;
    }

    /**
     * Check if user can take test
     */
    public function canUserTakeTest(int $testId, User $user): bool
    {
        $test = $this->findTestById($testId);

        if (!$test->is_active) {
            throw new ApiException(ErrorCodes::TEST_NOT_ACTIVE, ErrorCodes::TEST_NOT_ACTIVE_MSG);
        }

        if (!$test->is_public) {
            throw new ApiException(ErrorCodes::TEST_NOT_PUBLIC, ErrorCodes::TEST_NOT_PUBLIC_MSG);
        }

        // Check if user has already started the test
        $existingAttempt = $test->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            throw new ApiException(ErrorCodes::TEST_ALREADY_STARTED, ErrorCodes::TEST_ALREADY_STARTED_MSG);
        }

        return true;
    }

    /**
     * Get test analytics
     */
    public function getTestAnalytics(int $testId, User $user): array
    {
        $test = $this->findTestById($testId);

        // Check if user can access this test's analytics
        if ($user->role !== 'admin' && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::FORBIDDEN, ErrorCodes::FORBIDDEN_MSG);
        }

        $totalAttempts = $test->attempts()->count();
        $completedAttempts = $test->attempts()->where('status', 'completed')->count();
        $averageScore = $test->attempts()->where('status', 'completed')->avg('score') ?? 0;
        $highestScore = $test->attempts()->where('status', 'completed')->max('score') ?? 0;
        $lowestScore = $test->attempts()->where('status', 'completed')->min('score') ?? 0;

        return [
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'average_score' => round($averageScore, 2),
            'highest_score' => $highestScore,
            'lowest_score' => $lowestScore,
        ];
    }

    /**
     * Get detailed test analytics with user list and scores
     */
    public function getDetailedTestAnalytics(int $testId, User $user, int $perPage = 15): array
    {
        $test = $this->findTestById($testId);

        // Check if user can access this test's analytics
        if ($user->role !== 'admin' && $test->created_by !== $user->id) {
            throw new ApiException(ErrorCodes::FORBIDDEN, ErrorCodes::FORBIDDEN_MSG);
        }

        // Get summary statistics
        $totalAttempts = $test->attempts()->count();
        $completedAttempts = $test->attempts()->where('status', 'completed')->count();
        $inProgressAttempts = $test->attempts()->where('status', 'in_progress')->count();
        $averageScore = $test->attempts()->where('status', 'completed')->avg('score') ?? 0;
        $highestScore = $test->attempts()->where('status', 'completed')->max('score') ?? 0;
        $lowestScore = $test->attempts()->where('status', 'completed')->min('score') ?? 0;
        $passingAttempts = $test->attempts()
            ->where('status', 'completed')
            ->where('score', '>=', $test->passing_score ?? 0)
            ->count();
        $failingAttempts = $test->attempts()
            ->where('status', 'completed')
            ->where('score', '<', $test->passing_score ?? 0)
            ->count();

        // Get unique users who took the test
        $uniqueUsers = $test->attempts()
            ->where('status', 'completed')
            ->distinct('user_id')
            ->count();

        // Get detailed user attempts with pagination
        $userAttempts = $test->attempts()
            ->with(['user:id,name,email'])
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate($perPage);

        // Transform the attempts data
        $attemptsData = $userAttempts->getCollection()->map(function ($attempt) use ($test) {
            return [
                'user_id' => $attempt->user_id,
                'user_name' => $attempt->user->name,
                'user_email' => $attempt->user->email,
                'score' => round($attempt->score, 2),
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
                'started_at' => $attempt->started_at,
                'completed_at' => $attempt->completed_at,
                'time_taken_minutes' => $attempt->started_at && $attempt->completed_at 
                    ? round($attempt->started_at->diffInMinutes($attempt->completed_at), 2)
                    : null,
                'passed' => $attempt->score >= ($test->passing_score ?? 0),
                'percentage' => round(($attempt->correct_answers / $attempt->total_questions) * 100, 2),
            ];
        });

        return [
            'test_info' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'passing_score' => $test->passing_score,
                'total_questions' => $test->questions()->count(),
            ],
            'summary_statistics' => [
                'total_attempts' => $totalAttempts,
                'completed_attempts' => $completedAttempts,
                'in_progress_attempts' => $inProgressAttempts,
                'unique_users' => $uniqueUsers,
                'average_score' => round($averageScore, 2),
                'highest_score' => $highestScore,
                'lowest_score' => $lowestScore,
                'passing_attempts' => $passingAttempts,
                'failing_attempts' => $failingAttempts,
                'pass_rate' => $completedAttempts > 0 ? round(($passingAttempts / $completedAttempts) * 100, 2) : 0,
            ],
            'user_attempts' => [
                'data' => $attemptsData,
                'pagination' => [
                    'current_page' => $userAttempts->currentPage(),
                    'last_page' => $userAttempts->lastPage(),
                    'per_page' => $userAttempts->perPage(),
                    'total' => $userAttempts->total(),
                    'from' => $userAttempts->firstItem(),
                    'to' => $userAttempts->lastItem(),
                ]
            ]
        ];
    }

    /**
     * Get test attempts count by day for the last 15 days
     */
    public function getTestAttemptsByDay(User $user, int $days = 15): array
    {
        $endDate = now();
        $startDate = $endDate->copy()->subDays($days - 1)->startOfDay();

        // Get attempts for tests created by the user (or all if admin)
        $query = TestAttempt::query()
            ->join('tests', 'test_attempts.test_id', '=', 'tests.id');

        if ($user->role !== 'admin') {
            $query->where('tests.created_by', $user->id);
        }

        $attemptsByDay = $query
            ->whereBetween('test_attempts.created_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(test_attempts.created_at) as date,
                COUNT(*) as total_attempts,
                COUNT(CASE WHEN test_attempts.status = "completed" THEN 1 END) as completed_attempts,
                COUNT(CASE WHEN test_attempts.status = "in_progress" THEN 1 END) as in_progress_attempts,
                AVG(CASE WHEN test_attempts.status = "completed" THEN test_attempts.score END) as average_score
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Create a complete date range with all days filled (even with 0 attempts)
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->toDateString();
            $dayData = $attemptsByDay->firstWhere('date', $dateString);

            $dateRange[] = [
                'date' => $dateString,
                'day_name' => $currentDate->format('l'), // Monday, Tuesday, etc.
                'total_attempts' => $dayData ? (int) $dayData->total_attempts : 0,
                'completed_attempts' => $dayData ? (int) $dayData->completed_attempts : 0,
                'in_progress_attempts' => $dayData ? (int) $dayData->in_progress_attempts : 0,
                'average_score' => $dayData && $dayData->average_score ? round($dayData->average_score, 2) : 0,
            ];

            $currentDate->addDay();
        }

        // Calculate summary statistics
        $totalAttempts = $attemptsByDay->sum('total_attempts');
        $totalCompleted = $attemptsByDay->sum('completed_attempts');
        $totalInProgress = $attemptsByDay->sum('in_progress_attempts');
        $overallAverageScore = $attemptsByDay->where('average_score', '>', 0)->avg('average_score');

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $days,
            ],
            'summary' => [
                'total_attempts' => $totalAttempts,
                'completed_attempts' => $totalCompleted,
                'in_progress_attempts' => $totalInProgress,
                'average_score' => round($overallAverageScore ?? 0, 2),
                'completion_rate' => $totalAttempts > 0 ? round(($totalCompleted / $totalAttempts) * 100, 2) : 0,
            ],
            'daily_data' => $dateRange,
        ];
    }

    /**
     * Find test by ID
     */
    private function findTestById(int $testId): Test
    {
        $test = Test::find($testId);
        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }
        return $test;
    }

    public function getAllTestSummaryByUser(int|string|null $id, mixed $perPage): \Illuminate\Database\Eloquent\Collection|LengthAwarePaginator
    {
        $query = Test::query()
            ->with(['category'])
            ->withCount('questions')
            ->where('created_by', $id)
            ->orderBy('created_at', 'desc');

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function getTestDetailByUser(int|string|null $userId, int $testId): ?Test
    {
        return Test::query()
            ->with(['category', 'questions'])
            ->where('created_by', $userId)
            ->where('id', $testId)
            ->first();
    }

    public function getTestAnalyticSummarysByUser(int|string|null $userId): array
    {
        $tests = Test::where('created_by', $userId)->with('attempts')->get();

        $totalQuestionSets = $tests->count();
        $totalCompletions = 0;
        $totalScore = 0;
        $totalTimeSpent = 0;
        $completedAttemptsCount = 0;
        $activeUserIds = collect();

        foreach ($tests as $test) {
            $completedUserIds = $test->attempts
            ->where('status', 'completed')
            ->pluck('user_id')
            ->unique();

            $activeUserIds = $activeUserIds->merge($completedUserIds);

            $completedAttempts = $test->attempts->where('status', 'completed');
            $completions = $completedAttempts->count();

            $totalCompletions += $completions;
            $totalScore += $completedAttempts->sum('score');
            $completedAttemptsCount += $completions;

            foreach ($completedAttempts as $attempt) {
                if ($attempt->started_at && $attempt->completed_at) {
                    $startedAt = $attempt->started_at instanceof \Carbon\Carbon
                        ? $attempt->started_at
                        : \Carbon\Carbon::parse($attempt->started_at);

                    $completedAt = $attempt->completed_at instanceof \Carbon\Carbon
                        ? $attempt->completed_at
                        : \Carbon\Carbon::parse($attempt->completed_at);

                    if ($completedAt->greaterThanOrEqualTo($startedAt)) {
                        $timeSpent = $completedAt->diffInSeconds($startedAt) / 60; // minutes
                        $totalTimeSpent += $timeSpent;
                    }
                }
            }
        }

        return [
            'total_question_sets' => $totalQuestionSets,
            'total_completions' => $totalCompletions,
            'average_score' => $completedAttemptsCount > 0 ? round($totalScore / $completedAttemptsCount, 2) : 0,
            'completion_rate' => $totalQuestionSets > 0 ? round($totalCompletions / $totalQuestionSets, 2) : 0,
            'average_time_spent' => $completedAttemptsCount > 0 ? round($totalTimeSpent / $completedAttemptsCount, 2) : 0,
            'active_users' => $activeUserIds->unique()->count()
        ];
    }

    public function getCompletionTrends(int|string|null $userId, string $startDate, string $endDate): array
    {
        $results = DB::table('test_attempts')
            ->join('tests', 'test_attempts.test_id', '=', 'tests.id')
            ->where('tests.created_by', $userId)
            ->where('test_attempts.status', 'completed')
            ->whereBetween('test_attempts.completed_at', [$startDate, $endDate])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as completions, AVG(score) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format response to have all days filled (even with 0)
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $trends = [];

        while ($start->lte($end)) {
            $day = $start->toDateString();
            $entry = $results->firstWhere('date', $day);

            $trends[] = [
                'date' => $start->toDateString(), // e.g. "Jan 1"
                'completions' => $entry?->completions ?? 0,
                'avg_score' => $entry?->avg_score ? round($entry->avg_score, 2) : 0,
            ];

            $start->addDay();
        }

        return $trends;
    }

    public function getCategoryPerformance(int|string|null $userId)
    {
        $data = DB::table('categories')
            ->join('tests', 'tests.category_id', '=', 'categories.id')
            ->join('test_attempts', 'test_attempts.test_id', '=', 'tests.id')
            ->where('tests.created_by', $userId)
            ->select(
                'categories.name as category',
                DB::raw('COUNT(DISTINCT tests.id) as questionSets'),
                DB::raw('COUNT(test_attempts.id) as completions'),
                DB::raw('ROUND(AVG(test_attempts.score), 1) as averageScore')
            )
            ->groupBy('categories.name')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function getDifficultyDistribution(int|string|null $userId)
    {
        // Total tests with attempts by this user
        $totalTests = DB::table('tests')
            ->join('test_attempts', 'tests.id', '=', 'test_attempts.test_id')
            ->where('tests.created_by', $userId)
            ->distinct('tests.id')
            ->count('tests.id');

        $results = DB::table('tests')
            ->join('test_attempts', 'tests.id', '=', 'test_attempts.test_id')
            ->select(
                'tests.difficulty_level as difficulty',
                DB::raw('COUNT(DISTINCT tests.id) as count'),
                DB::raw('ROUND(AVG(test_attempts.score), 1) as averageScore')
            )
            ->where('tests.created_by', $userId)
            ->groupBy('tests.difficulty_level')
            ->get()
            ->map(function ($item) use ($totalTests) {
                $item->percentage = $totalTests > 0
                    ? round(($item->count / $totalTests) * 100, 1)
                    : 0;
                return $item;
            });

        return response()->json([
            'success' => true,
            'message' => 'Difficulty distribution retrieved successfully',
            'data' => $results,
            'code' => 200,
        ]);
    }

    public function getScoreDistribution(int|string|null $userId)
    {
        // Step 1: Get average score per user across all attempts on this user's tests
        $userAverages = DB::table('tests')
            ->join('test_attempts', 'tests.id', '=', 'test_attempts.test_id')
            ->select('test_attempts.user_id', DB::raw('AVG(test_attempts.score) as avg_score'))
            ->where('tests.created_by', $userId)
            ->groupBy('test_attempts.user_id')
            ->get();

        // Step 2: Define ranges
        $ranges = [
            '90-100%' => [90, 100],
            '80-89%' => [80, 89.99],
            '70-79%' => [70, 79.99],
            '60-69%' => [60, 69.99],
            '50-59%' => [50, 59.99],
            'Below 50%' => [0, 49.99],
        ];

        // Step 3: Initialize result structure
        $distribution = [];
        foreach ($ranges as $label => $_) {
            $distribution[$label] = 0;
        }

        // Step 4: Bucket users into score ranges
        foreach ($userAverages as $user) {
            foreach ($ranges as $label => [$min, $max]) {
                if ($user->avg_score >= $min && $user->avg_score <= $max) {
                    $distribution[$label]++;
                    break;
                }
            }
        }

        $totalUsers = count($userAverages);

        // Step 5: Build final result
        $result = [];
        foreach ($distribution as $range => $count) {
            $result[] = [
                'range' => $range,
                'count' => $count,
                'percentage' => $totalUsers > 0 ? round(($count / $totalUsers) * 100, 1) : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Score distribution retrieved successfully',
            'data' => $result,
            'code' => 200,
        ]);
    }

}
