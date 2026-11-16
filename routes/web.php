<?php

use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminGalleryController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPackageController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientGalleryController;
use App\Http\Controllers\OrderTrackingController;
use App\Models\Gallery;
use App\Models\Package;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // 1. Ambil galeri yang telah diterbitkan oleh admin dari database
    $publishedGalleries = Gallery::with('photos')
        ->where('status', 'Diterbitkan')
        ->latest()
        ->get();

    // Default karya studio bila database belum memiliki banyak galeri
    $defaultWorks = [
        ['image' => 'work-1.jpg', 'height' => 440, 'title' => 'Ulang Tahun Pertama Si Kecil', 'category' => 'Ulang Tahun & Keluarga', 'slug' => null, 'is_private' => false],
        ['image' => 'work-2.jpg', 'height' => 260, 'title' => 'Tawa Bersama Mannan', 'category' => 'Ulang Tahun', 'slug' => null, 'is_private' => false],
        ['image' => 'work-3.jpg', 'height' => 280, 'title' => 'Pesta Cerita Rumah Uma', 'category' => 'Keluarga & Anak', 'slug' => null, 'is_private' => false],
        ['image' => 'work-4.jpg', 'height' => 420, 'title' => 'Melangkah Bersama di Taman', 'category' => 'Family Outdoor', 'slug' => null, 'is_private' => false],
        ['image' => 'work-5.jpg', 'height' => 300, 'title' => 'Dekorasi Panggung Ulang Tahun Ghaizka', 'category' => 'Event & Dekorasi', 'slug' => null, 'is_private' => false],
        ['image' => 'work-6.jpg', 'height' => 400, 'title' => 'Langkah Kebanggaan Wisuda', 'category' => 'Wisuda & Potret', 'slug' => null, 'is_private' => false],
    ];

    $allWorks = [];
    $heightCycle = [420, 280, 340, 400, 300, 440];
    $idx = 0;

    // Masukkan galeri yang telah diterbitkan admin
    foreach ($publishedGalleries as $g) {
        $firstPhoto = $g->photos->first();
        $img = 'work-' . (($idx % 6) + 1) . '.jpg';
        if ($firstPhoto && !empty($firstPhoto->photo_path)) {
            $img = $firstPhoto->photo_path;
        } elseif (!empty($g->cover_image)) {
            $img = $g->cover_image;
        }

        $allWorks[] = [
            'image' => $img,
            'height' => $heightCycle[$idx % count($heightCycle)],
            'title' => $g->title,
            'category' => 'Klien: ' . $g->client_name,
            'slug' => $g->slug,
            'is_private' => ($g->access_type === 'Privat'),
            'gallery_id' => $g->id,
        ];
        $idx++;
    }

    // Sertakan juga default works
    foreach ($defaultWorks as $dw) {
        $allWorks[] = $dw;
    }

    // Distribusikan ke dalam 3 kolom masonry
    $columns = [[], [], []];
    foreach ($allWorks as $i => $item) {
        $columns[$i % 3][] = $item;
    }

    // Ambil dari database dengan fallback
    $dbPackages = Package::where('is_active', true)->get();
    if ($dbPackages->isNotEmpty()) {
        $imageMap = [
            'birthday' => 'paket-birthday.jpg',
            'family-session' => 'paket-family.jpg',
            'graduation' => 'paket-graduation.jpg',
            'prewedding' => 'paket-prewedding.jpg',
        ];
        $labelMap = [
            'birthday' => 'RAYAKAN HARI SPESIAL',
            'family-session' => 'HANGAT & PENUH MAKNA',
            'graduation' => 'MOMEN KELULUSAN TERBAIK',
            'prewedding' => 'KISAH CINTA SINEMATIK',
        ];

        $packages = $dbPackages->map(function ($pkg) use ($imageMap, $labelMap) {
            return [
                'id' => $pkg->slug,
                'image' => $imageMap[$pkg->slug] ?? ($pkg->image ?: 'paket-birthday.jpg'),
                'featured' => (bool) $pkg->is_featured,
                'label' => $labelMap[$pkg->slug] ?? ($pkg->tagline ?: 'PAKET STUDIO'),
                'name' => $pkg->name,
                'description' => $pkg->description,
                'price' => 'Rp'.number_format($pkg->price, 0, ',', '.'),
                'items' => is_array($pkg->features) ? $pkg->features : [],
            ];
        })->toArray();
    } else {
        $packages = [
            [
                'id' => 'birthday',
                'image' => 'paket-birthday.jpg',
                'featured' => false,
                'label' => 'RAYAKAN HARI SPESIAL',
                'name' => 'Birthday',
                'description' => 'Sesi personal atau pesta ulang tahun dengan dekorasi balon/kue, mengabadikan momen spesial penuh kebahagiaan.',
                'price' => 'Rp1.250.000',
                'items' => [
                    'Properti kue & balon studio',
                    '15 foto edited resolusi tinggi',
                    'Akses private digital gallery 60 hari',
                    'Bebas bawa properti ultah sendiri',
                ],
            ],
            [
                'id' => 'graduation',
                'image' => 'paket-graduation.jpg',
                'featured' => false,
                'label' => 'MOMEN KELULUSAN TERBAIK',
                'name' => 'Graduation',
                'description' => 'Sesi toga dan wisuda formal maupun santai bersama orang tua dan sahabat tercinta.',
                'price' => 'Rp1.500.000',
                'items' => [
                    'Sesi toga wisudawan + keluarga inti',
                    '15 foto edited resolusi tinggi',
                    '1 cetak pembesar 10RS + frame kayu',
                    'Free peminjaman properti toga/ijazah mockup',
                ],
            ],
            [
                'id' => 'family-session',
                'image' => 'paket-family.jpg',
                'featured' => true,
                'label' => 'HANGAT & PENUH MAKNA',
                'name' => 'Family Session',
                'description' => 'Momen hangat keluarga tanpa kesan kaku dengan arahan pose luwes dan interaksi alami antar anggota keluarga.',
                'price' => 'Rp2.450.000',
                'items' => [
                    'Maksimal 6-8 anggota keluarga',
                    '2 konsep latar / ganti kostum',
                    '25 foto master edited & cetak 10R framed',
                    'Semua file original via private cloud',
                ],
            ],
            [
                'id' => 'prewedding',
                'image' => 'paket-prewedding.jpg',
                'featured' => false,
                'label' => 'KISAH CINTA SINEMATIK',
                'name' => 'Prewedding',
                'description' => 'Sesi pra-nikah terkonsep dengan arahan moodboard terkurasi, tata cahaya sinematik, dan hasil berstandar galeri.',
                'price' => 'Rp4.800.000',
                'items' => [
                    'Sesi studio & semi-outdoor',
                    '3 kali ganti busana/wardrobe',
                    '40 foto retouched detail warna filmic',
                    '1 album kolase eksklusif 20x30 cm',
                ],
            ],
        ];
    }

    return view('beranda', compact('columns', 'packages'));
});

// Pemesanan & Alur Checkout
Route::get('/pemesanan', [BookingController::class, 'create'])->name('pemesanan');
Route::post('/pemesanan', [BookingController::class, 'store'])->name('pemesanan.store');

Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');
Route::get('/checkout/unduh-invoice', [BookingController::class, 'downloadInvoice'])->name('checkout.download-invoice');

Route::get('/konfirmasi-pembayaran', [BookingController::class, 'showPaymentConfirmation'])->name('konfirmasi');
Route::post('/konfirmasi-pembayaran', [BookingController::class, 'storePaymentConfirmation'])->name('konfirmasi.store');

// Cek Pesanan
Route::get('/cek-pesanan', [OrderTrackingController::class, 'show'])->name('cek-pesanan');

// Galeri Klien Publik
Route::get('/galeri-klien', [ClientGalleryController::class, 'index'])->name('galeri-klien');
Route::post('/galeri-klien/verify-pin', [ClientGalleryController::class, 'verifyPin'])->name('galeri-klien.verify-pin');
Route::get('/galeri-klien/unduh-foto', [ClientGalleryController::class, 'downloadPhoto'])->name('galeri-klien.download-photo');
Route::get('/galeri-klien/unduh-semua', [ClientGalleryController::class, 'downloadAll'])->name('galeri-klien.download-all');

/*
|--------------------------------------------------------------------------
| Admin Backoffice Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Autentikasi Admin (Publik untuk halaman login & proses)
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Seluruh rute dashboard & pengelolaan admin dilindungi middleware 'admin.token'
    Route::middleware('admin.token')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });

        // Dashboard Dinamis
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Pesanan
        Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('admin.pesanan');
        Route::get('/pesanan/{id}', [AdminOrderController::class, 'show'])->name('admin.pesanan.detail');
        Route::put('/pesanan/{id}', [AdminOrderController::class, 'update'])->name('admin.pesanan.update');
        Route::post('/pesanan/{id}/konfirmasi-sesi', [AdminOrderController::class, 'konfirmasiSesi'])->name('admin.pesanan.konfirmasi-sesi');
        Route::post('/pesanan/{id}/selesai-sesi', [AdminOrderController::class, 'selesaikanSesi'])->name('admin.pesanan.selesai-sesi');
        Route::post('/pesanan/{id}/verifikasi-pembayaran', [AdminOrderController::class, 'verifikasiPembayaran'])->name('admin.pesanan.verifikasi-pembayaran');
        Route::post('/pesanan/{id}/verifikasi-dan-konfirmasi', [AdminOrderController::class, 'verifikasiDanKonfirmasi'])->name('admin.pesanan.verifikasi-dan-konfirmasi');
        Route::post('/pesanan/{id}/minta-perbaikan-bukti', [AdminOrderController::class, 'mintaPerbaikanBukti'])->name('admin.pesanan.minta-perbaikan-bukti');
        Route::post('/pesanan/{id}/batalkan-sesi', [AdminOrderController::class, 'batalkanSesi'])->name('admin.pesanan.batalkan-sesi');
        Route::post('/pesanan/{id}/arsipkan-sesi', [AdminOrderController::class, 'arsipkanSesi'])->name('admin.pesanan.arsipkan-sesi');
        Route::post('/pesanan/{id}/draft', [AdminOrderController::class, 'draftSesi'])->name('admin.pesanan.draft');
        Route::delete('/pesanan/{id}', [AdminOrderController::class, 'destroy'])->name('admin.pesanan.destroy');

        // Pembayaran
        Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('admin.pembayaran');
        Route::post('/pembayaran/manual', [AdminPaymentController::class, 'storeManual'])->name('admin.pembayaran.manual');
        Route::get('/pembayaran/{id}', [AdminPaymentController::class, 'show'])->name('admin.pembayaran.detail');
        Route::get('/pembayaran/{id}/berkas', [AdminPaymentController::class, 'viewFile'])->name('admin.pembayaran.view-file');
        Route::post('/pembayaran/{id}/verifikasi', [AdminPaymentController::class, 'verifikasi'])->name('admin.pembayaran.verifikasi-action');
        Route::post('/pembayaran/{id}/minta-perbaikan', [AdminPaymentController::class, 'mintaPerbaikan'])->name('admin.pembayaran.minta-perbaikan');
        Route::post('/pembayaran/{id}/catatan', [AdminPaymentController::class, 'updateCatatan'])->name('admin.pembayaran.catatan');

        // Jadwal Studio
        Route::get('/jadwal', [AdminScheduleController::class, 'index'])->name('admin.jadwal');
        Route::post('/jadwal/slot', [AdminScheduleController::class, 'store'])->name('admin.jadwal.slot.store');
        Route::put('/jadwal/slot/{id}', [AdminScheduleController::class, 'update'])->name('admin.jadwal.slot.update');
        Route::delete('/jadwal/slot/{id}', [AdminScheduleController::class, 'destroy'])->name('admin.jadwal.slot.destroy');

        // Pelanggan
        Route::get('/pelanggan', [AdminCustomerController::class, 'index'])->name('admin.pelanggan');
        Route::post('/pelanggan', [AdminCustomerController::class, 'store'])->name('admin.pelanggan.store');
        Route::get('/pelanggan/{id}', [AdminCustomerController::class, 'show'])->name('admin.pelanggan.detail');
        Route::put('/pelanggan/{id}', [AdminCustomerController::class, 'update'])->name('admin.pelanggan.update');
        Route::post('/pelanggan/{id}/toggle', [AdminCustomerController::class, 'toggleStatus'])->name('admin.pelanggan.toggle');

        // Paket Layanan
        Route::get('/paket', [AdminPackageController::class, 'index'])->name('admin.paket');
        Route::post('/paket', [AdminPackageController::class, 'store'])->name('admin.paket.store');
        Route::put('/paket/{id}', [AdminPackageController::class, 'update'])->name('admin.paket.update');
        Route::post('/paket/{id}/toggle', [AdminPackageController::class, 'toggleStatus'])->name('admin.paket.toggle');
        Route::delete('/paket/{id}', [AdminPackageController::class, 'destroy'])->name('admin.paket.destroy');

        // Galeri Pelanggan
        Route::get('/galeri', [AdminGalleryController::class, 'index'])->name('admin.galeri');
        Route::post('/galeri', [AdminGalleryController::class, 'store'])->name('admin.galeri.store');
        Route::put('/galeri/{id}', [AdminGalleryController::class, 'update'])->name('admin.galeri.update');
        Route::post('/galeri/{id}/status', [AdminGalleryController::class, 'updateStatus'])->name('admin.galeri.status');
        Route::delete('/galeri/{id}', [AdminGalleryController::class, 'destroy'])->name('admin.galeri.destroy');
        Route::delete('/galeri/foto/{photo_id}', [AdminGalleryController::class, 'destroyPhoto'])->name('admin.galeri.foto.destroy');

        // Pengaturan Studio
        Route::get('/pengaturan', [AdminSettingController::class, 'index'])->name('admin.pengaturan');
        Route::post('/pengaturan', [AdminSettingController::class, 'save'])->name('admin.pengaturan.save');
    });
});
