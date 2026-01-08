<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingRepository
{
    /**
     * Get all bookings with optional filters, pagination and sorting.
     */
    public function getAll(array $params = [], bool $isAdmin = false): LengthAwarePaginator
    {
        $query = Booking::with('user');
        
        $this->applyFilters($query, $params, $isAdmin);

        // Sorting
        $sortBy = $params['sort_by'] ?? 'date';
        $sortDirection = $params['sort_direction'] ?? 'desc';
        
        $allowedSortFields = ['id', 'date', 'start_time', 'end_time', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date';
        }
        
        $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        
        if ($sortBy === 'date') {
            $query->orderBy('start_time', 'asc');
        }

        $perPage = min((int) ($params['per_page'] ?? 15), 100);
        
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get bookings for a specific user with optional filters.
     */
    public function getByUserId(int $userId, array $params = []): LengthAwarePaginator
    {
        $params['user_id'] = $userId;
        
        return $this->getAll($params, false);
    }

    /**
     * Apply search filters to query.
     */
    private function applyFilters(Builder $query, array $filters, bool $isAdmin = false): void
    {
        // Filter by user ID
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Date filters
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        // Time filters
        if (!empty($filters['start_time'])) {
            $query->where('start_time', '>=', $filters['start_time']);
        }

        if (!empty($filters['end_time'])) {
            $query->where('end_time', '<=', $filters['end_time']);
        }

        // Admin-only: keyword search for user name or email
        if ($isAdmin && !empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
    }

    /**
     * Count bookings for a specific date.
     */
    public function countByDate(string $date): int
    {
        return Booking::whereDate('date', $date)->count();
    }

    /**
     * Find a booking by ID.
     */
    public function findById(int $id): ?Booking
    {
        return Booking::with('user')->find($id);
    }

    /**
     * Create a new booking.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    /**
     * Update a booking.
     *
     * @param array<string, mixed> $data
     */
    public function update(Booking $booking, array $data): bool
    {
        return $booking->update($data);
    }

    /**
     * Delete a booking.
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }

    /**
     * Get bookings for a specific date range (non-paginated, for internal use).
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return Booking::with('user')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get bookings older than specified days.
     */
    public function getOlderThan(int $days): Collection
    {
        return Booking::where('date', '<', now()->subDays($days)->toDateString())
            ->get();
    }

    /**
     * Delete bookings older than specified days.
     */
    public function deleteOlderThan(int $days): int
    {
        return Booking::where('date', '<', now()->subDays($days)->toDateString())
            ->delete();
    }

    /**
     * Get all bookings for conflict checking.
     */
    public function getAllForConflictCheck(): Collection
    {
        return Booking::with('user')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }
}
