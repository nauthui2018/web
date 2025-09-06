<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->active()->get();
    }

    public function getInactiveUsers(): Collection
    {
        return $this->model->where('is_active', false)->get();
    }

    public function getAdmins(): Collection
    {
        return $this->model->admins()->get();
    }

    public function getTeachers(): Collection
    {
        return $this->model->teachers()->get();
    }

    public function getRegularUsers(): Collection
    {
        return $this->model->regularUsers()->get();
    }

    public function searchUsers(string $search, array $filters = []): Collection
    {
        $query = $this->model->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        $this->applyFilters($query, $filters);

        if (isset($filters['include_deleted']) && $filters['include_deleted']) {
            $query->withTrashed();
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getUsersWithTestCounts(): Collection
    {
        return $this->model->withCount(['createdTests', 'testAttempts'])->get();
    }

    public function bulkUpdateStatus(array $userIds, bool $isActive): int
    {
        return $this->model->whereIn('id', $userIds)->update(['is_active' => $isActive]);
    }

    public function bulkDelete(array $userIds): int
    {
        return $this->model->whereIn('id', $userIds)->delete();
    }

    public function bulkForceDelete(array $userIds): int
    {
        return $this->model->whereIn('id', $userIds)->forceDelete();
    }

    public function getUserStats(): array
    {
        return [
            'total_users' => $this->model->count(),
            'active_users' => $this->model->active()->count(),
            'inactive_users' => $this->model->where('is_active', false)->count(),
            'total_admins' => $this->model->admins()->count(),
            'total_teachers' => $this->model->teachers()->count(),
            'total_regular_users' => $this->model->regularUsers()->count(),
            'deleted_users' => $this->model->onlyTrashed()->count(),
        ];
    }

    public function findWithTrashed(int $id): ?User
    {
        return $this->model->withTrashed()->find($id);
    }

    private function applyFilters($query, array $filters)
    {
        if (isset($filters['role'])) {
            switch ($filters['role']) {
                case 'admin':
                    $query->admins();
                    break;
                case 'teacher':
                    $query->teachers();
                    break;
                case 'user':
                    $query->regularUsers();
                    break;
            }
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }
}