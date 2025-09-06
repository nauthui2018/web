<?php

namespace App\Services;

use App\Constants\ErrorCodes;
use App\Exceptions\ApiException;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Random\RandomException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Authenticate user and return tokens
     * 
     * @param array $credentials
     * @param Request|null $request
     * @return array
     * @throws ApiException
     */
    public function login(array $credentials, ?Request $request = null): array
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            throw new ApiException(ErrorCodes::INVALID_CREDENTIALS);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            throw new ApiException(ErrorCodes::ACCOUNT_DISABLED);
        }

        $refreshToken = $this->generateRefreshToken($user, $request);

        return [
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $this->getAccessTokenTTL(),
            'refresh_expires_in' => $this->getRefreshTokenTTL()
        ];
    }

    /**
     * Register a new user and return tokens
     * 
     * @param array $userData
     * @param Request|null $request
     * @return array
     * @throws RandomException
     */
    public function register(array $userData, ?Request $request = null): array
    {
        $userData['role'] = 'user';
        $userData['is_teacher'] = false;
        $userData['is_active'] = true;

        $user = $this->userService->createUser($userData);
        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->generateRefreshToken($user, $request);

        return [
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $this->getAccessTokenTTL(),
            'refresh_expires_in' => $this->getRefreshTokenTTL()
        ];
    }

    /**
     * Refresh tokens using refresh token
     * 
     * @param string $refreshToken
     * @param Request|null $request
     * @return array
     * @throws ApiException
     */
    public function refreshTokens(string $refreshToken, ?Request $request = null): array
    {
        $user = $this->validateRefreshToken($refreshToken);

        if (!$user) {
            throw new ApiException(ErrorCodes::TOKEN_INVALID, 'Invalid refresh token');
        }

        if (!$user->is_active) {
            throw new ApiException(ErrorCodes::ACCOUNT_DISABLED);
        }

        // Generate new tokens
        $newAccessToken = JWTAuth::fromUser($user);
        $newRefreshToken = $this->generateRefreshToken($user, $request);

        // Invalidate old refresh token
        $this->invalidateRefreshToken($refreshToken);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'bearer',
            'expires_in' => $this->getAccessTokenTTL(),
            'refresh_expires_in' => $this->getRefreshTokenTTL()
        ];
    }

    /**
     * Logout user and invalidate tokens
     * 
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // Invalidate current JWT token
        JWTAuth::invalidate(JWTAuth::getToken());

        // Invalidate all refresh tokens for this user
        $this->invalidateUserRefreshTokens($user);
    }

    /**
     * Generate a secure refresh token for the user
     * 
     * @param User $user
     * @param Request|null $request
     * @return string
     * @throws RandomException
     */
    public function generateRefreshToken(User $user, ?Request $request = null): string
    {
        $refreshToken = bin2hex(random_bytes(64));

        // Store refresh token in database
        RefreshToken::create([
            'token' => $refreshToken,
            'user_id' => $user->id,
            'device_name' => $request?->header('X-Device-Name') ?? 'Unknown Device',
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'expires_at' => now()->addDays($this->getRefreshTokenTTLInDays()),
        ]);

        return $refreshToken;
    }

    /**
     * Validate refresh token and return associated user
     * 
     * @param string $refreshToken
     * @return User|null
     */
    public function validateRefreshToken(string $refreshToken): ?User
    {
        $tokenRecord = RefreshToken::where('token', $refreshToken)
            ->valid()
            ->with('user')
            ->first();

        if (!$tokenRecord || !$tokenRecord->user) {
            return null;
        }

        // Mark token as used
        $tokenRecord->markAsUsed();

        return $tokenRecord->user;
    }

    /**
     * Invalidate a specific refresh token
     * 
     * @param string $refreshToken
     * @return void
     */
    public function invalidateRefreshToken(string $refreshToken): void
    {
        RefreshToken::where('token', $refreshToken)->delete();
    }

    /**
     * Invalidate all refresh tokens for a user
     * 
     * @param User $user
     * @return void
     */
    public function invalidateUserRefreshTokens(User $user): void
    {
        RefreshToken::where('user_id', $user->id)->delete();
    }

    /**
     * Get token expiration times in seconds
     * 
     * @return array
     */
    public function getTokenExpirationTimes(): array
    {
        return [
            'access_token_ttl' => $this->getAccessTokenTTL(),
            'refresh_token_ttl' => $this->getRefreshTokenTTL()
        ];
    }

    /**
     * Get access token TTL in seconds
     * 
     * @return int
     */
    public function getAccessTokenTTL(): int
    {
        return config('jwt.ttl', 60) * 60; // Convert minutes to seconds
    }

    /**
     * Get refresh token TTL in seconds
     * 
     * @return int
     */
    public function getRefreshTokenTTL(): int
    {
        return config('jwt.refresh_ttl', 10080) * 60; // Convert minutes to seconds
    }

    /**
     * Get refresh token TTL in days for database storage
     * 
     * @return int
     */
    public function getRefreshTokenTTLInDays(): int
    {
        return (int) (config('jwt.refresh_ttl', 10080) / (60 * 24)); // Convert minutes to days
    }

    /**
     * Check if user account is active and can authenticate
     * 
     * @param User $user
     * @return bool
     * @throws ApiException
     */
    public function validateUserAccount(User $user): bool
    {
        if (!$user->is_active) {
            throw new ApiException(ErrorCodes::ACCOUNT_DISABLED);
        }

        return true;
    }

    /**
     * Get all active refresh tokens for a user
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserActiveTokens(User $user)
    {
        return RefreshToken::forUser($user->id)
            ->valid()
            ->orderBy('last_used_at', 'desc')
            ->get();
    }

    /**
     * Revoke a specific token by ID (for "logout from this device" feature)
     * 
     * @param int $tokenId
     * @param int $userId
     * @return bool
     */
    public function revokeTokenById(int $tokenId, int $userId): bool
    {
        return RefreshToken::where('id', $tokenId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Revoke all tokens except the current one (for "logout from other devices" feature)
     * 
     * @param User $user
     * @param string $currentToken
     * @return int Number of tokens revoked
     */
    public function revokeOtherTokens(User $user, string $currentToken): int
    {
        return RefreshToken::where('user_id', $user->id)
            ->where('token', '!=', $currentToken)
            ->delete();
    }

    /**
     * Clean up expired tokens
     * 
     * @return int Number of tokens cleaned up
     */
    public function cleanupExpiredTokens(): int
    {
        return RefreshToken::expired()->delete();
    }
}
