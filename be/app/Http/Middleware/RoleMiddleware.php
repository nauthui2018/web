<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth('api')->user();

        // Check if user is authenticated
        if (!$user) {
            $errorData = config('errors.unauthenticated', [
                'code' => 4003,
                'message' => 'Unauthenticated',
                'http_code' => 401
            ]);

            return response()->json([
                'data' => [],
                'success' => false,
                'code' => $errorData['http_code'],
                'message' => $errorData['message'],
                'error_code' => $errorData['code'],
                'error_key' => 'unauthenticated'
            ], $errorData['http_code']);
        }

        // Check if user has required role
        if (!in_array($user->role, $roles)) {
            $errorData = config('errors.insufficient_permissions', [
                'code' => 4006,
                'message' => 'Insufficient permissions',
                'http_code' => 403
            ]);

            return response()->json([
                'data' => [],
                'success' => false,
                'code' => $errorData['http_code'],
                'message' => $errorData['message'],
                'error_code' => $errorData['code'],
                'error_key' => 'insufficient_permissions'
            ], $errorData['http_code']);
        }

        return $next($request);
    }
}