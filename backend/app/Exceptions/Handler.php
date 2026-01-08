<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Only format API responses
        if ($request->is('api/*') || $request->expectsJson()) {
            // Custom API exceptions
            if ($exception instanceof ApiException) {
                return $exception->render($request);
            }

            // Validation errors
            if ($exception instanceof ValidationException) {
                return ApiResponse::error(
                    422,
                    $exception->getMessage(),
                    ErrorCode::VALIDATION_ERROR,
                    null,
                    $exception->errors()
                );
            }

            // Authentication errors
            if ($exception instanceof AuthenticationException) {
                return ApiResponse::error(401, 'Unauthenticated', ErrorCode::UNAUTHENTICATED);
            }

            // Authorization errors
            if ($exception instanceof AuthorizationException) {
                return ApiResponse::error(403, $exception->getMessage() ?: 'Forbidden', ErrorCode::FORBIDDEN);
            }

            // Model not found errors
            if ($exception instanceof ModelNotFoundException) {
                return ApiResponse::error(404, 'Resource not found', ErrorCode::RESOURCE_NOT_FOUND);
            }
        }

        return parent::render($request, $exception);
    }
}
