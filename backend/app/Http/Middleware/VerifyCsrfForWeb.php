<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates CSRF token only for cookie-based authentication.
 * Skips CSRF validation if request has a Bearer token (mobile/API clients).
 */
class VerifyCsrfForWeb extends ValidateCsrfToken
{
    public function handle($request, Closure $next): Response
    {
        // Skip CSRF validation if using Bearer token authentication
        if ($this->isUsingBearerToken($request)) {
            return $next($request);
        }

        // Validate CSRF as normal
        return parent::handle($request, $next);
    }

    protected function isUsingBearerToken(Request $request): bool
    {
        return $request->bearerToken() !== null;
    }
}
