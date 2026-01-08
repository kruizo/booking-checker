<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Http\Middleware\VerifyCsrfForWeb;
use \Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\AdminMiddleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum's stateful authentication (cookie-based)
        $middleware->statefulApi();
        
        // Session + CSRF middleware
        $middleware->api(prepend: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            VerifyCsrfForWeb::class,
        ]);
        
        // Exclude auth entry points from CSRF validation (no session yet)
        $middleware->validateCsrfTokens(except: [
            'api/v1/auth/login',
            'api/v1/auth/register',
        ]);
        
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->respond(function (Response $response) {
            return ApiResponse::fromResponse($response);
        });
    })->create();
