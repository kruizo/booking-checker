<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\BookingRepository;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {
    }

    /**
     * Get all bookings for admin.
     */
    public function getAllBookings(): Collection
    {
        return $this->bookingRepository->getAll();
    }

    /**
     * Get bookings for a specific user.
     */
    public function getUserBookings(int $userId): Collection
    {
        return $this->bookingRepository->getByUserId($userId);
    }

    /**
     * Find a booking by ID.
     */
    public function findBooking(int $id): ?Booking
    {
        return $this->bookingRepository->findById($id);
    }

    /**
     * Create a new booking.
     *
     * @param array<string, mixed> $data
     */
    public function createBooking(array $data): Booking
    {
        return $this->bookingRepository->create($data);
    }

    /**
     * Update a booking.
     *
     * @param array<string, mixed> $data
     */
    public function updateBooking(Booking $booking, array $data): bool
    {
        return $this->bookingRepository->update($booking, $data);
    }

    /**
     * Delete a booking.
     */
    public function deleteBooking(Booking $booking): bool
    {
        return $this->bookingRepository->delete($booking);
    }

    /**
     * Delete old bookings (older than 30 days).
     */
    public function deleteOldBookings(): int
    {
        return $this->bookingRepository->deleteOlderThan(30);
    }
}
