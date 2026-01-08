<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class DeleteOldBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete bookings older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(BookingService $bookingService): int
    {
        $this->info('Deleting bookings older than 30 days...');

        $deletedCount = $bookingService->deleteOldBookings();

        $this->info("Deleted {$deletedCount} old booking(s).");

        return Command::SUCCESS;
    }
}
