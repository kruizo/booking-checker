<?php

namespace App\Http\Responses;

use App\Enums\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{

    /**
     * Successful response
     */
    public static function ok(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        if ($data instanceof LengthAwarePaginator) {
            return self::buildPaginatedResponse($data, $message);
        }

        return response()->json([
            'status' => $status,
            'errorCode' => null,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ], $status);
    }

    private static function buildPaginatedResponse(LengthAwarePaginator $paginator, string $message): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'errorCode' => null,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'has_next_page' => $paginator->hasMorePages(),
                'has_prev_page' => $paginator->currentPage() > 1,
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
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

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

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
        if ($response instanceof JsonResponse && request()->is('api/*')) {
            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getContent(), true);

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

            // Add data
            if ($data !== null) {
                $response['data'] = $data;
            }

            return response()->json($response, $statusCode);
        }

        return $response;
    }
}
