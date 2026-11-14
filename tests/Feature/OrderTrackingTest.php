<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_tracking_page_is_empty_without_static_sample_data(): void
    {
        // Seed order in database to ensure it does not automatically display
        Order::create([
            'order_code' => 'CH-EXISTING-001',
            'invoice_number' => 'INV-CH-EXISTING-001',
            'package_name' => 'Family Session',
            'customer_name' => 'Klien Rahasia',
            'customer_email' => 'rahasia@test.com',
            'customer_whatsapp' => '081234567890',
            'total_price' => 2450000,
            'session_date' => '2026-10-25',
            'session_time_slot' => '10.00–11.30 WIB',
            'location' => 'Studio',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->get('/cek-pesanan');

        $response->assertStatus(200);
        $response->assertDontSee('Pesanan ditemukan');
        $response->assertDontSee('Contoh tampilan hasil');
        $response->assertDontSee('Kode ilustrasi');
        $response->assertDontSee('Klien Rahasia');
        $response->assertDontSee('CH-EXISTING-001');
        $response->assertSee('Temukan pesanan Anda');
        $response->assertSee('Lupa kode pesanan?');
    }

    public function test_searching_with_valid_code_shows_order_details(): void
    {
        $order = Order::create([
            'order_code' => 'CH-CARIAN-001',
            'invoice_number' => 'INV-CH-CARIAN-001',
            'package_name' => 'Graduation Session',
            'customer_name' => 'Aulia Wisuda',
            'customer_email' => 'aulia@test.com',
            'customer_whatsapp' => '081299991111',
            'total_price' => 1500000,
            'session_date' => '2026-10-30',
            'session_time_slot' => '13.00–14.30 WIB',
            'location' => 'Studio Utama',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->get('/cek-pesanan?kode='.$order->order_code);

        $response->assertStatus(200);
        $response->assertSee('Pesanan ditemukan');
        $response->assertSee('CH-CARIAN-001');
        $response->assertSee('Graduation Session');
        $response->assertSee('Aulia Wisuda');
        $response->assertDontSee('Contoh tampilan hasil');
    }

    public function test_searching_with_invoice_number_shows_order_details(): void
    {
        $order = Order::create([
            'order_code' => 'CH-INV-TEST-001',
            'invoice_number' => 'INV-CH-2026-999',
            'package_name' => 'Prewedding Session',
            'customer_name' => 'Rian Pratama',
            'customer_email' => 'rian@test.com',
            'customer_whatsapp' => '081288887777',
            'total_price' => 4800000,
            'session_date' => '2026-11-05',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Studio & Outdoor',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->get('/cek-pesanan?kode='.$order->invoice_number);

        $response->assertStatus(200);
        $response->assertSee('Pesanan ditemukan');
        $response->assertSee('CH-INV-TEST-001');
        $response->assertSee('INV-CH-2026-999');
        $response->assertSee('Rian Pratama');
    }

    public function test_searching_with_invalid_code_shows_not_found(): void
    {
        $response = $this->get('/cek-pesanan?kode=KODE-NGASAL-999');

        $response->assertStatus(200);
        $response->assertSee('Pesanan tidak ditemukan');
        $response->assertDontSee('Pesanan ditemukan');
    }
}
