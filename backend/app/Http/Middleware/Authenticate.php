<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the token from cookie for Sanctum auth
     */
    protected function authenticate($request, array $guards)
    {
        if ($request->hasCookie('auth')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('auth_token'));
        }
        
        return parent::authenticate($request, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Always return null for API requests to prevent redirects
        return null;
    }
}
