<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getPaginatedWithFilters($filters, $perPage);
    }

    public function createUser(array $data): User
    {
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new ApiException(ErrorCodes::EMAIL_ALREADY_EXISTS, ErrorCodes::EMAIL_ALREADY_EXISTS_MSG);
        }

        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            return $this->userRepository->create($data);
        });
    }

    public function updateUser(int $userId, array $data): User
    {
        $user = $this->findUser($userId);

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = $this->userRepository->findByEmail($data['email']);
            if ($existingUser && $existingUser->id !== $userId) {
                throw new ApiException(ErrorCodes::EMAIL_ALREADY_EXISTS, ErrorCodes::EMAIL_ALREADY_EXISTS_MSG);
            }
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return DB::transaction(function () use ($userId, $data) {
            return $this->userRepository->update($userId, $data);
        });
    }

    public function promoteToTeacher(int $userId): User
    {
        $user = $this->findUser($userId);

        if ($user->is_teacher) {
            throw new ApiException(ErrorCodes::USER_ALREADY_TEACHER, ErrorCodes::USER_ALREADY_TEACHER_MSG);
        }

        if ($user->isAdmin()) {
            throw new ApiException(
                ErrorCodes::OPERATION_NOT_ALLOWED,
                ErrorCodes::OPERATION_NOT_ALLOWED_MSG
            );
        }

        return $this->userRepository->update($userId, ['is_teacher' => true]);
    }

    public function demoteFromTeacher(int $userId): User
    {
        $user = $this->findUser($userId);
        $currentUser = Auth::user();

        if ($currentUser->id === $userId) {
            throw new ApiException(ErrorCodes::CANNOT_DEACTIVATE_YOURSELF, ErrorCodes::CANNOT_DEACTIVATE_YOURSELF_MSG);
        }

        if (!$user->is_teacher) {
            throw new ApiException(
                ErrorCodes::OPERATION_NOT_ALLOWED,
                ErrorCodes::OPERATION_NOT_ALLOWED_MSG
            );
        }

        return $this->userRepository->update($userId, ['is_teacher' => false]);
    }

    public function activateUser(int $userId): User
    {
        $user = $this->findUser($userId);

        if ($user->is_active) {
            throw new ApiException(ErrorCodes::USER_ALREADY_ACTIVE, ErrorCodes::USER_ALREADY_ACTIVE_MSG);
        }

        return $this->userRepository->update($userId, ['is_active' => true]);
    }

    public function deactivateUser(int $userId): User
    {
        $user = $this->findUser($userId);
        $currentUser = Auth::user();

        if ($currentUser->id === $userId) {
            throw new ApiException(ErrorCodes::CANNOT_DEACTIVATE_YOURSELF, ErrorCodes::CANNOT_DEACTIVATE_YOURSELF_MSG);
        }

        if (!$user->is_active) {
            throw new ApiException(ErrorCodes::USER_ALREADY_INACTIVE, ErrorCodes::USER_ALREADY_INACTIVE_MSG);
        }

        return $this->userRepository->update($userId, ['is_active' => false]);
    }

    public function deleteUser(int $userId): bool
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $userId) {
            throw new ApiException(ErrorCodes::CANNOT_DELETE_YOURSELF, ErrorCodes::CANNOT_DELETE_YOURSELF_MSG);
        }

        return $this->userRepository->forceDelete($userId);
    }

    public function getUserStats(): array
    {
        return $this->userRepository->getUserStats();
    }

    public function bulkActivateUsers(array $userIds): int
    {
        return $this->userRepository->bulkUpdateStatus($userIds, true);
    }

    public function bulkDeactivateUsers(array $userIds): int
    {
        $currentUserId = Auth::id();
        $userIds = array_filter($userIds, fn($id) => $id != $currentUserId);
        
        return $this->userRepository->bulkUpdateStatus($userIds, false);
    }

    public function bulkDeleteUsers(array $userIds): int
    {
        $currentUserId = Auth::id();
        $userIds = array_filter($userIds, fn($id) => $id != $currentUserId);
        
        return $this->userRepository->bulkForceDelete($userIds);
    }

    public function getUserById(int $userId): User
    {
        return $this->findUser($userId);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): User
    {
        $user = $this->findUser($userId);

        if (!Hash::check($currentPassword, $user->password)) {
            throw new ApiException(ErrorCodes::INVALID_CREDENTIALS, 'Current password is incorrect');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId): array
    {
        $user = $this->findUser($userId);
        
        return [
            'total_attempts' => $user->testAttempts()->count(),
            'completed_attempts' => $user->testAttempts()->where('status', 'completed')->count(),
            'in_progress_attempts' => $user->testAttempts()->where('status', 'in_progress')->count(),
            'average_score' => $user->testAttempts()->where('status', 'completed')->avg('score') ?? 0,
            'tests_taken' => $user->testAttempts()->distinct('test_id')->count(),
        ];
    }

    private function findUser(int $userId): User
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new ApiException(ErrorCodes::USER_NOT_FOUND, ErrorCodes::USER_NOT_FOUND_MSG);
        }

        return $user;
    }
}