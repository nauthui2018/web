<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get active categories
     */
    public function getActiveCategories(): Collection;

    /**
     * Get inactive categories
     */
    public function getInactiveCategories(): Collection;

    /**
     * Get categories created by specific user
     */
    public function getCategoriesByCreator(int $userId): Collection;

    /**
     * Get categories with pagination and filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Search categories
     */
    public function searchCategories(string $search): Collection;

    /**
     * Get categories with test counts
     */
    public function getCategoriesWithTestCounts(): Collection;

    /**
     * Get category statistics
     */
    public function getCategoryStats(): array;

    /**
     * Toggle category status
     */
    public function toggleStatus(int $categoryId): bool;

    /**
     * Get categories with relationships
     */
    public function getCategoriesWithRelations(array $relations = []): Collection;

    /**
     * Find category with creator
     */
    public function findWithCreator(int $categoryId): ?Category;

    /**
     * Get popular categories (with most tests)
     */
    public function getPopularCategories(int $limit = 10): Collection;
}
