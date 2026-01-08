<?php

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Closure;
use Error;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return ApiResponse::error(403, 'Forbidden', ErrorCode::FORBIDDEN);
        }

        return $next($request);
    }
}
