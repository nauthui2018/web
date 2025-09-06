<?php

use App\Http\Controllers\Api\v1\AdminController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\CertificateController;
use App\Http\Controllers\Api\v1\HealthController;
use App\Http\Controllers\Api\v1\TeacherController;
use App\Http\Controllers\Api\v1\TestController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check route
Route::get('health', [HealthController::class, 'check'])->name('health.check');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
    Route::get('me', [AuthController::class, 'me'])->name('auth.me');
});

// Protected routes
Route::middleware(['jwt.auth', 'check.user.status'])->group(function () {
    // Admin only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User management
        Route::get('users', [AdminController::class, 'index'])->name('admin.users.index');
        Route::post('users', [AdminController::class, 'store'])->name('admin.users.store');
        Route::get('users/{id}', [AdminController::class, 'show'])->name('admin.users.show');
        Route::put('users/{id}', [AdminController::class, 'update'])->name('admin.users.update');
        Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');

        // User role management
        Route::put('users/{id}/promote-teacher', [AdminController::class, 'promoteToTeacher'])->name('admin.users.promote-teacher');
        Route::put('users/{id}/demote-teacher', [AdminController::class, 'demoteFromTeacher'])->name('admin.users.demote-teacher');
        Route::put('users/{id}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');
        Route::put('users/{id}/deactivate', [AdminController::class, 'deactivateUser'])->name('admin.users.deactivate');

        // Bulk operations
        Route::post('users/bulk-activate', [AdminController::class, 'bulkActivateUsers'])->name('admin.users.bulk-activate');
        Route::post('users/bulk-deactivate', [AdminController::class, 'bulkDeactivateUsers'])->name('admin.users.bulk-deactivate');
        Route::post('users/bulk-delete', [AdminController::class, 'bulkDeleteUsers'])->name('admin.users.bulk-delete');

        // Statistics
        Route::get('users-stats', [AdminController::class, 'getUserStats'])->name('admin.users.stats');

        // Category management
        Route::apiResource('categories', CategoryController::class)->names([
            'index' => 'admin.categories.index',
            'store' => 'admin.categories.store',
            'show' => 'admin.categories.show',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy',
        ]);

        // Test management
        Route::get('tests', [AdminController::class, 'allTests'])->name('admin.tests.index');
        Route::delete('tests/{id}', [AdminController::class, 'deleteTest'])->name('admin.tests.delete');

        // Certificate management
        Route::post('certificates/{certificateNumber}/regenerate', [CertificateController::class, 'regenerate'])->name('certificates.regenerate');
    });

    // Teacher only routes
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        // Test management
        Route::apiResource('tests', TeacherController::class)->names([
            'index' => 'teacher.tests.index',
            'store' => 'teacher.tests.store',
            'show' => 'teacher.tests.show',
            'update' => 'teacher.tests.update',
            'destroy' => 'teacher.tests.destroy',
        ]);
        Route::get('tests/{id}/analytics', [TeacherController::class, 'getTestAnalytics'])->name('teacher.tests.analytics');
        Route::get('tests/{id}/detailed-analytics', [TeacherController::class, 'getDetailedTestAnalytics'])->name('teacher.tests.detailed-analytics');
        Route::get('tests/{id}/attempts', [TeacherController::class, 'getTestAttempts'])->name('teacher.tests.attempts');

        // Question management
        Route::post('tests/{testId}/questions', [TeacherController::class, 'addQuestion'])->name('teacher.tests.questions.store');
        Route::put('tests/{testId}/questions/{questionId}', [TeacherController::class, 'updateQuestion'])->name('teacher.tests.questions.update');
        Route::delete('tests/{testId}/questions/{questionId}', [TeacherController::class, 'deleteQuestion'])->name('teacher.tests.questions.delete');
        Route::get('tests/{testId}/questions', [TeacherController::class, 'getTestQuestions'])->name('teacher.tests.questions.index');
        Route::put('tests/{testId}/questions/reorder', [TeacherController::class, 'reorderQuestions'])->name('teacher.tests.questions.reorder');
    });

    // All authenticated users

    // Quiz Page
    Route::get('tests/{id}/take', [TestController::class, 'getTestForTaking'])->name('tests.take');
    Route::post('tests/{id}/start', [TestController::class, 'startTest'])->name('tests.start');
    Route::post('attempts/{id}/submit', [TestController::class, 'submitTestAttempt'])->name('attempts.submit');

    // Dashboard and test management
    Route::get('management/tests', [TestController::class, 'getTestsCreatedByUser'])->name('tests.get-tests-created-by-user');
    Route::get('management/tests/{testId}', [TestController::class, 'getTestDetail'])->name('tests.get-test-detail');
    Route::post('management/tests', [TestController::class, 'createTestWithQuestions'])->name('tests.create-with-questions');
    Route::put('management/tests/{testId}', [TestController::class, 'updateTestAndQuestions'])->name('tests.update-test');
    Route::delete('management/tests/{testId}', [TestController::class, 'deleteTest'])->name('test.delete-test-by-id');

    // Analysis and statistics
    Route::get('analytics/tests/summary', [TestController::class, 'getYourCreatedTestAnalyticsSummary'])->name('tests.your-tests-analytics');
    Route::get('analytics/tests/completion-trends', [TestController::class, 'getCompletionTrend'])->name('tests.completion-trends');
    Route::get('analytics/tests/category-performance', [TestController::class, 'getCategoryPerformance'])->name('tests.category-performance');
    Route::get('analytics/tests/difficulty-distribution', [TestController::class, 'getDifficultyDistribution'])->name('tests.difficulty-distribution');
    Route::get('analytics/tests/score-distribution', [TestController::class, 'getScoreDistribution'])->name('tests.score-distribution');
    Route::get('analytics/tests/{testId}/detailed', [TestController::class, 'getDetailedTestAnalytics'])->name('tests.detailed-analytics');
    Route::get('analytics/tests/attempts-by-day', [TestController::class, 'getTestAttemptsByDay'])->name('tests.attempts-by-day');



    // User profile and attempts
    Route::get('profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('password', [UserController::class, 'changePassword'])->name('user.password.change');
    Route::get('my-attempts', [UserController::class, 'myAttempts'])->name('user.attempts');
    Route::get('activity-summary', [UserController::class, 'activitySummary'])->name('user.activity');

    // Certificate routes (authenticated)
    Route::prefix('certificates')->group(function () {
        Route::get('/', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('templates', [CertificateController::class, 'templates'])->name('certificates.templates');
        Route::get('{certificateNumber}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::get('{certificateNumber}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });
});


// Public routes
Route::get('tests', [TestController::class, 'availableTests'])->name('public.tests.index');
Route::get('tests/{id}', [TestController::class, 'show'])->name('public.tests.show');
Route::get('categories', [CategoryController::class, 'index'])->name('public.categories.index');
Route::get('categories/active', [CategoryController::class, 'getActiveCategories'])->name('public.categories.active');
Route::get('categories/{id}', [CategoryController::class, 'show'])->name('public.categories.show');
