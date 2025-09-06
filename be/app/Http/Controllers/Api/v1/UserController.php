<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\AttemptResource;
use App\Http\Resources\TestResource;
use App\Services\UserService;
use App\Services\AttemptService;
use App\Services\TestService;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Test\CreateTestWithQuestionsRequest;

class UserController extends Controller
{
    protected UserService $userService;
    protected AttemptService $attemptService;
    protected TestService $testService;

    public function __construct(UserService $userService, AttemptService $attemptService, TestService $testService)
    {
        $this->userService = $userService;
        $this->attemptService = $attemptService;
        $this->testService = $testService;
    }

    /**
     * Get user profile
     */
    public function profile(): JsonResponse
    {
        return ResponseHelper::success(
            new UserResource(Auth::user()),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update profile
     * @throws ApiException
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $user = $this->userService->updateUser(Auth::id(), $request->only(['name', 'phone']));

        return ResponseHelper::success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Change password
     * @throws ApiException
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->userService->updatePassword(
            Auth::id(),
            $request->current_password,
            $request->new_password
        );

        return ResponseHelper::success(null, 'Password updated successfully');
    }

    /**
     * Get user attempts
     */
    public function myAttempts(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        $attempts = $this->attemptService->getUserAttempts(Auth::user(), $perPage);

        return ResponseHelper::paginated(
            $attempts->through(fn($attempt) => new AttemptResource($attempt)),
            'Your attempts retrieved successfully'
        );
    }

    /**
     * Get user activity summary
     */
    public function activitySummary(): JsonResponse
    {
        $summary = $this->userService->getUserActivitySummary(Auth::id());

        return ResponseHelper::success($summary, 'Activity summary retrieved successfully');
    }

}
