<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getActiveCategories(): Collection
    {
        return $this->model->active()->get();
    }

    public function getInactiveCategories(): Collection
    {
        return $this->model->where('is_active', false)->get();
    }

    public function getCategoriesByCreator(int $userId): Collection
    {
        return $this->model->where('created_by', $userId)->get();
    }

    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('creator');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (isset($filters['include_deleted']) && $filters['include_deleted']) {
            $query->withTrashed();
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function searchCategories(string $search): Collection
    {
        return $this->model->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        })->get();
    }

    public function getCategoriesWithTestCounts(): Collection
    {
        return $this->model->withCount('tests')->get();
    }

    public function getCategoryStats(): array
    {
        return [
            'total_categories' => $this->model->count(),
            'active_categories' => $this->model->where('is_active', true)->count(),
            'inactive_categories' => $this->model->where('is_active', false)->count(),
            'categories_with_tests' => $this->model->has('tests')->count(),
            'categories_without_tests' => $this->model->doesntHave('tests')->count(),
        ];
    }

    public function toggleStatus(int $categoryId): bool
    {
        $category = $this->findOrFail($categoryId);
        $category->is_active = !$category->is_active;
        return $category->save();
    }

    public function getCategoriesWithRelations(array $relations = []): Collection
    {
        return $this->model->with($relations)->get();
    }

    public function findWithCreator(int $categoryId): ?Category
    {
        return $this->model->with('creator')->find($categoryId);
    }

    public function getPopularCategories(int $limit = 10): Collection
    {
        return $this->model
            ->withCount('tests')
            ->orderBy('tests_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
