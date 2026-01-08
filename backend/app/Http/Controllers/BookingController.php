<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
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
        if ($request->user()->is_admin) {
            return $this->bookingService->getAllBookings();
        }

        return $this->bookingService->getUserBookings();
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $bookingResource = $this->bookingService->createBooking($request->validated());

        return response()->json([
            'booking' => $bookingResource,
            'message' => 'Booking created successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $bookingResource = $this->bookingService->findBooking($id);

        return response()->json([
            'booking' => $bookingResource,
        ]);
    }

    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->findBookingModel($id);
        $updatedResource = $this->bookingService->updateBooking($booking, $request->validated());

        return response()->json([
            'booking' => $updatedResource,
            'message' => 'Booking updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bookingService->deleteBooking($id);

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }

    public function validate(int $id): JsonResponse
    {
        $bookingResource = $this->bookingService->findBooking($id, checkAuthorization: false);

        $report = $this->conflictCheckService->generateConflictReport();

        // Filter conflicts related to this booking
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
            'booking' => $bookingResource,
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
