<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return ResponseHelper::error(ErrorCodes::UNAUTHENTICATED);
        }

        $user = auth()->user();
        
        if (!$user->is_active) {
            return ResponseHelper::error(ErrorCodes::ACCOUNT_DISABLED);
        }

        $hasPermission = false;

        foreach ($roles as $role) {
            switch ($role) {
                case 'admin':
                    $hasPermission = $user->role === 'admin';
                    break;
                case 'teacher':
                    $hasPermission = $user->role === 'user' && $user->is_teacher;
                    break;
                case 'user':
                    $hasPermission = $user->role === 'user' && !$user->is_teacher;
                    break;
                case 'teacher_or_admin':
                    $hasPermission = $user->role === 'admin' || ($user->role === 'user' && $user->is_teacher);
                    break;
            }
            
            if ($hasPermission) break;
        }

        if (!$hasPermission) {
            return ResponseHelper::error(ErrorCodes::FORBIDDEN);
        }

        return $next($request);
    }
}