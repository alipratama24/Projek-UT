<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPaymentRevisionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): User
    {
        $token = 'test-admin-token-rev';
        $user = User::factory()->create([
            'remember_token' => $token,
        ]);

        $this->actingAs($user)->withCookie('admin_auth_token', $token);

        return $user;
    }

    public function test_customer_payment_revision_flow_and_admin_image_display(): void
    {
        Storage::fake('public');

        // 1. Pelanggan Citra Ameena membuat pesanan CH-2026-005
        $customer = Customer::create([
            'name' => 'Citra Ameena',
            'email' => 'citra@example.com',
            'whatsapp' => '081234567890',
            'customer_code' => 'CUST-2026-005',
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'order_code' => 'CH-2026-005',
            'invoice_number' => 'INV-CH-2026-005',
            'customer_id' => $customer->id,
            'package_name' => 'Family Session',
            'customer_name' => 'Citra Ameena',
            'customer_email' => 'citra@example.com',
            'customer_whatsapp' => '081234567890',
            'total_price' => 2450000,
            'session_date' => '2026-11-25',
            'session_time_slot' => '15.30–17.00 WIB · 90 menit',
            'location' => 'Studio naro capture',
            'session_status' => 'Permintaan',
            'payment_status' => 'Bukti terkirim',
        ]);

        $initialFile = UploadedFile::fake()->image('struk_buram.jpg', 400, 400);
        $initialPath = $initialFile->store('payments', 'public');

        $payment = Payment::create([
            'payment_code' => 'BYR-2026-005',
            'order_id' => $order->id,
            'sender_name' => 'Citra Ameena',
            'transfer_date' => now()->toDateString(),
            'bank_source' => 'BCA',
            'nominal' => 2450000,
            'proof_file' => $initialPath,
            'file_original_name' => 'struk_buram.jpg',
            'file_extension' => 'JPG',
            'status' => 'Bukti terkirim',
        ]);

        // 2. Admin memberikan aksi "Minta perbaikan bukti"
        $admin = $this->authenticateAdmin();
        $revisionReason = 'Nominal atau nomor rekening tujuan tidak terbaca jelas. Mohon foto ulang dengan jelas.';
        $actionResponse = $this->post('/admin/pembayaran/'.$payment->payment_code.'/minta-perbaikan', [
            'alasan' => 'Nominal atau nomor rekening tujuan tidak terbaca jelas',
            'pesan_instruksi' => 'Mohon foto ulang dengan jelas tanpa terpotong.',
        ]);

        $actionResponse->assertRedirect(route('admin.pembayaran.detail', ['id' => $payment->payment_code]));
        $order->refresh();
        $this->assertEquals('Perlu perbaikan', $order->payment_status);

        // 3. Pelanggan mengecek status di /cek-pesanan?kode=CH-2026-005
        // Memastikan status TIDAK stuck di "menunggu pembayaran"
        $trackingResponse = $this->get('/cek-pesanan?kode=CH-2026-005');
        $trackingResponse->assertStatus(200);
        $trackingResponse->assertSee('PERLU PERBAIKAN BUKTI');
        $trackingResponse->assertSee('Perlu perbaikan bukti pembayaran');
        $trackingResponse->assertSee('Unggah Ulang Bukti Pembayaran');

        // 4. Pelanggan membuka halaman konfirmasi pembayaran
        $confirmPageResponse = $this->get('/konfirmasi-pembayaran?order=CH-2026-005');
        $confirmPageResponse->assertStatus(200);
        // Halaman HARUS menampilkan halaman perbaikan bukti, BUKAN stuck di "menunggu pembayaran" atau read-only
        $confirmPageResponse->assertSee('PERLU PERBAIKAN BUKTI PEMBAYARAN');
        $confirmPageResponse->assertSee('Unggah perbaikan bukti pembayaran');
        $confirmPageResponse->assertSee('Catatan Instruksi Perbaikan Bukti dari Studio');
        $confirmPageResponse->assertSee('Kirim Bukti Perbaikan');

        // 5. Pelanggan mengunggah bukti perbaikan yang baru
        $revisedFile = UploadedFile::fake()->image('struk_citra_jelas.jpg', 800, 1200);
        $submitRevisionResponse = $this->post('/konfirmasi-pembayaran', [
            'order_code' => 'CH-2026-005',
            'nama_pengirim' => 'Citra Ameena',
            'tanggal_transfer' => now()->toDateString(),
            'bank_sumber' => 'BCA',
            'nominal' => '2450000',
            'catatan_pembayaran' => 'Ini struk yang sudah difoto ulang lebih jelas.',
            'struk_pembayaran' => $revisedFile,
        ]);

        $submitRevisionResponse->assertRedirect(route('konfirmasi', ['order' => 'CH-2026-005', 'status' => 'success']));
        $submitRevisionResponse->assertSessionHas('success');

        // 6. Status pesanan diperbarui menjadi "Bukti terkirim"
        $order->refresh();
        $this->assertEquals('Bukti terkirim', $order->payment_status);

        // 7. Admin memeriksa Detail Pembayaran -> gambar muncul dengan benar
        $latestPayment = $order->payments()->latest('id')->first();
        $this->assertNotNull($latestPayment);
        $this->assertEquals('struk_citra_jelas.jpg', $latestPayment->file_original_name);

        $adminViewResponse = $this->get('/admin/pembayaran/'.$latestPayment->payment_code);
        $adminViewResponse->assertStatus(200);
        $adminViewResponse->assertSee('struk_citra_jelas.jpg');
        $adminViewResponse->assertSee('Buka berkas di tab baru ↗');

        // Berkas dapat diakses/di-stream langsung via route viewFile
        $fileStreamResponse = $this->get(route('admin.pembayaran.view-file', ['id' => $latestPayment->payment_code]));
        $fileStreamResponse->assertStatus(200);
    }
}
