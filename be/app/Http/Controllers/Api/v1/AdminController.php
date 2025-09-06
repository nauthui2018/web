<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\TestResource;
use App\Services\UserService;
use App\Services\TestService;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected UserService $userService;
    protected TestService $testService;

    public function __construct(UserService $userService, TestService $testService)
    {
        $this->userService = $userService;
        $this->testService = $testService;
    }

    /**
     * List all users
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['role', 'is_active', 'search', 'include_deleted']);
        $perPage = $request->get('per_page', 15);

        $users = $this->userService->getPaginatedUsers($filters, $perPage);

        return ResponseHelper::paginated(
            $users->through(fn($user) => new UserResource($user)),
            'Users retrieved successfully'
        );
    }

    /**
     * Create new user
     * @throws ApiException
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return ResponseHelper::success(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    /**
     * Get specific user
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        return ResponseHelper::success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update user
     * @throws ApiException
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->updateUser($id, $request->validated());

        return ResponseHelper::success(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Delete user
     * @throws ApiException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return ResponseHelper::success(null, 'User deleted successfully');
    }

    /**
     * Promote user to teacher
     * @throws ApiException
     */
    public function promoteToTeacher(int $id): JsonResponse
    {
        $user = $this->userService->promoteToTeacher($id);

        return ResponseHelper::success(
            new UserResource($user),
            'User promoted to teacher successfully'
        );
    }

    /**
     * Demote user from teacher
     * @throws ApiException
     */
    public function demoteFromTeacher(int $id): JsonResponse
    {
        $user = $this->userService->demoteFromTeacher($id);

        return ResponseHelper::success(
            new UserResource($user),
            'User demoted from teacher successfully'
        );
    }

    /**
     * Activate user
     * @throws ApiException
     */
    public function activateUser(int $id): JsonResponse
    {
        $user = $this->userService->activateUser($id);

        return ResponseHelper::success(
            new UserResource($user),
            'User activated successfully'
        );
    }

    /**
     * Deactivate user
     * @throws ApiException
     */
    public function deactivateUser(int $id): JsonResponse
    {
        $user = $this->userService->deactivateUser($id);

        return ResponseHelper::success(
            new UserResource($user),
            'User deactivated successfully'
        );
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): JsonResponse
    {
        $stats = $this->userService->getUserStats();

        return ResponseHelper::success($stats, 'User statistics retrieved successfully');
    }

    /**
     * Bulk activate users
     */
    public function bulkActivateUsers(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        $count = $this->userService->bulkActivateUsers($request->user_ids);

        return ResponseHelper::success(
            ['affected_count' => $count],
            'Users activated successfully'
        );
    }

    /**
     * Bulk deactivate users
     */
    public function bulkDeactivateUsers(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        $count = $this->userService->bulkDeactivateUsers($request->user_ids);

        return ResponseHelper::success(
            ['affected_count' => $count],
            'Users deactivated successfully'
        );
    }

    /**
     * Bulk delete users
     */
    public function bulkDeleteUsers(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        $count = $this->userService->bulkDeleteUsers($request->user_ids);

        return ResponseHelper::success(
            ['affected_count' => $count],
            'Users deleted successfully'
        );
    }

    /**
     * Get all tests (admin only)
     */
    public function allTests(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'is_public', 'category_id', 'search']);
        $perPage = $request->get('per_page', 15);

        $tests = $this->testService->getPaginatedTests($filters, $perPage);

        // Transform the collection while preserving pagination
        $tests->getCollection()->transform(function ($test) {
            return new TestResource($test);
        });

        return ResponseHelper::paginated(
            $tests,
            'Tests retrieved successfully'
        );
    }

    /**
     * Delete test (admin only)
     * @throws ApiException
     */
    public function deleteTest(int $id): JsonResponse
    {
        $this->testService->deleteTest($id, Auth::user());

        return ResponseHelper::success(null, 'Test deleted successfully');
    }
}
