<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return ResponseHelper::error(ErrorCodes::UNAUTHENTICATED);
        }

        $user = auth()->user();
        
        if (!$user->is_active) {
            return ResponseHelper::error(ErrorCodes::ACCOUNT_DISABLED);
        }
        
        return $next($request);
    }
}