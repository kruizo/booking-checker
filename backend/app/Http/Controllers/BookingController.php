<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Responses\ApiResponse;
use App\Services\BookingService;
use App\Services\ConflictCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private ConflictCheckService $conflictCheckService
    ) {
    }

    /**
     * Get bookings with filters, pagination and sorting.
     * Supports: ?date=, ?date_from=, ?date_to=, ?start_time=, ?end_time=, ?keyword= (admin only)
     * Pagination: ?page=, ?per_page=, ?sort_by=, ?sort_direction=
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->only([
            'date',
            'date_from',
            'date_to',
            'start_time',
            'end_time',
            'keyword',
            'page',
            'per_page',
            'sort_by',
            'sort_direction',
        ]);

        $bookings = $this->bookingService->getAllBookings($params);
        
        return ApiResponse::ok($bookings, 'Bookings retrieved successfully');
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $bookingResource = $this->bookingService->createBooking($request->validated());

        return ApiResponse::ok(
            ['booking' => $bookingResource],
            'Booking created successfully',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $bookingResource = $this->bookingService->getBookingById($id);

        return ApiResponse::ok(
            ['booking' => $bookingResource],
            'Booking retrieved successfully'
        );
    }

    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $updatedResource = $this->bookingService->updateBooking($id, $request->validated());

        return ApiResponse::ok(
            ['booking' => $updatedResource],
            'Booking updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bookingService->deleteBooking($id);

        return ApiResponse::ok(
            null,
            'Booking deleted successfully'
        );
    }

    public function validate(int $id): JsonResponse
    {
        $bookingResource = $this->bookingService->getBookingById($id);

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

        return ApiResponse::ok([
            'booking' => $bookingResource,
            'has_conflicts' => count($relatedConflicts) > 0 || count($relatedExactConflicts) > 0,
            'overlapping' => array_values($relatedConflicts),
            'conflicts' => array_values($relatedExactConflicts),
        ], 'Booking validation completed');
    }

    public function conflicts(): JsonResponse
    {
        $report = $this->conflictCheckService->generateConflictReport();

        return ApiResponse::ok($report, 'Conflict report generated successfully');
    }
}
