<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Booking Checker API',
        'version' => '1.0.0',
        'documentation' => '/api/v1',
    ]);
});

Route::prefix('v1')->group(function (): void {
    // ============================================
    // Auth (Cookie + Token) - excluded from CSRF
    // Web SPA uses cookies, Mobile uses the token
    // ============================================
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Current user can update their own profile
        Route::prefix('user')->group(function (): void {
            Route::get('/', [AuthController::class, 'user'])->name('user');
            Route::put('/', [UserController::class, 'updateProfile'])->name('user.update');
            // FOr testing purposes
            Route::patch('/{id}/permission', [UserController::class, 'updatePermission']);
        });


        Route::prefix('bookings')->group(function (): void {
            Route::get('/', [BookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'store']);
            Route::get('/{id}', [BookingController::class, 'show']);
            Route::put('/{id}', [BookingController::class, 'update']);
            Route::delete('/{id}', [BookingController::class, 'destroy']);
            Route::get('/{id}/validate', [BookingController::class, 'validate']);
        });

        Route::middleware('admin')->group(function (): void {
            Route::get('/admin/conflicts', [BookingController::class, 'conflicts']);
            Route::get('/admin/statistics', [StatisticsController::class, 'index']);
            
            Route::prefix('admin/users')->group(function (): void {
                Route::get('/', [UserController::class, 'index']);
                Route::get('/{id}', [UserController::class, 'show']);
                Route::put('/{id}', [UserController::class, 'update']);
                Route::patch('/{id}/permission', [UserController::class, 'updatePermission']);
            });
        });
    });
});
