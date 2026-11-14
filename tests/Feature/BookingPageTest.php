<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_page_renders_real_time_month_and_matching_package_prices(): void
    {
        $response = $this->get('/pemesanan');

        $response->assertStatus(200);

        // Real-time month and year verification
        $currentMonthYear = Carbon::now()->translatedFormat('F Y');
        $response->assertSee($currentMonthYear);

        // Ensure old hardcoded static mockup data from QA defects are completely gone
        $response->assertDontSee('Rp9.500.000');
        $response->assertDontSee('Sabtu, 17 Oktober 2026');
        $response->assertDontSee('Cerita Keluarga');

        // Verify active package price in summary matches default selected package (Family Session: Rp2.450.000)
        $response->assertSee('Family Session');
        $response->assertSee('Rp2.450.000');
    }

    public function test_booking_page_matches_selected_query_package(): void
    {
        $response = $this->get('/pemesanan?package=birthday');

        $response->assertStatus(200);
        $response->assertSee('Birthday');
        $response->assertSee('Rp1.250.000');
    }
}
