<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function getActiveUsers(): Collection;
    public function getInactiveUsers(): Collection;
    public function getAdmins(): Collection;
    public function getTeachers(): Collection;
    public function getRegularUsers(): Collection;
    public function searchUsers(string $search, array $filters = []): Collection;
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getUsersWithTestCounts(): Collection;
    public function bulkUpdateStatus(array $userIds, bool $isActive): int;
    public function bulkDelete(array $userIds): int;
    public function bulkForceDelete(array $userIds): int;
    public function getUserStats(): array;
    public function findWithTrashed(int $id): ?User;
}