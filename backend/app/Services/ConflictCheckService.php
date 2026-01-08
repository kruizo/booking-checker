<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use Illuminate\Support\Collection;

class ConflictCheckService
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {
    }

    /**
     * Generate a comprehensive conflict report.
     *
     * @return array<string, mixed>
     */
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

    /**
     * Find overlapping bookings (partial time overlap on same date).
     *
     * @return array<int, array<string, mixed>>
     */
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
     *
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
     *
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

    /**
     * Check if two bookings overlap.
     */
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

    /**
     * Check if two bookings are exact conflicts.
     */
    private function isExactConflict(object $booking1, object $booking2): bool
    {
        return $booking1->date->format('Y-m-d') === $booking2->date->format('Y-m-d')
            && $booking1->start_time === $booking2->start_time
            && $booking1->end_time === $booking2->end_time;
    }
}
