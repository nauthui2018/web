<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    /**
     * Check the health of the API and its dependencies
     */
    public function check(): JsonResponse
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
            ],
            'api_version' => 'v1',
        ];

        $httpStatus = collect($status['services'])->contains('status', 'down') ? 503 : 200;

        return response()->json($status, $httpStatus);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            // Simple database connection test
            DB::connection()->getPdo();
            $dbStatus = [
                'status' => 'up',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $dbStatus = [
                'status' => 'down',
                'message' => 'Database connection failed'
            ];
            
            if (app()->environment() !== 'production') {
                $dbStatus['error'] = $e->getMessage();
            }
        }

        return $dbStatus;
    }

    /**
     * Check cache connectivity
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_test_' . time();
            Cache::put($testKey, true, 10);
            $cacheWorking = Cache::get($testKey);
            
            return [
                'status' => $cacheWorking ? 'up' : 'down',
                'message' => $cacheWorking ? 'Cache service is working' : 'Cache retrieve failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'message' => 'Cache service failed',
                'error' => app()->environment() !== 'production' ? $e->getMessage() : null
            ];
        }
    }
}
