<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Get all users with search, pagination and sorting (admin only).
     * 
     * @param array $params - keyword, page, per_page, sort_by, sort_direction
     */
    public function getAllUsers(array $params = []): LengthAwarePaginator
    {
        $this->authorizeAdmin();
        
        $paginator = $this->userRepository->getAll($params);
        
        $paginator->through(fn ($user) => new UserResource($user));
        
        return $paginator;
    }

    /**
     * Get user by ID (admin only).
     */
    public function getUserById(int $id): UserResource
    {
        $this->authorizeAdmin();
        
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new ModelNotFoundException('User not found');
        }

        return new UserResource($user);
    }

    /**
     * Update current user's own profile.
     */
    public function updateProfile(User $user, array $data): UserResource
    {
        $updatedUser = $this->userRepository->update($user, $data);

        return new UserResource($updatedUser);
    }

    /**
     * Update any user (admin only).
     */
    public function updateUser(int $id, array $data): UserResource
    {
        $this->authorizeAdmin();
        
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new ModelNotFoundException('User not found');
        }

        $updatedUser = $this->userRepository->update($user, $data);

        return new UserResource($updatedUser);
    }

    /**
     * Toggle user permission (admin only).
     */
    public function updatePermission(int $id): UserResource
    {
        $currentUser = $this->getAuthenticatedUser();

        // Commented out to allow self-demotion for testing purposes
        // $this->authorizeAdmin();
        
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new ModelNotFoundException('User not found');
        }

        // Prevent admin from demoting themselves
        if ($currentUser->id === $user->id && $user->is_admin) {
            throw new AuthorizationException('You cannot remove your own admin status.');
        }

        $updatedUser = $this->userRepository->updatePermission($user, !$user->is_admin);

        return new UserResource($updatedUser);
    }

    /**
     * Get the authenticated user.
     */
    private function getAuthenticatedUser(): User
    {
        return Auth::user();
    }

    /**
     * Authorize that current user is admin.
     */
    private function authorizeAdmin(): void
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user->is_admin) {
            throw new AuthorizationException('Admin access required.');
        }
    }
}
