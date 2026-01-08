<?php

namespace App\Services;

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

    /**
     * Register a new user.
     *
     * @param array<string, mixed> $data
     */
    public function register(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * Authenticate user and return token.
     *
     * @param array<string, mixed> $credentials
     * @throws ValidationException
     */
    public function login(array $credentials): string
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        return $user->createToken('auth-token')->plainTextToken;
    }

    /**
     * Logout user by revoking all tokens.
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
