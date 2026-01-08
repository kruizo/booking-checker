<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use App\Services\ConflictCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private ConflictCheckService $conflictCheckService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->is_admin) {
            $bookings = $this->bookingService->getAllBookings();
        } else {
            $bookings = $this->bookingService->getUserBookings($user->id);
        }

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $booking = $this->bookingService->createBooking($data);

        return response()->json([
            'booking' => new BookingResource($booking),
            'message' => 'Booking created successfully',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->findBooking($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        // Check authorization
        if (!$request->user()->is_admin && $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'booking' => new BookingResource($booking),
        ]);
    }

    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->findBooking($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        if (!$request->user()->is_admin && $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->bookingService->updateBooking($booking, $request->validated());

        return response()->json([
            'booking' => new BookingResource($booking->fresh()),
            'message' => 'Booking updated successfully',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->findBooking($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        if (!$request->user()->is_admin && $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->bookingService->deleteBooking($booking);

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }

    public function validate(int $id): JsonResponse
    {
        $booking = $this->bookingService->findBooking($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        $report = $this->conflictCheckService->generateConflictReport();

        $relatedConflicts = array_filter(
            $report['overlapping'],
            fn ($conflict) => $conflict['booking_1']['id'] === $id
                || $conflict['booking_2']['id'] === $id
        );

        $relatedExactConflicts = array_filter(
            $report['conflicts'],
            fn ($conflict) => $conflict['booking_1']['id'] === $id
                || $conflict['booking_2']['id'] === $id
        );

        return response()->json([
            'booking' => new BookingResource($booking),
            'has_conflicts' => count($relatedConflicts) > 0 || count($relatedExactConflicts) > 0,
            'overlapping' => array_values($relatedConflicts),
            'conflicts' => array_values($relatedExactConflicts),
        ]);
    }

    public function conflicts(): JsonResponse
    {
        $report = $this->conflictCheckService->generateConflictReport();

        return response()->json($report);
    }
}
