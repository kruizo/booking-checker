<?php

namespace App\Services;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\BookingRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private ConflictCheckService $conflictCheckService
    ) {
    }

    /**
     * Get all bookings with filters, pagination and sorting.
     * Admin sees all, regular users only see their own.
     * 
     * @param array $params - date, date_from, date_to, start_time, end_time, keyword, page, per_page, sort_by, sort_direction
     */
    public function getAllBookings(array $params = []): LengthAwarePaginator
    {
        $user = $this->getAuthenticatedUser();
        $isAdmin = (bool) $user->is_admin;

        if (!$isAdmin) {
            $params['user_id'] = $user->id;
        }

        $paginator = $this->bookingRepository->getAll($params, $isAdmin);
        
        $paginator->through(fn ($booking) => new BookingResource($booking));
        
        return $paginator;
    }


    public function getBookingById(int $id): BookingResource
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            throw new ModelNotFoundException('Booking not found');
        }

        $this->authorizeBookingAccess($booking);

        return new BookingResource($booking);
    }

    public function createBooking(array $data): BookingResource
    {
        $user = $this->getAuthenticatedUser();
        $data['user_id'] = $user->id;

        $this->validateTimeLogic($data['start_time'], $data['end_time']);

        // $this->conflictCheckService->validateNoOverlap(
        //     $data['user_id'],
        //     $data['date'],
        //     $data['start_time'],
        //     $data['end_time']
        // );

        $booking = $this->bookingRepository->create($data);
        
        return new BookingResource($booking);
    }

    public function updateBooking(int $id, array $data): BookingResource
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            throw new ModelNotFoundException('Booking not found');
        }

        $this->authorizeBookingAccess($booking);

        if (isset($data['start_time']) && isset($data['end_time'])) {
            $this->validateTimeLogic($data['start_time'], $data['end_time']);
        }

        // Check for overlap if date/time changing
        if (isset($data['date']) || isset($data['start_time']) || isset($data['end_time'])) {
            $this->conflictCheckService->validateNoOverlap(
                $booking->user_id,
                $data['date'] ?? $booking->date->format('Y-m-d'),
                $data['start_time'] ?? $booking->start_time,
                $data['end_time'] ?? $booking->end_time,
                $booking->id
            );
        }

        $this->bookingRepository->update($booking, $data);
        
        return new BookingResource($booking->fresh());
    }

    public function deleteBooking(int $id): bool
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            throw new ModelNotFoundException('Booking not found');
        }

        $this->authorizeBookingAccess($booking);

        return $this->bookingRepository->delete($booking);
    }

    public function deleteOldBookings(): int
    {
        return $this->bookingRepository->deleteOlderThan(30);
    }


    private function validateTimeLogic(string $startTime, string $endTime): void
    {
        $start = strtotime($startTime);
        $end = strtotime($endTime);

        if ($end <= $start) {
            throw ValidationException::withMessages([
                'end_time' => ['End time must be after start time.'],
            ]);
        }
    }

    private function getAuthenticatedUser(): User
    {
        return Auth::user();
    }


    private function authorizeBookingAccess(Booking $booking): void
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->is_admin && $booking->user_id !== $user->id) {
            throw new AuthorizationException('Unauthorized to access this booking.');
        }
    }

}
