<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class CheckTestOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $testId = $request->route('id') ?? $request->route('test');
        $user = auth()->user();

        if (!$testId) {
            return ResponseHelper::error(ErrorCodes::TEST_NOT_FOUND);
        }

        $test = Test::find($testId);
        if (!$test) {
            return ResponseHelper::error(ErrorCodes::TEST_NOT_FOUND);
        }

        // Admin can access all tests
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check ownership for teachers
        if ($user->role === 'teacher' && $test->created_by === $user->id) {
            return $next($request);
        }

        return ResponseHelper::error(ErrorCodes::INSUFFICIENT_PERMISSIONS);
    }
}