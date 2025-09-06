<?php

namespace App\Http\Response\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function respondSuccess($data, $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], $statusCode);
    }

    protected function respondError($error, $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $error['data'] ?? null,
            'error' => [
                'code' => $error['code'],
                'message' => $error['message'],
            ],
        ], $statusCode);
    }
}
