<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected int $statusCode;
    protected ?string $errorCode;
    protected mixed $data;

    public function __construct(
        int $statusCode,
        string $message,
        ?string $errorCode = null,
        mixed $data = null
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->data = $data;
    }

    public function render($request): JsonResponse
    {
        return ApiResponse::error(
            $this->statusCode,
            $this->message,
            $this->errorCode,
            $this->data
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
