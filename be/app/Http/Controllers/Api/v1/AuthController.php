<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    )
    {
        $this->middleware('jwt.auth', ['except' => ['login', 'register', 'refresh']]);
    }

    /**
     * User login
     * @throws ApiException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $authData = $this->authService->login($credentials, $request);

        return ResponseHelper::success([
            'user' => new UserResource($authData['user']),
            'access_token' => $authData['access_token'],
            'refresh_token' => $authData['refresh_token'],
            'token_type' => $authData['token_type'],
            'expires_in' => $authData['expires_in'],
            'refresh_expires_in' => $authData['refresh_expires_in']
        ], 'Login successful');
    }

    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = $request->validated();
        $authData = $this->authService->register($userData, $request);

        return ResponseHelper::success([
            'user' => new UserResource($authData['user']),
            'access_token' => $authData['access_token'],
            'refresh_token' => $authData['refresh_token'],
            'token_type' => $authData['token_type'],
            'expires_in' => $authData['expires_in'],
            'refresh_expires_in' => $authData['refresh_expires_in']
        ], 'Registration successful', 201);
    }

    /**
     * User logout
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();
        $this->authService->logout($user);

        return ResponseHelper::success(null, 'Successfully logged out');
    }

    /**
     * Get current user
     */
    public function me(): JsonResponse
    {
        return ResponseHelper::success(
            new UserResource(Auth::user()),
            'User profile retrieved successfully'
        );
    }

    /**
     * Refresh JWT token using refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return ResponseHelper::error('INVALID_CREDENTIALS', 'Refresh token is required');
        }

        try {
            $authData = $this->authService->refreshTokens($refreshToken, $request);

            return ResponseHelper::success([
                'access_token' => $authData['access_token'],
                'refresh_token' => $authData['refresh_token'],
                'token_type' => $authData['token_type'],
                'expires_in' => $authData['expires_in'],
                'refresh_expires_in' => $authData['refresh_expires_in']
            ], 'Token refreshed successfully');
        } catch (ApiException $e) {
            return ResponseHelper::error($e->getErrorCode(), $e->getCustomMessage());
        }
    }
}
