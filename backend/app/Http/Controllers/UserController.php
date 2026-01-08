<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Responses\ApiResponse;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
    }

    /**
     * Get all users (admin only).
     * Supports ?keyword=, ?page=, ?per_page=, ?sort_by=, ?sort_direction= query params.
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->only([
            'keyword',
            'page',
            'per_page',
            'sort_by',
            'sort_direction',
        ]);
        
        $users = $this->userService->getAllUsers($params);
        
        return ApiResponse::ok($users, 'Users retrieved successfully');
    }

    /**
     * Get a specific user (admin only).
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        return ApiResponse::ok([
            'user' => $user,
        ], 'User retrieved successfully');
    }

    /**
     * Update current user's own profile.
     */
    public function updateProfile(UpdateUserRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::ok([
            'user' => $user,
        ], 'Profile updated successfully');
    }

    /**
     * Update any user (admin only).
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->updateUser(
            $id,
            $request->validated()
        );

        return ApiResponse::ok([
            'user' => $user,
        ], 'User updated successfully');
    }

    /**
     * Toggle user permission/admin status (Testing purposes).
     */
    public function updatePermission(int $id): JsonResponse
    {
        $user = $this->userService->updatePermission($id);

        return ApiResponse::ok([
            'user' => $user,
        ], 'User permission updated successfully');
    }
}
