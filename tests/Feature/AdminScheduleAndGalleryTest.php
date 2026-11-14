<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminScheduleAndGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): User
    {
        $token = 'test-token-sched-gal';
        $user = User::factory()->create([
            'remember_token' => $token,
        ]);

        $this->actingAs($user)->withCookie('admin_auth_token', $token);

        return $user;
    }

    public function test_payment_detail_displays_customer_document(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-PAY-DOC',
            'invoice_number' => 'INV-PAY-DOC',
            'customer_name' => 'Dzaky Naufal',
            'customer_email' => 'niwwjea@gmail.com',
            'customer_whatsapp' => '081233334444',
            'package_name' => 'Prewedding',
            'total_price' => 4800000,
            'session_date' => '2026-11-20',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Bukti terkirim',
            'session_status' => 'Permintaan',
        ]);

        $payment = Payment::create([
            'payment_code' => 'BYR-DOC-001',
            'order_id' => $order->id,
            'sender_name' => 'Dzaky Naufal',
            'transfer_date' => now()->toDateString(),
            'bank_source' => 'BCA',
            'nominal' => 4800000,
            'proof_file' => 'work-1.jpg',
            'file_original_name' => 'struk-dzaky.jpg',
            'file_extension' => 'JPG',
            'status' => 'Bukti terkirim',
        ]);

        $response = $this->get('/admin/pembayaran/'.$payment->payment_code);
        $response->assertStatus(200);
        $response->assertSee('Dzaky Naufal');
        $response->assertSee('struk-dzaky.jpg');
        $response->assertSee('Bukti Unggahan Pelanggan');
        $response->assertDontSee('Tidak ada berkas bukti fisik terlampir');
    }

    public function test_customer_checkout_displays_real_time_status_for_order(): void
    {
        $order = Order::create([
            'order_code' => 'CH-CHECKOUT-REAL',
            'invoice_number' => 'INV-CHECKOUT-REAL',
            'customer_name' => 'Dzaky Naufal',
            'customer_email' => 'niwwjea@gmail.com',
            'customer_whatsapp' => '081233334444',
            'package_name' => 'Prewedding',
            'total_price' => 4800000,
            'session_date' => '2026-11-20',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Terverifikasi',
            'session_status' => 'Dikonfirmasi',
        ]);

        $response = $this->get('/checkout?order='.$order->order_code);
        $response->assertStatus(200);
        $response->assertSee('Dzaky Naufal');
        $response->assertSee('Terverifikasi · Lunas');
        $response->assertSee('Sesi dikonfirmasi');
        $response->assertSee('Pembayaran Anda telah diverifikasi oleh studio.');
    }

    public function test_admin_can_create_gallery_as_draft_and_it_appears_in_table(): void
    {
        $this->authenticateAdmin();

        $order = Order::create([
            'order_code' => 'CH-GAL-ORDER',
            'invoice_number' => 'INV-GAL-ORDER',
            'customer_name' => 'Citra Kirana',
            'customer_email' => 'citra@example.com',
            'customer_whatsapp' => '081255556666',
            'package_name' => 'Family Session',
            'total_price' => 2450000,
            'session_date' => '2026-11-20',
            'session_time_slot' => '15.30–17.00 WIB',
            'location' => 'Studio Utama naro capture',
            'payment_status' => 'Terverifikasi',
            'session_status' => 'Selesai',
        ]);

        // Verifikasi dropdown hanya memuat pesanan berstatus 'Selesai'
        $indexResponse = $this->get('/admin/galeri');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($order->order_code);

        // Buat galeri draf dengan PIN custom
        $postResponse = $this->post('/admin/galeri', [
            'title' => 'Galeri Keluarga Citra',
            'order_id' => $order->id,
            'target_photos' => 25,
            'access_type' => 'Privat',
            'pin_code' => '8899',
        ]);

        $postResponse->assertRedirect('/admin/galeri');
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('galleries', [
            'title' => 'Galeri Keluarga Citra',
            'client_name' => 'Citra Kirana',
            'status' => 'Draf',
            'pin_code' => '8899',
        ]);

        $gallery = Gallery::where('title', 'Galeri Keluarga Citra')->first();

        // Update data galeri, ubah status ke Diterbitkan dan ganti PIN
        $updateResponse = $this->put('/admin/galeri/'.$gallery->id, [
            'title' => 'Galeri Keluarga Citra Update',
            'order_id' => $order->id,
            'status' => 'Diterbitkan',
            'target_photos' => 30,
            'access_type' => 'Privat',
            'pin_code' => '7766',
        ]);

        $updateResponse->assertRedirect('/admin/galeri');
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'title' => 'Galeri Keluarga Citra Update',
            'status' => 'Diterbitkan',
            'pin_code' => '7766',
        ]);

        // Pastikan muncul di tabel dengan tombol Kelola & Foto serta PIN custom baru
        $indexCheck = $this->get('/admin/galeri');
        $indexCheck->assertStatus(200);
        $indexCheck->assertSee('Galeri Keluarga Citra Update');
        $indexCheck->assertSee('PIN: 7766');
        $indexCheck->assertSee('Kelola &amp; Foto ✎', false);
    }

    public function test_admin_schedule_renders_real_calendar_date_and_supports_navigation(): void
    {
        $this->authenticateAdmin();

        $now = Carbon::now();
        $currentMonthFormatted = $now->translatedFormat('F Y');

        StudioSlot::create([
            'date' => $now->copy()->startOfMonth()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'type' => 'available',
            'note' => 'Slot Uji Coba Real-Time',
        ]);

        // 1. Kalender default memuat bulan saat ini secara real-time
        $response = $this->get('/admin/jadwal');
        $response->assertStatus(200);
        $response->assertSee($currentMonthFormatted);
        $response->assertSee('Slot Uji Coba Real-Time');

        // 2. Navigasi ke bulan depan
        $nextMonth = $now->copy()->addMonth();
        $nextMonthResponse = $this->get('/admin/jadwal?bulan='.$nextMonth->format('Y-m'));
        $nextMonthResponse->assertStatus(200);
        $nextMonthResponse->assertSee($nextMonth->translatedFormat('F Y'));

        // 3. Navigasi ke bulan lalu
        $prevMonth = $now->copy()->subMonth();
        $prevMonthResponse = $this->get('/admin/jadwal?bulan='.$prevMonth->format('Y-m'));
        $prevMonthResponse->assertStatus(200);
        $prevMonthResponse->assertSee($prevMonth->translatedFormat('F Y'));
    }

    public function test_admin_can_stage_delete_photos_and_upload_new_photos_in_gallery_modal(): void
    {
        Storage::fake('public');
        $this->authenticateAdmin();

        $gallery = Gallery::create([
            'slug' => 'test-gallery-photos',
            'title' => 'Galeri Foto Test',
            'client_name' => 'Klien Test',
            'target_photos' => 10,
            'status' => 'Draf',
            'access_type' => 'Terbuka',
        ]);

        $photo1 = GalleryPhoto::create([
            'gallery_id' => $gallery->id,
            'photo_path' => 'galleries/photo1.jpg',
            'caption' => 'Photo 1',
        ]);
        Storage::disk('public')->put('galleries/photo1.jpg', 'fake-image-1');

        $photo2 = GalleryPhoto::create([
            'gallery_id' => $gallery->id,
            'photo_path' => 'galleries/photo2.jpg',
            'caption' => 'Photo 2',
        ]);
        Storage::disk('public')->put('galleries/photo2.jpg', 'fake-image-2');

        $newFile = UploadedFile::fake()->create('photo_new.jpg', 100, 'image/jpeg');

        $response = $this->put('/admin/galeri/'.$gallery->id, [
            'title' => 'Galeri Foto Test Updated',
            'target_photos' => 12,
            'access_type' => 'Terbuka',
            'status' => 'Penyuntingan',
            'deleted_photo_ids' => [$photo1->id],
            'photos' => [$newFile],
        ]);

        $response->assertRedirect('/admin/galeri');
        $response->assertSessionHas('success');

        // Foto 1 harus terhapus dari database dan storage
        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo1->id]);
        Storage::disk('public')->assertMissing('galleries/photo1.jpg');

        // Foto 2 harus tetap ada
        $this->assertDatabaseHas('gallery_photos', ['id' => $photo2->id]);
        Storage::disk('public')->assertExists('galleries/photo2.jpg');

        // Foto baru harus tersimpan
        $this->assertEquals(2, $gallery->photos()->count());
    }
}
