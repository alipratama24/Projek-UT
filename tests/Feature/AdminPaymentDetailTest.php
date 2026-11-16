<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentDetailTest extends TestCase
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

    public function test_payment_detail_displays_no_data_state_when_no_file_uploaded(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'ORD-TEST-001',
            'invoice_number' => 'INV-TEST-001',
            'package_name' => 'Family Session',
            'customer_name' => 'Budi Client',
            'customer_email' => 'budi@test.com',
            'customer_whatsapp' => '081234567890',
            'total_price' => 1500000,
            'session_date' => '2026-10-20',
            'session_time_slot' => '10:00 - 11:30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Menunggu pembayaran',
        ]);

        $payment = Payment::create([
            'payment_code' => 'BYR-TEST-001',
            'order_id' => $order->id,
            'sender_name' => 'Budi Client',
            'transfer_date' => now()->toDateString(),
            'bank_source' => 'BCA',
            'nominal' => 1500000,
            'proof_file' => null,
            'file_original_name' => null,
            'file_extension' => 'NONE',
            'status' => 'Bukti terkirim',
        ]);

        $response = $this->get('/admin/pembayaran/'.$payment->payment_code);

        $response->assertStatus(200);
        $response->assertSee('No Data / Tidak Ada Berkas');
        $response->assertSee('TIDAK ADA BERKAS');
        $response->assertDontSee('Buka berkas ilustrasi');
        $response->assertDontSee('Dokumen ilustrasi');
        $response->assertDontSee('bukti-transfer-contoh.pdf');
        $response->assertDontSee('Buka berkas di tab baru');
    }

    public function test_payment_detail_displays_file_and_open_button_when_file_exists(): void
    {
        $this->authenticateAdmin();
        Storage::fake('public');

        $fakeFile = UploadedFile::fake()->create('struk_pembayaran_asli.pdf', 100, 'application/pdf');
        $storedPath = $fakeFile->store('payments', 'public');

        $order = Order::create([
            'order_code' => 'ORD-TEST-002',
            'invoice_number' => 'INV-TEST-002',
            'package_name' => 'Birthday Session',
            'customer_name' => 'Citra Maharani',
            'customer_email' => 'citra@test.com',
            'customer_whatsapp' => '081298765432',
            'total_price' => 2000000,
            'session_date' => '2026-10-22',
            'session_time_slot' => '13:00 - 14:30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Menunggu pembayaran',
        ]);

        $payment = Payment::create([
            'payment_code' => 'BYR-TEST-002',
            'order_id' => $order->id,
            'sender_name' => 'Citra Maharani',
            'transfer_date' => now()->toDateString(),
            'bank_source' => 'Mandiri',
            'nominal' => 2000000,
            'proof_file' => $storedPath,
            'file_original_name' => 'struk_pembayaran_asli.pdf',
            'file_extension' => 'PDF',
            'status' => 'Bukti terkirim',
        ]);

        $response = $this->get('/admin/pembayaran/'.$payment->payment_code);

        $response->assertStatus(200);
        $response->assertSee('struk_pembayaran_asli.pdf');
        $response->assertSee('Buka berkas di tab baru ↗');
        $response->assertSee('<iframe', false);
        $response->assertSee('Unduh dokumen ↓');
        $response->assertDontSee('No Data / Tidak Ada Berkas');
    }

    public function test_payment_detail_displays_image_preview_when_image_file_exists(): void
    {
        $this->authenticateAdmin();
        Storage::fake('public');

        $fakeFile = UploadedFile::fake()->image('struk_bca.jpg', 600, 800);
        $storedPath = $fakeFile->store('payments', 'public');

        $order = Order::create([
            'order_code' => 'ORD-TEST-003',
            'invoice_number' => 'INV-TEST-003',
            'package_name' => 'Graduation Session',
            'customer_name' => 'Dimas Wicaksono',
            'customer_email' => 'dimas@test.com',
            'customer_whatsapp' => '081211112222',
            'total_price' => 1500000,
            'session_date' => '2026-10-23',
            'session_time_slot' => '10:00 - 11:30 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Menunggu pembayaran',
        ]);

        $payment = Payment::create([
            'payment_code' => 'BYR-TEST-003',
            'order_id' => $order->id,
            'sender_name' => 'Dimas Wicaksono',
            'transfer_date' => now()->toDateString(),
            'bank_source' => 'BCA',
            'nominal' => 1500000,
            'proof_file' => $storedPath,
            'file_original_name' => 'struk_bca.jpg',
            'file_extension' => 'JPG',
            'status' => 'Bukti terkirim',
        ]);

        $response = $this->get('/admin/pembayaran/'.$payment->payment_code);

        $response->assertStatus(200);
        $response->assertSee('struk_bca.jpg');
        $response->assertSee('Buka berkas di tab baru ↗');
        $response->assertSee('<img src="', false);
        $response->assertDontSee('No Data / Tidak Ada Berkas');
    }

    public function test_payment_detail_can_be_accessed_via_order_code_without_prior_payment(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-CONTOH-004',
            'invoice_number' => 'INV-CH-2026-004',
            'package_name' => 'Prewedding',
            'customer_name' => 'al budi',
            'customer_email' => 'al@mail.com',
            'customer_whatsapp' => '081299998888',
            'total_price' => 4800000,
            'session_date' => '2026-10-17',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Cafe niux jaksel',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->get('/admin/pembayaran/'.$order->order_code);

        $response->assertStatus(200);
        $response->assertSee('al budi');
        $response->assertSee('CH-CONTOH-004');
        $response->assertSee('INV-CH-2026-004');
        $response->assertSee('Rp4.800.000');
        $response->assertSee('No Data / Tidak Ada Berkas');
    }

    public function test_update_internal_notes_via_order_code_saves_note_successfully(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-CONTOH-005',
            'invoice_number' => 'INV-CH-2026-005',
            'package_name' => 'Prewedding',
            'customer_name' => 'Klien Contoh',
            'customer_email' => 'klien@test.com',
            'customer_whatsapp' => '081233334444',
            'total_price' => 4800000,
            'session_date' => '2026-10-17',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Studio',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->post('/admin/pembayaran/'.$order->order_code.'/catatan', [
            'catatan' => '-minus 100k',
        ]);

        $response->assertRedirect('/admin/pembayaran/'.$order->order_code);
        $response->assertSessionHas('success');

        $detailResponse = $this->get('/admin/pembayaran/'.$order->order_code);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('-minus 100k');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'internal_review_notes' => '-minus 100k',
        ]);
    }

    public function test_request_fix_updates_status_and_note_successfully(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-CONTOH-006',
            'invoice_number' => 'INV-CH-2026-006',
            'package_name' => 'Graduation',
            'customer_name' => 'Dina Amalia',
            'customer_email' => 'dina@test.com',
            'customer_whatsapp' => '081255556666',
            'total_price' => 1500000,
            'session_date' => '2026-10-25',
            'session_time_slot' => '10.00–11.30 WIB',
            'location' => 'Studio Utama',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Permintaan',
        ]);

        $response = $this->post('/admin/pembayaran/'.$order->order_code.'/minta-perbaikan', [
            'alasan' => 'Nominal atau nomor rekening tujuan tidak terbaca jelas',
            'pesan_instruksi' => 'Mohon unggah struk asli yang lebih terang.',
        ]);

        $response->assertRedirect('/admin/pembayaran/'.$order->order_code);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'Perlu perbaikan',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'Perlu perbaikan',
        ]);
    }
}
