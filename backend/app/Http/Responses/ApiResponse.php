<?php

namespace App\Http\Responses;

use App\Enums\ErrorCode;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Success response
     */
    public static function ok(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'errorCode' => null,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ], 200);
    }

    /**
     * Error response
     */
    public static function error(
        int $status,
        string $message,
        ?string $errorCode = null,
        mixed $data = null,
        ?array $errors = null
    ): JsonResponse {
        $response = [
            'status' => $status,
            'errorCode' => $errorCode,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        // Add errors at root level if provided
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // Add data if provided
        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Transform a Laravel exception response into API format
     */
    public static function fromResponse(Response $response): Response
    {
        // Only transform JSON responses for API routes
        if ($response instanceof JsonResponse && request()->is('api/*')) {
            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getContent(), true);

            // Map common error codes
            $errorCode = match ($statusCode) {
                401 => ErrorCode::UNAUTHENTICATED,
                403 => ErrorCode::FORBIDDEN,
                404 => ErrorCode::RESOURCE_NOT_FOUND,
                422 => ErrorCode::VALIDATION_ERROR,
                500 => ErrorCode::INTERNAL_SERVER_ERROR,
                default => null,
            };

            // Extract message and errors from Laravel's response
            $message = $content['message'] ?? 'An error occurred';
            
            // Hide error details in production for 500 errors
            if ($statusCode === 500 && !config('app.debug')) {
                $message = 'Internal server error has occurred';
            }
            
            $errors = null;
            $data = null;

            // Handle validation errors - put at root level
            if ($statusCode === 422 && isset($content['errors'])) {
                $errors = $content['errors'];
            }

            // Transform to our format
            $response = [
                'status' => $statusCode,
                'errorCode' => $errorCode,
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ];

            // Add errors at root level if present
            if ($errors !== null) {
                $response['errors'] = $errors;
            }

            // Add data if needed
            if ($data !== null) {
                $response['data'] = $data;
            }

            return response()->json($response, $statusCode);
        }

        return $response;
    }
}
