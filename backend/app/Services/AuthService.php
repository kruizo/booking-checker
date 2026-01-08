<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function register(array $data): UserResource
    {
        $user = $this->userRepository->create($data);
        
        return new UserResource($user);
    }

    public function login(array $credentials): UserResource
    {
        $user = $this->userRepository->findByEmail($credentials['email']);
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();

        return new UserResource($user);
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
