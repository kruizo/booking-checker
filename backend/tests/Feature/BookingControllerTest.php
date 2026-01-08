<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->otherUser = User::factory()->create();
    }

    // ==========================================
    // INDEX (LIST) TESTS
    // ==========================================

    public function test_user_can_list_their_own_bookings(): void
    {
        Booking::factory()->count(3)->for($this->user)->create();
        Booking::factory()->count(2)->for($this->otherUser)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'total_pages',
                ],
            ]);

        // User should only see their own 3 bookings
        $this->assertEquals(3, $response->json('pagination.total'));
    }

    public function test_admin_can_list_all_bookings(): void
    {
        Booking::factory()->count(3)->for($this->user)->create();
        Booking::factory()->count(2)->for($this->otherUser)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/bookings');

        $response->assertStatus(200);
        
        // Admin should see all 5 bookings
        $this->assertEquals(5, $response->json('pagination.total'));
    }

    public function test_bookings_can_be_filtered_by_date(): void
    {
        $targetDate = now()->addDays(5)->format('Y-m-d');
        
        Booking::factory()->for($this->user)->onDate($targetDate)->create();
        Booking::factory()->count(2)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings?date={$targetDate}");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_bookings_can_be_filtered_by_date_range(): void
    {
        $dateFrom = now()->addDays(5)->format('Y-m-d');
        $dateTo = now()->addDays(10)->format('Y-m-d');
        
        Booking::factory()->for($this->user)->onDate(now()->addDays(7)->format('Y-m-d'))->create();
        Booking::factory()->for($this->user)->onDate(now()->addDays(20)->format('Y-m-d'))->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_admin_can_search_bookings_by_keyword(): void
    {
        $searchableUser = User::factory()->create(['name' => 'John Searchable']);
        Booking::factory()->for($searchableUser)->create();
        Booking::factory()->count(2)->for($this->user)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/bookings?keyword=Searchable');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_bookings_support_pagination(): void
    {
        Booking::factory()->count(25)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/bookings?per_page=10&page=1');

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(25, $response->json('pagination.total'));
        $this->assertEquals(3, $response->json('pagination.total_pages'));
    }

    public function test_bookings_support_sorting(): void
    {
        Booking::factory()->for($this->user)->onDate(now()->addDays(5)->format('Y-m-d'))->create();
        Booking::factory()->for($this->user)->onDate(now()->addDays(1)->format('Y-m-d'))->create();
        Booking::factory()->for($this->user)->onDate(now()->addDays(10)->format('Y-m-d'))->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/bookings?sort_by=date&sort_direction=asc');

        $response->assertStatus(200);
        
        $dates = array_column($response->json('data'), 'date');
        $sortedDates = $dates;
        sort($sortedDates);
        
        $this->assertEquals($sortedDates, $dates);
    }

    public function test_unauthenticated_user_cannot_list_bookings(): void
    {
        $response = $this->getJson('/api/v1/bookings');

        $response->assertStatus(401);
    }

    // ==========================================
    // STORE (CREATE) TESTS
    // ==========================================

    public function test_user_can_create_booking(): void
    {
        $bookingData = [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_booking_creation_fails_with_end_time_before_start_time(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_booking_creation_fails_with_missing_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'start_time', 'end_time']);
    }

    public function test_booking_creation_fails_with_invalid_date_format(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'date' => 'invalid-date',
                'start_time' => '09:00',
                'end_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_booking_creation_fails_with_invalid_time_format(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => 'invalid',
                'end_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    // ==========================================
    // SHOW (GET SINGLE) TESTS
    // ==========================================

    public function test_user_can_view_their_own_booking(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.booking.id', $booking->id);
    }

    public function test_user_cannot_view_other_users_booking(): void
    {
        $booking = Booking::factory()->for($this->otherUser)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_booking(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(200);
    }

    public function test_viewing_nonexistent_booking_returns_404(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/bookings/99999');

        $response->assertStatus(404);
    }

    // ==========================================
    // UPDATE TESTS
    // ==========================================

    public function test_user_can_update_their_own_booking(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/bookings/{$booking->id}", [
                'date' => now()->addDays(10)->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '12:00',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'start_time' => '11:00',
        ]);
    }

    public function test_user_cannot_update_other_users_booking(): void
    {
        $booking = Booking::factory()->for($this->otherUser)->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/bookings/{$booking->id}", [
                'date' => now()->addDays(10)->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '12:00',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_booking(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/bookings/{$booking->id}", [
                'date' => now()->addDays(10)->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '12:00',
            ]);

        $response->assertStatus(200);
    }

    // ==========================================
    // DELETE TESTS
    // ==========================================

    public function test_user_can_delete_their_own_booking(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('bookings', ['id' => $booking->id]);
    }

    public function test_user_cannot_delete_other_users_booking(): void
    {
        $booking = Booking::factory()->for($this->otherUser)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_booking(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(200);
    }

    // ==========================================
    // VALIDATE (CONFLICT CHECK) TESTS
    // ==========================================

    public function test_user_can_validate_their_booking_for_conflicts(): void
    {
        $booking = Booking::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$booking->id}/validate");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'has_conflicts',
                ],
            ]);
    }

    // ==========================================
    // ADMIN CONFLICTS REPORT TESTS
    // ==========================================

    public function test_admin_can_get_conflict_report(): void
    {
        Booking::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/conflicts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'overlapping',
                    'conflicts',
                    'gaps',
                    'summary',
                ],
            ]);
    }

    public function test_regular_user_cannot_access_conflict_report(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/admin/conflicts');

        $response->assertStatus(403);
    }
}
