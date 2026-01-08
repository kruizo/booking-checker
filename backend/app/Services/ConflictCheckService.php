<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ConflictCheckService
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {
    }

    /**
     * Validate that a booking doesn't overlap with existing bookings.
     *
     * @throws ValidationException
     */
    public function validateNoOverlap(
        int $userId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): void {
        $existingBookings = $this->bookingRepository->getByUserId($userId);

        $start = strtotime($startTime);
        $end = strtotime($endTime);

        foreach ($existingBookings as $existing) {
            // Skip if this is the booking being updated
            if ($excludeBookingId && $existing->id === $excludeBookingId) {
                continue;
            }

            // Check if same date
            if ($existing->date->format('Y-m-d') !== $date) {
                continue;
            }

            $existingStart = strtotime($existing->start_time);
            $existingEnd = strtotime($existing->end_time);

            // Check for time overlap
            if ($start < $existingEnd && $end > $existingStart) {
                throw ValidationException::withMessages([
                    'date' => ['You already have a booking that overlaps with this time slot.'],
                ]);
            }
        }
    }

    public function generateConflictReport(): array
    {
        $bookings = $this->bookingRepository->getAllForConflictCheck();

        $overlapping = $this->findOverlappingBookings($bookings);
        $conflicts = $this->findConflictingBookings($bookings);
        $gaps = $this->findGapsBetweenBookings($bookings);

        return [
            'overlapping' => $overlapping,
            'conflicts' => $conflicts,
            'gaps' => $gaps,
            'summary' => [
                'total_bookings' => $bookings->count(),
                'overlapping_count' => count($overlapping),
                'conflict_count' => count($conflicts),
                'gap_count' => count($gaps),
            ],
        ];
    }


    private function findOverlappingBookings(Collection $bookings): array
    {
        $overlapping = [];

        foreach ($bookings as $i => $booking1) {
            foreach ($bookings as $j => $booking2) {
                if ($i >= $j) {
                    continue;
                }

                if ($this->isOverlapping($booking1, $booking2)) {
                    $overlapping[] = [
                        'booking_1' => [
                            'id' => $booking1->id,
                            'user' => $booking1->user->name,
                            'date' => $booking1->date->format('Y-m-d'),
                            'start_time' => $booking1->start_time,
                            'end_time' => $booking1->end_time,
                        ],
                        'booking_2' => [
                            'id' => $booking2->id,
                            'user' => $booking2->user->name,
                            'date' => $booking2->date->format('Y-m-d'),
                            'start_time' => $booking2->start_time,
                            'end_time' => $booking2->end_time,
                        ],
                        'overlap_type' => 'partial',
                    ];
                }
            }
        }

        return $overlapping;
    }

    /**
     * Find conflicting bookings (exact same date and time).
     * @return array<int, array<string, mixed>>
     */
    private function findConflictingBookings(Collection $bookings): array
    {
        $conflicts = [];

        foreach ($bookings as $i => $booking1) {
            foreach ($bookings as $j => $booking2) {
                if ($i >= $j) {
                    continue;
                }

                if ($this->isExactConflict($booking1, $booking2)) {
                    $conflicts[] = [
                        'booking_1' => [
                            'id' => $booking1->id,
                            'user' => $booking1->user->name,
                            'date' => $booking1->date->format('Y-m-d'),
                            'start_time' => $booking1->start_time,
                            'end_time' => $booking1->end_time,
                        ],
                        'booking_2' => [
                            'id' => $booking2->id,
                            'user' => $booking2->user->name,
                            'date' => $booking2->date->format('Y-m-d'),
                            'start_time' => $booking2->start_time,
                            'end_time' => $booking2->end_time,
                        ],
                        'conflict_type' => 'exact_match',
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Find gaps between consecutive bookings on the same date.
     * @return array<int, array<string, mixed>>
     */
    private function findGapsBetweenBookings(Collection $bookings): array
    {
        $gaps = [];
        $groupedByDate = $bookings->groupBy(fn ($booking) => $booking->date->format('Y-m-d'));

        foreach ($groupedByDate as $date => $dateBookings) {
            $sorted = $dateBookings->sortBy('start_time')->values();

            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $current = $sorted[$i];
                $next = $sorted[$i + 1];

                $currentEndTime = strtotime($current->end_time);
                $nextStartTime = strtotime($next->start_time);

                if ($nextStartTime > $currentEndTime) {
                    $gapMinutes = ($nextStartTime - $currentEndTime) / 60;

                    $gaps[] = [
                        'date' => $date,
                        'between_bookings' => [
                            [
                                'id' => $current->id,
                                'user' => $current->user->name,
                                'end_time' => $current->end_time,
                            ],
                            [
                                'id' => $next->id,
                                'user' => $next->user->name,
                                'start_time' => $next->start_time,
                            ],
                        ],
                        'gap_duration_minutes' => $gapMinutes,
                        'gap_start' => $current->end_time,
                        'gap_end' => $next->start_time,
                    ];
                }
            }
        }

        return $gaps;
    }

    private function isOverlapping(object $booking1, object $booking2): bool
    {
        if ($booking1->date->format('Y-m-d') !== $booking2->date->format('Y-m-d')) {
            return false;
        }

        $start1 = strtotime($booking1->start_time);
        $end1 = strtotime($booking1->end_time);
        $start2 = strtotime($booking2->start_time);
        $end2 = strtotime($booking2->end_time);

        // Check for any overlap (excluding exact matches)
        $hasOverlap = ($start1 < $end2 && $end1 > $start2);
        $isExact = ($start1 === $start2 && $end1 === $end2);

        return $hasOverlap && !$isExact;
    }

    private function isExactConflict(object $booking1, object $booking2): bool
    {
        return $booking1->date->format('Y-m-d') === $booking2->date->format('Y-m-d')
            && $booking1->start_time === $booking2->start_time
            && $booking1->end_time === $booking2->end_time;
    }
}
