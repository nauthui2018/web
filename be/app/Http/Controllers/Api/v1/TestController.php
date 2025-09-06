<?php

namespace App\Http\Controllers\Api\v1;

use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Test\CreateTestWithQuestionsRequest;
use App\Http\Requests\Test\UpdateTestAndQuestionsRequest;
use App\Http\Resources\TestResource;
use App\Http\Resources\AttemptResource;
use App\Services\TestService;
use App\Services\AttemptService;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    protected TestService $testService;
    protected AttemptService $attemptService;

    public function __construct(TestService $testService, AttemptService $attemptService)
    {
        $this->testService = $testService;
        $this->attemptService = $attemptService;
    }

    /* ==================================QUIZ TAKEN SECTION================================== */
    /**
     * Get available tests
     */
    public function availableTests(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        $tests = $this->testService->getAvailableTests($perPage);

        // Transform the data while preserving pagination
        $tests->getCollection()->transform(function($test) {
            return new TestResource($test);
        });

        return ResponseHelper::paginated(
            $tests,
            'Available tests retrieved successfully'
        );
    }

    /**
     * Get test details
     * @throws ApiException
     */
    public function show(int $id): JsonResponse
    {
        $test = $this->testService->getTestById($id, true); // Public access only

        return ResponseHelper::success(
            new TestResource($test),
            'Test details retrieved successfully'
        );
    }

    /**
     * Start test attempt
     * @throws ApiException
     */
    public function startTest(int $id): JsonResponse
    {
        $attempt = $this->attemptService->startAttempt($id, Auth::user());

        return ResponseHelper::success(
            new AttemptResource($attempt),
            'Test started successfully'
        );
    }

    /**
     * Submit test answers
     * @throws ApiException
     */
    public function submitTestAttempt(Request $request, int $attemptId): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.selected_option_ids' => 'required|array|min:1',
            'answers.*.selected_option_ids.*' => 'integer',
        ]);
        $attempt = $this->attemptService->submitTestAttempt($attemptId, $validated['answers'], Auth::user());

        return ResponseHelper::success(
            new AttemptResource($attempt),
            'Test submitted successfully'
        );
    }

    /**
     * Get test with questions for taking
     * @throws ApiException
     */
    public function getTestForTaking(int $id): JsonResponse
    {
        $this->testService->canUserTakeTest($id, Auth::user());
        $test = $this->testService->getTestWithQuestions($id);

        return ResponseHelper::success(
            ['test' => new TestResource($test)],
            'Test questions retrieved successfully'
        );
    }

    /* ==================================TEST MANAGEMENT SECTION================================== */

    /**
     * Create a public test with questions
     */
    public function createTestWithQuestions(CreateTestWithQuestionsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $questions = $data['questions'];
        unset($data['questions']);

        // Set created_by to current user
        $data['created_by'] = Auth::id();

        $test = $this->testService->createTestWithQuestions($data, $questions, Auth::user());

        return ResponseHelper::success(
            new TestResource($test->load('questions')),
            'Test with questions created successfully',
            201
        );
    }

    /**
     * Update test
     * @throws ApiException
     */
    public function updateTestAndQuestions(UpdateTestAndQuestionsRequest $request, int $testId): JsonResponse
    {
        $test = $this->testService->updateTestAndQuestions($testId, $request->validated(), Auth::user());

        return ResponseHelper::success(
            new TestResource($test),
            'Test updated successfully'
        );
    }

    public function getTestsCreatedByUser(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $tests = $this->testService->getAllTestSummaryByUser(Auth::id(), $perPage);

        // Transform the data while preserving pagination
        $tests->getCollection()->transform(function($test) {
            return new TestResource($test);
        });

        return ResponseHelper::paginated(
            $tests,
            'All tests summary retrieved successfully'
        );
    }

    /**
     * @throws ApiException
     */
    public function getTestDetail(int $testId): JsonResponse
    {
        $test = $this->testService->getTestDetailByUser(Auth::id(), $testId);

        if (!$test) {
            throw new ApiException(ErrorCodes::TEST_NOT_FOUND, ErrorCodes::TEST_NOT_FOUND_MSG);
        }

        return ResponseHelper::success(
            new TestResource($test),
            'Test details retrieved successfully'
        );
    }

    public function deleteTest(int $testId): JsonResponse
    {
        $this->testService->deleteTest($testId, Auth::User());

        return ResponseHelper::success(
            null,
            'Test deleted successfully'
        );
    }

    /* ==================================ANALYTICS AND STATISTICS SECTION================================== */
    public function getYourCreatedTestAnalyticsSummary(Request $request): JsonResponse
    {
        $summary = $this->testService->getTestAnalyticSummarysByUser(Auth::id());

        return ResponseHelper::success(
            $summary,
            'Your created test analytics retrieved successfully'
        );
    }

    public function getCompletionTrend(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subYear()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $trends = $this->testService->getCompletionTrends(Auth::id(), $startDate, $endDate);

        return ResponseHelper::success(
            $trends,
            'Completion trends retrieved successfully'
        );
    }

    public function getCategoryPerformance(Request $request): JsonResponse
    {
        $performance = $this->testService->getCategoryPerformance(Auth::id());

        return ResponseHelper::success(
            $performance,
            'Category performance retrieved successfully'
        );
    }

    public function getDifficultyDistribution(Request $request): JsonResponse
    {
        $distribution = $this->testService->getDifficultyDistribution(Auth::id());

        return ResponseHelper::success(
            $distribution,
            'Difficulty distribution retrieved successfully'
        );
    }

    public function getScoreDistribution(Request $request): JsonResponse
    {
        $distribution = $this->testService->getScoreDistribution(Auth::id());

        return ResponseHelper::success(
            $distribution,
            'Score distribution retrieved successfully'
        );
    }

    public function getDetailedTestAnalytics(Request $request, int $testId): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $analytics = $this->testService->getDetailedTestAnalytics($testId, Auth::user(), $perPage);

        return ResponseHelper::success(
            $analytics,
            'Detailed test analytics retrieved successfully'
        );
    }

    public function getTestAttemptsByDay(Request $request): JsonResponse
    {
        $days = $request->get('days', 15);
        $analytics = $this->testService->getTestAttemptsByDay(Auth::user(), $days);

        return ResponseHelper::success(
            $analytics,
            'Test attempts by day retrieved successfully'
        );
    }

}
