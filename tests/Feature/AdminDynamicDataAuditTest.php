<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDynamicDataAuditTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): User
    {
        $token = 'test-audit-token-123';
        $user = User::factory()->create([
            'remember_token' => $token,
        ]);

        $this->actingAs($user)->withCookie('admin_auth_token', $token);

        return $user;
    }

    public function test_admin_orders_index_is_dynamic(): void
    {
        $this->authenticateAdmin();

        $order1 = Order::create([
            'order_code' => 'CH-2026-001',
            'invoice_number' => 'INV-CH-2026-001',
            'customer_name' => 'Alisa Wardani',
            'customer_email' => 'alisa@example.com',
            'customer_whatsapp' => '081211112222',
            'package_name' => 'Graduation Session',
            'total_price' => 1500000,
            'location' => 'Studio Utama naro capture',
            'session_status' => 'Permintaan',
            'payment_status' => 'Belum dibayar',
            'session_date' => '2026-11-20',
            'session_time_slot' => '10.00–11.30 WIB',
        ]);

        $response = $this->get('/admin/pesanan');

        $response->assertStatus(200);
        $response->assertSee('Semua pesanan');
        $response->assertSee('Permintaan');
        $response->assertSee('Dikonfirmasi');
        $response->assertSee('Alisa Wardani');
        $response->assertDontSee('Semua pesanan (24)');
        $response->assertDontSee('Data ilustrasi');

        // Test search
        $searchResponse = $this->get('/admin/pesanan?search=NonExistentPerson');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Tidak ada data pesanan yang sesuai dengan filter ini.');
    }

    public function test_admin_payments_index_is_dynamic(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-2026-002',
            'invoice_number' => 'INV-CH-2026-002',
            'customer_name' => 'Rian Hidayat',
            'customer_email' => 'rian@example.com',
            'customer_whatsapp' => '081299998888',
            'package_name' => 'Family Session',
            'total_price' => 2000000,
            'location' => 'Studio Utama naro capture',
            'session_date' => '2026-11-14',
            'session_time_slot' => '14.00–15.30 WIB',
            'session_status' => 'Permintaan',
            'payment_status' => 'Bukti terkirim',
        ]);

        $payment = Payment::create([
            'payment_code' => 'BYR-2026-001',
            'order_id' => $order->id,
            'sender_name' => 'Rian Hidayat',
            'transfer_date' => '2026-11-14',
            'bank_source' => 'BCA',
            'nominal' => 2000000,
            'status' => 'Bukti terkirim',
        ]);

        $response = $this->get('/admin/pembayaran');

        $response->assertStatus(200);
        $response->assertSee('Rian Hidayat');
        $response->assertSee('BYR-2026-001');
        $response->assertSee('Bukti tertua diterima 14 November');
        $response->assertDontSee('Bukti tertua diterima 5 Oktober');
        $response->assertDontSee('dari 3 bukti terkirim');
        $response->assertDontSee('Data ilustrasi');
    }

    public function test_admin_customers_index_is_dynamic(): void
    {
        $this->authenticateAdmin();

        $customer = Customer::create([
            'customer_code' => 'PLG-2026-001',
            'name' => 'Dewi Anggraini',
            'email' => 'dewi@example.com',
            'whatsapp' => '081277776666',
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'order_code' => 'CH-2026-DEWI',
            'invoice_number' => 'INV-CH-2026-DEWI',
            'customer_id' => $customer->id,
            'customer_name' => 'Dewi Anggraini',
            'customer_email' => 'dewi@example.com',
            'customer_whatsapp' => '081277776666',
            'package_name' => 'Birthday Session',
            'total_price' => 1250000,
            'location' => 'Studio Utama naro capture',
            'session_date' => '2026-11-18',
            'session_time_slot' => '09.00–10.00 WIB',
            'session_status' => 'Permintaan',
            'payment_status' => 'Belum dibayar',
        ]);

        $response = $this->get('/admin/pelanggan');

        $response->assertStatus(200);
        $response->assertSee('Dewi Anggraini');
        $response->assertSee('Aktif (1)');
        $response->assertDontSee('Aktif (18)');
        $response->assertDontSee('Email ilustrasi');
        $response->assertSee(route('admin.pelanggan.detail', ['id' => $customer->customer_code]));
        $response->assertDontSee('CH-CONTOH-001');
    }

    public function test_public_booking_page_renders_current_month(): void
    {
        Package::create([
            'code' => 'PKG-FAMILY',
            'slug' => 'family-session',
            'name' => 'Family Session',
            'price' => 2450000,
            'duration' => '90 menit',
            'duration_minutes' => 90,
            'photo_count' => 25,
            'photo_count_label' => '25 foto',
            'is_active' => true,
        ]);

        $response = $this->get('/pemesanan');

        $response->assertStatus(200);
        $currentMonthYear = Carbon::now()->translatedFormat('F Y');
        $response->assertSee($currentMonthYear);
        $response->assertSee('id="btnNextMonth"', false);
        $response->assertSee('id="btnPrevMonth"', false);
        $response->assertSee('renderCalendar(viewYear, viewMonth);', false);
    }

    public function test_new_booking_generates_standard_order_code(): void
    {
        Package::create([
            'code' => 'PKG-FAMILY',
            'slug' => 'family-session',
            'name' => 'Family Session',
            'price' => 2450000,
            'duration' => '90 menit',
            'duration_minutes' => 90,
            'photo_count' => 25,
            'photo_count_label' => '25 foto',
            'is_active' => true,
        ]);

        $response = $this->post('/pemesanan', [
            'package_id' => 'family-session',
            'session_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'session_time' => '10.00',
            'nama_lengkap' => 'Bintang Pratama',
            'email' => 'bintang@example.com',
            'whatsapp' => '081234567890',
            'lokasi' => 'Studio Utama naro capture',
            'catatan' => 'Sesi foto keluarga',
            'persetujuan' => '1',
        ]);

        $response->assertRedirect('/checkout');

        $order = Order::where('customer_email', 'bintang@example.com')->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('CH-'.date('Y').'-', $order->order_code);
        $this->assertStringNotContainsString('CONTOH', $order->order_code);
    }

    public function test_package_order_has_graduation_before_family_session(): void
    {
        Package::create([
            'code' => 'PKG-BIRTHDAY',
            'slug' => 'birthday',
            'name' => 'Birthday',
            'price' => 1250000,
            'duration' => '60 menit',
            'duration_minutes' => 60,
            'photo_count' => 15,
            'photo_count_label' => '15 foto',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Package::create([
            'code' => 'PKG-FAMILY',
            'slug' => 'family-session',
            'name' => 'Family Session',
            'price' => 2450000,
            'duration' => '90 menit',
            'duration_minutes' => 90,
            'photo_count' => 25,
            'photo_count_label' => '25 foto',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Package::create([
            'code' => 'PKG-GRADUATION',
            'slug' => 'graduation',
            'name' => 'Graduation',
            'price' => 1500000,
            'duration' => '60 menit',
            'duration_minutes' => 60,
            'photo_count' => 15,
            'photo_count_label' => '15 foto',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Verifikasi pada halaman /pemesanan
        $bookingResponse = $this->get('/pemesanan');
        $bookingResponse->assertStatus(200);
        $bookingContent = $bookingResponse->getContent();

        $posBirthday = strpos($bookingContent, 'Birthday');
        $posGraduation = strpos($bookingContent, 'Graduation');
        $posFamily = strpos($bookingContent, 'Family Session');

        $this->assertNotFalse($posBirthday);
        $this->assertNotFalse($posGraduation);
        $this->assertNotFalse($posFamily);
        $this->assertTrue($posBirthday < $posGraduation, 'Birthday harus muncul sebelum Graduation');
        $this->assertTrue($posGraduation < $posFamily, 'Graduation harus muncul sebelum Family Session');

        // Verifikasi pada halaman Beranda (/)
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeContent = $homeResponse->getContent();

        $homePosBirthday = strpos($homeContent, 'Birthday');
        $homePosGraduation = strpos($homeContent, 'Graduation');
        $homePosFamily = strpos($homeContent, 'Family Session');

        $this->assertNotFalse($homePosBirthday);
        $this->assertNotFalse($homePosGraduation);
        $this->assertNotFalse($homePosFamily);
        $this->assertTrue($homePosBirthday < $homePosGraduation, 'Birthday harus muncul sebelum Graduation di Beranda');
        $this->assertTrue($homePosGraduation < $homePosFamily, 'Graduation harus muncul sebelum Family Session di Beranda');
    }

    public function test_order_detail_shows_payment_proof_and_allows_direct_verification(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-ORDER-VERIFY',
            'invoice_number' => 'INV-ORDER-VERIFY',
            'customer_name' => 'Citra Kirana',
            'customer_email' => 'citra@example.com',
            'customer_whatsapp' => '081234567890',
            'package_name' => 'Graduation',
            'total_price' => 1500000,
            'location' => 'Studio Utama naro capture',
            'session_status' => 'Permintaan',
            'payment_status' => 'Bukti terkirim',
            'session_date' => '2026-12-01',
            'session_time_slot' => '10.00–11.30 WIB',
        ]);

        Payment::create([
            'payment_code' => 'BYR-CH-ORDER-VERIFY',
            'order_id' => $order->id,
            'sender_name' => 'Citra Kirana',
            'transfer_date' => now()->toDateString(),
            'bank_source' => 'BCA',
            'nominal' => 1500000,
            'proof_file' => 'work-1.jpg',
            'file_original_name' => 'bukti-transfer-citra.jpg',
            'file_extension' => 'JPG',
            'status' => 'Menunggu Verifikasi',
        ]);

        // 1. Cek detail pesanan memuat bukti transfer dan tombol verifikasi langsung
        $response = $this->get('/admin/pesanan/CH-ORDER-VERIFY');
        $response->assertStatus(200);
        $response->assertSee('Bukti Transfer (BCA)');
        $response->assertSee('Verifikasi &amp; Konfirmasi Sesi', false);
        $response->assertSee('Verifikasi Saja');
        $response->assertSee('Minta Perbaikan');

        // 2. Lakukan verifikasi dan konfirmasi sesi langsung dalam 1 klik
        $actionResponse = $this->post('/admin/pesanan/CH-ORDER-VERIFY/verifikasi-dan-konfirmasi');
        $actionResponse->assertRedirect('/admin/pesanan/CH-ORDER-VERIFY');

        $order->refresh();
        $this->assertEquals('Terverifikasi', $order->payment_status);
        $this->assertEquals('Dikonfirmasi', $order->session_status);
    }
}
