<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return ResponseHelper::error('UNAUTHENTICATED');
            }

        } catch (TokenExpiredException $e) {
            return ResponseHelper::error('TOKEN_EXPIRED');
        } catch (TokenInvalidException $e) {
            return ResponseHelper::error('TOKEN_INVALID');
        } catch (JWTException $e) {
            return ResponseHelper::error('UNAUTHENTICATED');
        }

        return $next($request);
    }
}
