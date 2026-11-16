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

    public function test_booking_page_reflects_admin_created_studio_slots(): void
    {
        $targetDate = Carbon::today()->addDay();
        $targetDateStr = $targetDate->format('Y-m-d');

        // Buat slot kustom dari admin di StudioSlot
        \App\Models\StudioSlot::create([
            'date' => $targetDateStr,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'type' => 'available',
            'note' => 'Slot Pagi Admin',
        ]);

        $response = $this->get('/pemesanan');
        $response->assertStatus(200);

        // Pastikan view memuat slot 10.00 dalam data JSON kalender
        $response->assertSee('10.00');
        $response->assertSee('Slot Pagi Admin');
    }

    public function test_booking_submission_syncs_with_studio_slot_and_calculates_exact_duration(): void
    {
        $targetDate = Carbon::today()->addDays(2);
        $targetDateStr = $targetDate->format('Y-m-d');

        $slot = \App\Models\StudioSlot::create([
            'date' => $targetDateStr,
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'type' => 'available',
            'note' => 'Slot Siang',
        ]);

        // Paket Birthday memiliki durasi 60 menit
        $response = $this->post('/pemesanan', [
            'package_id' => 'birthday',
            'session_date' => $targetDateStr,
            'session_time' => '14.00',
            'nama_lengkap' => 'Tasya Kamila',
            'email' => 'tasya@example.com',
            'whatsapp' => '081234567890',
            'lokasi' => 'Studio naro capture',
            'catatan' => 'Sesi foto ultah tema pastel',
            'persetujuan' => '1',
        ]);

        $response->assertRedirect(route('checkout'));

        // Cek order
        $order = \App\Models\Order::where('customer_name', 'Tasya Kamila')->first();
        $this->assertNotNull($order);
        // Durasi 60 menit dari jam 14.00 adalah 15.00
        $this->assertStringContainsString('14.00–15.00 WIB · 60 menit', $order->session_time_slot);

        // Cek bahwa slot studio otomatis diupdate menjadi booked
        $slot->refresh();
        $this->assertEquals('booked', $slot->type);
        $this->assertEquals($order->id, $slot->order_id);
    }

    public function test_booking_submission_fails_when_required_fields_are_missing(): void
    {
        // 1. Kirim payload kosong
        $response = $this->post('/pemesanan', []);
        $response->assertSessionHasErrors([
            'package_id',
            'session_date',
            'session_time',
            'nama_lengkap',
            'email',
            'whatsapp',
            'lokasi',
            'persetujuan',
        ]);
        $this->assertEquals(0, \App\Models\Order::count());

        // 2. Kirim tanpa lokasi dan tanpa persetujuan
        $response2 = $this->post('/pemesanan', [
            'package_id' => 'birthday',
            'session_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
            'session_time' => '10.00',
            'nama_lengkap' => 'Ahmad Dani',
            'email' => 'ahmad@example.com',
            'whatsapp' => '081234567890',
            'lokasi' => '', // kosong
            'persetujuan' => '', // belum centang
        ]);
        $response2->assertSessionHasErrors(['lokasi', 'persetujuan']);
        $this->assertEquals(0, \App\Models\Order::count());

        // 3. Kirim dengan email tidak valid dan whatsapp kurang digit
        $response3 = $this->post('/pemesanan', [
            'package_id' => 'birthday',
            'session_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
            'session_time' => '10.00',
            'nama_lengkap' => 'Ahmad Dani',
            'email' => 'bukan-email-valid',
            'whatsapp' => '1234',
            'lokasi' => 'Studio naro capture',
            'persetujuan' => '1',
        ]);
        $response3->assertSessionHasErrors(['email', 'whatsapp']);
        $this->assertEquals(0, \App\Models\Order::count());
    }
}
