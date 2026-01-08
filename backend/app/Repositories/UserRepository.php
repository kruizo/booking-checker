<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * Get all users with optional search, pagination and sorting.
     */
    public function getAll(array $params = []): LengthAwarePaginator
    {
        $query = User::query();

        // Keyword search
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDirection = $params['sort_direction'] ?? 'desc';
        
        $allowedSortFields = ['id', 'name', 'email', 'created_at', 'is_admin'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');

        $perPage = min((int) ($params['per_page'] ?? 15), 100);
        
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get recent signups.
     */
    public function getRecentSignups(int $limit = 5): Collection
    {
        return User::latest()->take($limit)->get();
    }

    /**
     * Count signups for a specific date.
     */
    public function countSignupsByDate(string $date): int
    {
        return User::whereDate('created_at', $date)->count();
    }

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => $data['is_admin'] ?? false,
        ]);
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find a user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update user profile.
     *
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }

    /**
     * Update user permission (admin status).
     */
    public function updatePermission(User $user, bool $isAdmin): User
    {
        $user->is_admin = $isAdmin;
        $user->save();

        return $user;
    }
}
