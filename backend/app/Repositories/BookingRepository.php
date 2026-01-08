<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    /**
     * Get all bookings.
     */
    public function getAll(): Collection
    {
        return Booking::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get bookings for a specific user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Booking::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();
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
     * Get bookings for a specific date range.
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
