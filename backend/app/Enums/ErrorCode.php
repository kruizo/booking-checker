<?php

namespace App\Enums;

class ErrorCode
{
    // General errors (1xxx)
    public const INTERNAL_SERVER_ERROR = 'E1000';
    public const VALIDATION_ERROR = 'E1001';
    public const UNAUTHORIZED = 'E1002';
    public const FORBIDDEN = 'E1003';
    public const NOT_FOUND = 'E1004';
    public const METHOD_NOT_ALLOWED = 'E1005';
    public const TOO_MANY_REQUESTS = 'E1006';

    // Authentication errors (2xxx)
    public const UNAUTHENTICATED = 'E2000';
    public const INVALID_CREDENTIALS = 'E2001';
    public const TOKEN_EXPIRED = 'E2002';
    public const TOKEN_INVALID = 'E2003';

    // Resource errors (3xxx)
    public const RESOURCE_NOT_FOUND = 'E3000';
    public const USER_NOT_FOUND = 'E3001';
    public const BOOKING_NOT_FOUND = 'E3002';

    // Business logic errors (4xxx)
    public const BOOKING_CONFLICT = 'E4000';
    public const BOOKING_OVERLAP = 'E4001';
    public const INVALID_TIME_RANGE = 'E4002';
    public const PAST_DATE_NOT_ALLOWED = 'E4003';

    // Database errors (5xxx)
    public const DATABASE_ERROR = 'E5000';
    public const DATABASE_CONNECTION_ERROR = 'E5001';

    /**
     * Get human-readable description for error code
     */
    public static function getDescription(string $code): string
    {
        return match ($code) {
            self::INTERNAL_SERVER_ERROR => 'Internal server error',
            self::VALIDATION_ERROR => 'Validation error',
            self::UNAUTHORIZED => 'Unauthorized access',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Resource not found',
            self::METHOD_NOT_ALLOWED => 'Method not allowed',
            self::TOO_MANY_REQUESTS => 'Too many requests',
            
            self::UNAUTHENTICATED => 'Authentication required',
            self::INVALID_CREDENTIALS => 'Invalid credentials',
            self::TOKEN_EXPIRED => 'Token expired',
            self::TOKEN_INVALID => 'Invalid token',
            
            self::RESOURCE_NOT_FOUND => 'Resource not found',
            self::USER_NOT_FOUND => 'User not found',
            self::BOOKING_NOT_FOUND => 'Booking not found',
            
            self::BOOKING_CONFLICT => 'Booking conflict detected',
            self::BOOKING_OVERLAP => 'Booking time overlap',
            self::INVALID_TIME_RANGE => 'Invalid time range',
            self::PAST_DATE_NOT_ALLOWED => 'Past date not allowed',
            
            self::DATABASE_ERROR => 'Database error',
            self::DATABASE_CONNECTION_ERROR => 'Database connection error',
            
            default => 'Unknown error',
        };
    }
}
