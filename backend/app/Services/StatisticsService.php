<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * Get statistics based on period type (daily, weekly, yearly)
     */
    public function getStatistics(string $period = 'daily'): array
    {
        $intervals = $this->getDateIntervals($period);
        
        $bookingStats = $this->getBookingCountsByInterval($intervals);
        $signupStats = $this->getSignupCountsByInterval($intervals);
        $recentSignups = $this->getRecentSignups(5);

        return [
            'period' => $period,
            'intervals' => $this->formatStatistics($intervals, $bookingStats, $signupStats),
            'recent_signups' => $recentSignups,
            'summary' => [
                'total_bookings' => array_sum($bookingStats),
                'total_signups' => array_sum($signupStats),
            ],
        ];
    }

    /**
     * Get date intervals based on period type
     */
    private function getDateIntervals(string $period): array
    {
        $intervals = [];
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                // Last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $date = $now->copy()->subDays($i);
                    $intervals[] = [
                        'label' => $date->format('Y-m-d'),
                        'start' => $date->copy()->startOfDay(),
                        'end' => $date->copy()->endOfDay(),
                    ];
                }
                break;

            case 'weekly':
                // Last 7 weeks
                for ($i = 6; $i >= 0; $i--) {
                    $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                    $weekEnd = $weekStart->copy()->endOfWeek();
                    $intervals[] = [
                        'label' => 'Week of ' . $weekStart->format('M d'),
                        'start' => $weekStart,
                        'end' => $weekEnd,
                    ];
                }
                break;

            case 'yearly':
                // Last 7 months (more practical than 7 years)
                for ($i = 6; $i >= 0; $i--) {
                    $monthStart = $now->copy()->subMonths($i)->startOfMonth();
                    $monthEnd = $monthStart->copy()->endOfMonth();
                    $intervals[] = [
                        'label' => $monthStart->format('M Y'),
                        'start' => $monthStart,
                        'end' => $monthEnd,
                    ];
                }
                break;

            default:
                // Default to daily
                for ($i = 6; $i >= 0; $i--) {
                    $date = $now->copy()->subDays($i);
                    $intervals[] = [
                        'label' => $date->format('Y-m-d'),
                        'start' => $date->copy()->startOfDay(),
                        'end' => $date->copy()->endOfDay(),
                    ];
                }
        }

        return $intervals;
    }

    /**
     * Get booking counts for each interval
     */
    private function getBookingCountsByInterval(array $intervals): array
    {
        $counts = [];

        foreach ($intervals as $interval) {
            $counts[] = Booking::whereBetween('created_at', [
                $interval['start'],
                $interval['end'],
            ])->count();
        }

        return $counts;
    }

    /**
     * Get signup counts for each interval
     */
    private function getSignupCountsByInterval(array $intervals): array
    {
        $counts = [];

        foreach ($intervals as $interval) {
            $counts[] = User::whereBetween('created_at', [
                $interval['start'],
                $interval['end'],
            ])->count();
        }

        return $counts;
    }

    /**
     * Get recent signups
     */
    private function getRecentSignups(int $limit): Collection
    {
        return User::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'created_at']);
    }

    /**
     * Format statistics into a combined array
     */
    private function formatStatistics(array $intervals, array $bookingCounts, array $signupCounts): array
    {
        $formatted = [];

        foreach ($intervals as $index => $interval) {
            $formatted[] = [
                'date' => $interval['label'],
                'bookings' => $bookingCounts[$index],
                'signups' => $signupCounts[$index],
            ];
        }

        return $formatted;
    }
}
