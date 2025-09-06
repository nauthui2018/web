<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseHelper
{
    /**
     * Return success response
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $statusCode
        ], $statusCode);
    }

    /**
     * Return error response
     */
    public static function error(string $errorCode, string $customMessage = null, string $customDetails = null, array $validationErrors = null): JsonResponse
    {
        $errorConfig = config("error_messages.{$errorCode}");
        
        if (!$errorConfig) {
            $errorConfig = config("error_messages.INTERNAL_SERVER_ERROR");
        }
        
        // Fallback if config is still not found
        if (!$errorConfig) {
            $errorConfig = [
                'message' => 'Internal Server Error',
                'details' => 'An unexpected error occurred',
                'status' => 500
            ];
        }
        
        $response = [
            'success' => false,
            'message' => $customMessage ?? $errorConfig['message'],
            'error' => [
                'code' => $errorCode,
                'details' => $customDetails ?? $errorConfig['details']
            ],
            'data' => null,
            'code' => $errorConfig['status']
        ];

        if ($validationErrors) {
            $response['error']['errors'] = $validationErrors;
        }

        return response()->json($response, $errorConfig['status']);
    }

    /**
     * Return paginated response
     */
    public static function paginated(LengthAwarePaginator $data, string $message = 'Data retrieved successfully', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'total_pages' => $data->lastPage(),
                    'has_next' => $data->hasMorePages(),
                    'has_previous' => $data->currentPage() > 1
                ]
            ],
            'code' => $statusCode
        ], $statusCode);
    }
}
