<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): User
    {
        $token = 'test-admin-token-123';
        $user = User::factory()->create([
            'remember_token' => $token,
        ]);

        $this->actingAs($user)->withCookie('admin_auth_token', $token);

        return $user;
    }

    public function test_order_detail_shows_pending_proof_state_when_unpaid(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-UNPAID',
            'invoice_number' => 'INV-CH-2026-UNPAID',
            'package_name' => 'Graduation Session',
            'customer_name' => 'Dinda Putri',
            'customer_email' => 'dinda@test.com',
            'customer_whatsapp' => '081233332222',
            'total_price' => 1500000,
            'session_date' => '2026-11-10',
            'session_time_slot' => '10.00–11.30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->get('/admin/pesanan/'.$order->order_code);

        $response->assertStatus(200);
        $response->assertSee('Belum dibayar');
        $response->assertSee('Pelanggan belum mengirimkan bukti pembayaran.');
        $response->assertSee('Bukti transfer · Belum dikirim');
        $response->assertDontSee('Lihat bukti pembayaran');
        $response->assertDontSee('5 Okt · Bukti diterima');
    }

    public function test_order_detail_shows_proof_button_when_proof_submitted(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-PAID',
            'invoice_number' => 'INV-CH-2026-PAID',
            'package_name' => 'Family Session',
            'customer_name' => 'Hendra Setiawan',
            'customer_email' => 'hendra@test.com',
            'customer_whatsapp' => '081299994444',
            'total_price' => 2450000,
            'session_date' => '2026-11-12',
            'session_time_slot' => '14.00–15.30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Permintaan',
        ]);

        Payment::create([
            'payment_code' => 'BYR-TEST-PAID',
            'order_id' => $order->id,
            'sender_name' => 'Hendra Setiawan',
            'transfer_date' => '2026-11-05',
            'bank_source' => 'BCA',
            'nominal' => 2450000,
            'proof_file' => 'payments/dummy.jpg',
            'file_original_name' => 'struk.jpg',
            'file_extension' => 'JPG',
            'status' => 'Bukti terkirim',
        ]);

        $response = $this->get('/admin/pesanan/'.$order->order_code);

        $response->assertStatus(200);
        $response->assertSee('Lihat bukti pembayaran');
        $response->assertSee('Bukti diterima');
        $response->assertDontSee('Pelanggan belum mengirimkan bukti pembayaran.');
    }

    public function test_unpaid_order_cannot_confirm_session_and_shows_locked_button(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-LOCK-01',
            'invoice_number' => 'INV-LOCK-01',
            'package_name' => 'Graduation Session',
            'customer_name' => 'Fajar Pratama',
            'customer_email' => 'fajar@test.com',
            'customer_whatsapp' => '081255556666',
            'total_price' => 1500000,
            'session_date' => '2026-11-28',
            'session_time_slot' => '10.00–11.30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->get('/admin/pesanan/'.$order->order_code);
        $response->assertStatus(200);
        $response->assertSee('Konfirmasi Sesi Terkunci');
        $response->assertSee('Konfirmasi sesi (Menunggu Pembayaran)');

        // Percobaan konfirmasi sesi saat belum bayar ditolak oleh controller
        $actionResponse = $this->post('/admin/pesanan/'.$order->order_code.'/konfirmasi-sesi');
        $actionResponse->assertRedirect('/admin/pesanan/'.$order->order_code);
        $actionResponse->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('Permintaan', $order->session_status, 'Sesi tidak boleh berubah jadi Dikonfirmasi jika belum bayar');
    }

    public function test_order_can_confirm_session_after_payment_is_marked_paid(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-UNLOCK-02',
            'invoice_number' => 'INV-UNLOCK-02',
            'package_name' => 'Birthday Session',
            'customer_name' => 'Rina Salsabila',
            'customer_email' => 'rina@test.com',
            'customer_whatsapp' => '081277778888',
            'total_price' => 1250000,
            'session_date' => '2026-11-29',
            'session_time_slot' => '13.00–14.30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        // 1. Tandai lunas pembayaran secara manual
        $this->post('/admin/pesanan/'.$order->order_code.'/verifikasi-pembayaran');

        $order->refresh();
        $this->assertEquals('Terverifikasi', $order->payment_status);

        // 2. Sekarang tombol konfirmasi sesi aktif dan berhasil disetujui
        $confirmResponse = $this->post('/admin/pesanan/'.$order->order_code.'/konfirmasi-sesi');
        $confirmResponse->assertRedirect('/admin/pesanan/'.$order->order_code);
        $confirmResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Dikonfirmasi', $order->session_status);
    }

    public function test_admin_orders_index_tabs_do_not_contain_counter_brackets(): void
    {
        $this->authenticateAdmin();

        $response = $this->get('/admin/pesanan');

        $response->assertStatus(200);
        $response->assertSee('Semua pesanan');
        $response->assertSee('Permintaan');
        $response->assertSee('Dikonfirmasi');
        $response->assertSee('Selesai');
        $response->assertSee('Arsip');
        $response->assertDontSee('Semua pesanan (');
        $response->assertDontSee('Permintaan (');
        $response->assertDontSee('Dikonfirmasi (');
    }

    public function test_timeline_reflects_real_payment_verification_and_session_confirmation(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-TIMELINE',
            'invoice_number' => 'INV-TEST-TIMELINE',
            'package_name' => 'Family Session',
            'customer_name' => 'Dzaky Naufal',
            'customer_email' => 'niwwjea@gmail.com',
            'customer_whatsapp' => '081211112222',
            'total_price' => 2450000,
            'session_date' => '2026-11-20',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        // Verifikasi & Konfirmasi Sesi sekaligus dalam 1 klik
        $this->post('/admin/pesanan/'.$order->order_code.'/verifikasi-dan-konfirmasi');

        $order->refresh();
        $this->assertEquals('Terverifikasi', $order->payment_status);
        $this->assertEquals('Dikonfirmasi', $order->session_status);

        // Cek halaman detail untuk melihat Linimasa telah bergerak maju secara real-time
        $response = $this->get('/admin/pesanan/'.$order->order_code);
        $response->assertStatus(200);
        $response->assertSee('Pesanan dibuat');
        $response->assertSee('Bukti diterima');
        $response->assertSee('Pembayaran terverifikasi');
        $response->assertSee('Jadwal sesi dikonfirmasi');
    }

    public function test_admin_can_destroy_duplicate_order(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-DUPLICATE',
            'invoice_number' => 'INV-TEST-DUP',
            'package_name' => 'Family Session',
            'customer_name' => 'Test Duplicate',
            'customer_email' => 'dup@test.com',
            'customer_whatsapp' => '08123456789',
            'session_date' => '2026-11-20',
            'session_time_slot' => '10:00–11:00 WIB',
            'location' => 'Studio Utama',
            'total_price' => 2450000,
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->delete('/admin/pesanan/'.$order->order_code);

        $response->assertRedirect('/admin/pesanan');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
            'order_code' => 'CH-TEST-DUPLICATE',
        ]);
    }

    public function test_admin_can_mark_session_as_completed(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-SELESAI',
            'invoice_number' => 'INV-TEST-SELESAI',
            'package_name' => 'Family Session',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@test.com',
            'customer_whatsapp' => '08123456789',
            'session_date' => '2026-11-20',
            'session_time_slot' => '10:00–11:00 WIB',
            'location' => 'Studio Utama',
            'total_price' => 2450000,
            'payment_status' => 'Terverifikasi',
            'session_status' => 'Dikonfirmasi',
        ]);

        $response = $this->post('/admin/pesanan/'.$order->order_code.'/selesai-sesi');

        $response->assertRedirect('/admin/pesanan/'.$order->order_code);
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Selesai', $order->session_status);

        $detailResponse = $this->get('/admin/pesanan/'.$order->order_code);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Sesi pemotretan telah selesai dilaksanakan.');
    }

    public function test_order_detail_displays_linked_client_gallery(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-TEST-GALLERY',
            'invoice_number' => 'INV-TEST-GALLERY',
            'package_name' => 'Prewedding Session',
            'customer_name' => 'Citra & Rama',
            'customer_email' => 'citra@test.com',
            'customer_whatsapp' => '08123456789',
            'session_date' => '2026-11-22',
            'session_time_slot' => '13:00–15:00 WIB',
            'location' => 'Studio Utama',
            'total_price' => 4800000,
            'payment_status' => 'Terverifikasi',
            'session_status' => 'Selesai',
        ]);

        $gallery = Gallery::create([
            'title' => 'Galeri Kenangan Citra & Rama',
            'slug' => 'galeri-kenangan-citra-rama',
            'client_name' => 'Citra & Rama',
            'order_id' => $order->id,
            'status' => 'Draf',
            'target_photos' => 40,
            'access_type' => 'Privat',
            'pin_code' => '5678',
        ]);

        $response = $this->get('/admin/pesanan/'.$order->order_code);

        $response->assertStatus(200);
        $response->assertSee('Galeri hasil foto klien');
        $response->assertSee('Galeri Kenangan Citra & Rama');
        $response->assertSee('5678');
        $response->assertSee('Terbitkan Galeri Sekarang');
    }
}
