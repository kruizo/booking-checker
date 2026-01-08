<?php

namespace App\Services;

use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function register(array $data): AuthResource
    {
        $user = $this->userRepository->create($data);
        
        Auth::login($user);
        
        request()->session()->regenerate();
        $user->token = $user->createToken('auth_token')->plainTextToken;

        return new AuthResource($user);
    }

    /**
     * Login user - sets session cookie AND returns token
     * Credentials are already validated by LoginRequest
     */
    public function login(array $credentials): AuthResource
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        Auth::login($user, remember: $credentials['remember'] ?? false);
        
        request()->session()->regenerate();
        $user->token = $user->createToken('auth_token')->plainTextToken;

        return new AuthResource($user);
    }

    public function logout(Request $request): void
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
