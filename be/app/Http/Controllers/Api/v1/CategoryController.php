<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * List all categories
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'search', 'include_deleted']);
        $perPage = $request->get('per_page', 15);

        $categories = $this->categoryService->getPaginatedCategories($filters, $perPage);

        return ResponseHelper::paginated(
            $categories->through(fn($category) => new CategoryResource($category)),
            'Categories retrieved successfully'
        );
    }

    /**
     * Create new category
     * @throws ApiException
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $category = $this->categoryService->createCategory($data);

        return ResponseHelper::success(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Get specific category
     */
    public function show(int $category): JsonResponse
    {
        $categoryModel = $this->categoryService->getCategoryById($category);

        return ResponseHelper::success(
            new CategoryResource($categoryModel),
            'Category retrieved successfully'
        );
    }

    /**
     * Update category
     * @throws ApiException
     */
    public function update(UpdateCategoryRequest $request, int $category): JsonResponse
    {
        $categoryModel = $this->categoryService->updateCategory($category, $request->validated());

        return ResponseHelper::success(
            new CategoryResource($categoryModel),
            'Category updated successfully'
        );
    }

    /**
     * Delete category
     * @throws ApiException
     */
    public function destroy(int $category): JsonResponse
    {
        $this->categoryService->deleteCategory($category);

        return ResponseHelper::success(null, 'Category deleted successfully');
    }

    /**
     * Get active categories (for dropdowns)
     */
    public function getActiveCategories(): JsonResponse
    {
        $categories = $this->categoryService->getActiveCategories();

        return ResponseHelper::success(
            CategoryResource::collection($categories),
            'Active categories retrieved successfully'
        );
    }
}
