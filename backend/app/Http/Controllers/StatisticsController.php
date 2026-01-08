<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {
    }

    /**
     * Get statistics with period filter (daily, weekly, yearly)
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->query('period', 'daily');

        // Validate period
        if (!in_array($period, ['daily', 'weekly', 'yearly'])) {
            $period = 'daily';
        }

        $statistics = $this->statisticsService->getStatistics($period);

        return ApiResponse::ok($statistics, 'Statistics retrieved successfully');
    }
}
