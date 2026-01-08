<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\BookingRepository;
use App\Services\ConflictCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ConflictCheckServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConflictCheckService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->service = app(ConflictCheckService::class);
    }

    // ==========================================
    // NO OVERLAP VALIDATION TESTS
    // ==========================================

    public function test_no_exception_when_no_overlap_exists(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '11:00',
            '12:00'
        );

        $this->assertTrue(true); 
    }

    public function test_exception_thrown_when_exact_overlap(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        $this->expectException(ValidationException::class);

        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '09:00',
            '10:00'
        );
    }

    public function test_exception_thrown_when_new_booking_starts_during_existing(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '11:00')
            ->create();

        $this->expectException(ValidationException::class);

        // New booking starts at 10:00, existing ends at 11:00
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '10:00',
            '12:00'
        );
    }

    public function test_exception_thrown_when_new_booking_ends_during_existing(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('10:00', '12:00')
            ->create();

        $this->expectException(ValidationException::class);

        // New booking ends at 11:00, existing starts at 10:00
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '08:00',
            '11:00'
        );
    }

    public function test_exception_thrown_when_new_booking_contains_existing(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('10:00', '11:00')
            ->create();

        $this->expectException(ValidationException::class);

        // New booking completely contains existing
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '09:00',
            '12:00'
        );
    }

    public function test_exception_thrown_when_existing_contains_new_booking(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '12:00')
            ->create();

        $this->expectException(ValidationException::class);

        // Existing completely contains new booking
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '10:00',
            '11:00'
        );
    }

    public function test_no_overlap_when_bookings_touch_at_boundary(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        // Should not throw - new booking starts exactly when existing ends
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '10:00',
            '11:00'
        );

        $this->assertTrue(true);
    }

    public function test_no_overlap_when_different_dates(): void
    {
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        // Should not throw - different date
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-16',
            '09:00',
            '10:00'
        );

        $this->assertTrue(true);
    }

    public function test_no_overlap_when_different_users(): void
    {
        $otherUser = User::factory()->create();
        
        Booking::factory()
            ->for($otherUser)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        // Should not throw - different user
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '09:00',
            '10:00'
        );

        $this->assertTrue(true);
    }

    public function test_exclude_booking_id_skips_self_when_updating(): void
    {
        $booking = Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        // Should not throw - excluding the booking being updated
        $this->service->validateNoOverlap(
            $this->user->id,
            '2026-01-15',
            '09:00',
            '10:30',
            $booking->id
        );

        $this->assertTrue(true);
    }

    // ==========================================
    // CONFLICT REPORT TESTS
    // ==========================================

    public function test_generate_conflict_report_returns_correct_structure(): void
    {
        Booking::factory()->count(5)->create();

        $report = $this->service->generateConflictReport();

        $this->assertArrayHasKey('overlapping', $report);
        $this->assertArrayHasKey('conflicts', $report);
        $this->assertArrayHasKey('gaps', $report);
        $this->assertArrayHasKey('summary', $report);
        
        $this->assertArrayHasKey('total_bookings', $report['summary']);
        $this->assertArrayHasKey('overlapping_count', $report['summary']);
        $this->assertArrayHasKey('conflict_count', $report['summary']);
        $this->assertArrayHasKey('gap_count', $report['summary']);
    }

    public function test_detect_overlapping_bookings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create overlapping bookings for different users on same date
        Booking::factory()
            ->for($user1)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '11:00')
            ->create();

        Booking::factory()
            ->for($user2)
            ->onDate('2026-01-15')
            ->withTimes('10:00', '12:00')
            ->create();

        $report = $this->service->generateConflictReport();

        $this->assertCount(1, $report['overlapping']);
        $this->assertEquals(1, $report['summary']['overlapping_count']);
    }

    public function test_no_overlapping_when_bookings_dont_overlap(): void
    {
        // Create non-overlapping bookings
        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('09:00', '10:00')
            ->create();

        Booking::factory()
            ->for($this->user)
            ->onDate('2026-01-15')
            ->withTimes('11:00', '12:00')
            ->create();

        $report = $this->service->generateConflictReport();

        $this->assertEmpty($report['overlapping']);
        $this->assertEquals(0, $report['summary']['overlapping_count']);
    }

    public function test_empty_report_when_no_bookings(): void
    {
        $report = $this->service->generateConflictReport();

        $this->assertEmpty($report['overlapping']);
        $this->assertEmpty($report['conflicts']);
        $this->assertEmpty($report['gaps']);
        $this->assertEquals(0, $report['summary']['total_bookings']);
    }
}
