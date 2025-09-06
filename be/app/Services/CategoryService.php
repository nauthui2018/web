<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get paginated categories
     */
    public function getPaginatedCategories(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Create category
     */
    public function createCategory(array $data): Category
    {
        // Check if category with same name exists
        $existingCategory = $this->categoryRepository->searchCategories($data['name']);
        if ($existingCategory->where('name', $data['name'])->count() > 0) {
            throw new ApiException(ErrorCodes::CATEGORY_ALREADY_EXISTS, ErrorCodes::CATEGORY_ALREADY_EXISTS_MSG);
        }

        return DB::transaction(function () use ($data) {
            return $this->categoryRepository->create($data);
        });
    }

    /**
     * Update category
     */
    public function updateCategory(int $categoryId, array $data): Category
    {
        $category = $this->findCategoryById($categoryId);

        // Check if name is being changed and if new name already exists
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $existingCategory = $this->categoryRepository->searchCategories($data['name']);
            if ($existingCategory->where('name', $data['name'])->where('id', '!=', $categoryId)->count() > 0) {
                throw new ApiException(ErrorCodes::CATEGORY_ALREADY_EXISTS, ErrorCodes::CATEGORY_ALREADY_EXISTS_MSG);
            }
        }

        return DB::transaction(function () use ($categoryId, $data) {
            return $this->categoryRepository->update($categoryId, $data);
        });
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $categoryId): bool
    {
        $category = $this->findCategoryById($categoryId);

        // Check if category has tests
        if ($category->tests()->count() > 0) {
            throw new ApiException(ErrorCodes::CANNOT_DELETE_CATEGORY_WITH_TESTS, ErrorCodes::CANNOT_DELETE_CATEGORY_WITH_TESTS_MSG);
        }

        return $this->categoryRepository->forceDelete($categoryId);
    }

    /**
     * Get category by ID
     */
    public function getCategoryById(int $categoryId): Category
    {
        return $this->findCategoryById($categoryId);
    }

    /**
     * Get active categories
     */
    public function getActiveCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->categoryRepository->getActiveCategories();
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats(): array
    {
        return $this->categoryRepository->getCategoryStats();
    }

    /**
     * Find category by ID or throw exception
     */
    private function findCategoryById(int $categoryId): Category
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw new ApiException(ErrorCodes::CATEGORY_NOT_FOUND, ErrorCodes::CATEGORY_NOT_FOUND_MSG);
        }

        return $category;
    }
}
