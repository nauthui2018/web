<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Test\CreateTestRequest;
use App\Http\Requests\Question\CreateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Requests\Test\UpdateTestAndQuestionsRequest;
use App\Http\Resources\TestResource;
use App\Http\Resources\AttemptResource;
use App\Services\TestService;
use App\Services\QuestionService;
use App\Services\AttemptService;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\QuestionResource;

class TeacherController extends Controller
{
    protected TestService $testService;
    protected QuestionService $questionService;
    protected AttemptService $attemptService;

    public function __construct(
        TestService $testService,
        QuestionService $questionService,
        AttemptService $attemptService
    ) {
        $this->testService = $testService;
        $this->questionService = $questionService;
        $this->attemptService = $attemptService;
    }

    /**
     * List teacher's tests
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category_id', 'is_active', 'is_public', 'search']);
        $filters['created_by'] = Auth::id();
        $perPage = $request->get('per_page', 15);

        $tests = $this->testService->getPaginatedTests($filters, $perPage);

        // Transform the collection while preserving pagination
        $tests->getCollection()->transform(function($test) {
            return new TestResource($test);
        });

        return ResponseHelper::paginated(
            $tests,
            'Tests retrieved successfully'
        );
    }

    /**
     * Create new test
     */
    public function store(CreateTestRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $test = $this->testService->createTest($data);

        return ResponseHelper::success(
            new TestResource($test),
            'Test created successfully',
            201
        );
    }

    /**
     * Get specific test
     */
    public function show(int $test): JsonResponse
    {
        $testModel = $this->testService->getTestWithQuestions($test);

        return ResponseHelper::success(
            new TestResource($testModel),
            'Test retrieved successfully'
        );
    }

    /**
     * Update test
     * @throws ApiException
     */
    public function update(UpdateTestAndQuestionsRequest $request, int $test): JsonResponse
    {
        $testModel = $this->testService->updateTest($test, $request->validated(), Auth::user());

        return ResponseHelper::success(
            new TestResource($testModel),
            'Test updated successfully'
        );
    }

    /**
     * Delete test
     * @throws ApiException
     */
    public function destroy(int $test): JsonResponse
    {
        $this->testService->deleteTest($test, Auth::user());

        return ResponseHelper::success(null, 'Test deleted successfully');
    }

    /**
     * Add question to test
     * @throws ApiException
     */
    public function addQuestion(CreateQuestionRequest $request, int $testId): JsonResponse
    {
        $data = $request->validated();
        $data['test_id'] = $testId;

        $question = $this->questionService->createQuestion($data, Auth::user());

        return ResponseHelper::success(
            new QuestionResource($question),
            'Question added successfully',
            201
        );
    }

    /**
     * Update question
     */
    public function updateQuestion(UpdateQuestionRequest $request, int $testId, int $questionId)
    {
        $question = $this->questionService->updateQuestion($questionId, $request->validated(), Auth::user());

        return ResponseHelper::success(
            new QuestionResource($question),
            'Question updated successfully'
        );
    }

    /**
     * Delete question
     */
    public function deleteQuestion(int $testId, int $questionId)
    {
        $this->questionService->deleteQuestion($questionId, Auth::user());

        return ResponseHelper::success(null, 'Question deleted successfully');
    }

    /**
     * Get test questions
     */
    public function getTestQuestions(int $testId)
    {
        $questions = $this->questionService->getTestQuestions($testId);

        return ResponseHelper::success(
            QuestionResource::collection($questions),
            'Questions retrieved successfully'
        );
    }

    /**
     * Reorder questions
     */
    public function reorderQuestions(Request $request, int $testId)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'integer|exists:questions,id'
        ]);

        $this->questionService->reorderQuestions($testId, $request->question_ids, Auth::user());

        return ResponseHelper::success(null, 'Questions reordered successfully');
    }

    /**
     * Get test attempts
     */
    public function getTestAttempts(Request $request, int $testId)
    {
        $perPage = $request->get('per_page', 15);

        $attempts = $this->attemptService->getTestAttempts($testId, Auth::user(), $perPage);

        // Transform the collection while preserving pagination
        $attempts->getCollection()->transform(function($attempt) {
            return new AttemptResource($attempt);
        });

        return ResponseHelper::paginated(
            $attempts,
            'Test attempts retrieved successfully'
        );
    }

    /**
     * Get test analytics
     */
    public function getTestAnalytics(int $testId)
    {
        $analytics = $this->testService->getTestAnalytics($testId, Auth::user());

        return ResponseHelper::success($analytics, 'Test analytics retrieved successfully');
    }

    /**
     * Get detailed test analytics with user list and scores
     */
    public function getDetailedTestAnalytics(Request $request, int $testId)
    {
        $perPage = $request->get('per_page', 15);
        $analytics = $this->testService->getDetailedTestAnalytics($testId, Auth::user(), $perPage);

        return ResponseHelper::success($analytics, 'Detailed test analytics retrieved successfully');
    }
}
