<?php

namespace App\Exceptions;

use App\Http\Response\Traits\ApiResponse;
use App\Utils\AppStr;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<string>
     */
    protected $dontReport = [
        AppValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(static function (Throwable $e): void {
        });
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        $errors = $exception->errors() ?: [];
        $firstError = array_shift($errors)[0] ?? null;
        $firstError = AppStr::getErrorName($firstError);
        $errorData = config("error.$firstError");

        if (!$errorData) {
            $errorData = [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $firstError,
            ];
        }

        return $this->respondError($errorData);
    }

    /**
     * @param Throwable $e
     * @return array<mixed>
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        $data = [
            'success' => false,
            'error' => [
                'code' => $this->isHttpException($e) ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $this->isHttpException($e) ? $e->getMessage() : 'server_error',
            ],
        ];

        if (config('app.debug')) {
            $data = array_merge($data, [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(static function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ]);
        }

        return $data;
    }

    protected function unauthenticated($request, AuthenticationException $exception): mixed
    {
        return $request->expectsJson()
            ? $this->respondError(config('admin_error.unauthenticated'))
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}

