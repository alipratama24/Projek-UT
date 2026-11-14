<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_gallery_page_loads_with_fallback_photos(): void
    {
        $response = $this->get('/galeri-klien');

        $response->assertStatus(200);
        $response->assertSee('Galeri foto Anda');
        $response->assertSee('Lihat Tayangan Foto');
        $response->assertSee('Unduh Semua Foto');
        $response->assertSee('galleryLightboxModal');
        $response->assertSee('btnOpenSlideshow');
        $response->assertSee('btnDownloadAll');
        $response->assertSee('galleryToast');
    }

    public function test_download_single_photo_returns_file_download(): void
    {
        $response = $this->get('/galeri-klien/unduh-foto?file=work-1.jpg&name=Keluarga%2001');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=keluarga-01.jpg');
    }

    public function test_download_single_photo_with_invalid_file_aborts_404(): void
    {
        $response = $this->get('/galeri-klien/unduh-foto?file=non-existent-photo-xyz.jpg');

        $response->assertStatus(404);
    }

    public function test_download_all_photos_returns_zip_archive(): void
    {
        $response = $this->get('/galeri-klien/unduh-semua');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
        $this->assertStringContainsString('naro-capture-', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.zip', $response->headers->get('content-disposition'));
    }

    public function test_client_gallery_with_database_records(): void
    {
        $order = Order::create([
            'order_code' => 'CH-2026-999',
            'invoice_number' => 'INV-CH-2026-999',
            'customer_name' => 'Budi Pratama',
            'customer_email' => 'budi@example.com',
            'customer_whatsapp' => '081299998888',
            'package_name' => 'Graduation Session',
            'total_price' => 1500000,
            'location' => 'Studio Utama naro capture',
            'session_status' => 'Selesai',
            'payment_status' => 'Lunas',
            'session_date' => '2026-11-25',
            'session_time_slot' => '10.00–11.30 WIB',
        ]);

        $gallery = Gallery::create([
            'slug' => 'galeri-budi-pratama',
            'title' => 'Wisuda Budi Pratama',
            'order_id' => $order->id,
            'client_name' => 'Budi Pratama',
            'target_photos' => 15,
            'status' => 'Diterbitkan',
            'access_type' => 'Terbuka',
            'cover_image' => 'work-1.jpg',
        ]);

        GalleryPhoto::create([
            'gallery_id' => $gallery->id,
            'photo_path' => 'work-1.jpg',
            'caption' => 'Momen Wisuda 01',
            'sort_order' => 1,
        ]);

        $response = $this->get('/galeri-klien?slug=galeri-budi-pratama');

        $response->assertStatus(200);
        $response->assertSee('Wisuda Budi Pratama');
        $response->assertSee('Budi Pratama');
        $response->assertSee('Momen Wisuda 01');
    }
}
