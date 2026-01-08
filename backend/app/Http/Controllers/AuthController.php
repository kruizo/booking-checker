<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return ApiResponse::ok([
            'user' => $user,
        ], 'User registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->validated());

        return ApiResponse::ok([
            'user' => $user,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::ok(null, 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        return ApiResponse::ok([
            'user' => new UserResource($request->user()),
        ], 'User retrieved successfully');
    }
}
