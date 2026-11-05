<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AuthController;
use App\Models\Package;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Karya terbaru: 3 kolom (masonry) dengan foto studio asli
    $columns = [
        [
            ['image' => 'work-1.jpg', 'height' => 440, 'title' => 'Ulang Tahun Pertama Si Kecil', 'category' => 'Ulang Tahun & Keluarga'],
            ['image' => 'work-2.jpg', 'height' => 260, 'title' => 'Tawa Bersama Mannan', 'category' => 'Ulang Tahun'],
        ],
        [
            ['image' => 'work-3.jpg', 'height' => 280, 'title' => 'Pesta Cerita Rumah Uma', 'category' => 'Keluarga & Anak'],
            ['image' => 'work-4.jpg', 'height' => 420, 'title' => 'Melangkah Bersama di Taman', 'category' => 'Family Outdoor'],
        ],
        [
            ['image' => 'work-5.jpg', 'height' => 300, 'title' => 'Dekorasi Panggung Ulang Tahun Ghaizka', 'category' => 'Event & Dekorasi'],
            ['image' => 'work-6.jpg', 'height' => 400, 'title' => 'Langkah Kebanggaan Wisuda', 'category' => 'Wisuda & Potret'],
        ],
    ];

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
                'image' => $imageMap[$pkg->slug] ?? 'paket-1.png',
                'featured' => (bool)$pkg->is_featured,
                'label' => $labelMap[$pkg->slug] ?? ($pkg->tagline ?: 'PAKET STUDIO'),
                'name' => $pkg->name,
                'description' => $pkg->description,
                'price' => 'Rp' . number_format($pkg->price, 0, ',', '.'),
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

Route::get('/konfirmasi-pembayaran', [BookingController::class, 'showPaymentConfirmation'])->name('konfirmasi');
Route::post('/konfirmasi-pembayaran', [BookingController::class, 'storePaymentConfirmation'])->name('konfirmasi.store');

// Cek Pesanan
Route::get('/cek-pesanan', [OrderTrackingController::class, 'show'])->name('cek-pesanan');

// Galeri Klien Publik
Route::get('/galeri-klien', function () {
    $data = session('last_booking', [
        'order_code' => 'CH-CONTOH-001',
        'invoice_number' => 'INV-CH-2026-001',
        'package_name' => 'Cerita Keluarga',
        'nama_lengkap' => 'Ayu Pramesti',
        'session_date' => 'Sabtu, 17 Oktober 2026',
        'session_time' => '15.30–17.00 WIB · 90 menit',
        'lokasi' => 'Rumah keluarga di Jakarta Selatan & taman terdekat',
    ]);

    $maskedEmail = 'a•••@gmail.com';
    $maskedPhone = '+62 8•• •••• 7890';

    return view('galeri-klien', compact('data', 'maskedEmail', 'maskedPhone'));
})->name('galeri-klien');

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

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Pesanan
        Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('admin.pesanan');
        Route::get('/pesanan/{id}', [AdminOrderController::class, 'show'])->name('admin.pesanan.detail');
        Route::post('/pesanan/{id}/konfirmasi-sesi', [AdminOrderController::class, 'konfirmasiSesi'])->name('admin.pesanan.konfirmasi-sesi');
        Route::post('/pesanan/{id}/batalkan-sesi', [AdminOrderController::class, 'batalkanSesi'])->name('admin.pesanan.batalkan-sesi');
        Route::post('/pesanan/{id}/arsipkan-sesi', [AdminOrderController::class, 'arsipkanSesi'])->name('admin.pesanan.arsipkan-sesi');

        // Pembayaran
        Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('admin.pembayaran');
        Route::get('/pembayaran/{id}', [AdminPaymentController::class, 'show'])->name('admin.pembayaran.detail');
        Route::post('/pembayaran/{id}/verifikasi', [AdminPaymentController::class, 'verifikasi'])->name('admin.pembayaran.verifikasi-action');
        Route::post('/pembayaran/{id}/minta-perbaikan', [AdminPaymentController::class, 'mintaPerbaikan'])->name('admin.pembayaran.minta-perbaikan');

        // Jadwal
        Route::get('/jadwal', function () {
            return view('admin.jadwal');
        })->name('admin.jadwal');

        // Pelanggan
        Route::get('/pelanggan', function () {
            $activeTab = request('tab', 'aktif');

            $customers = \App\Models\Customer::with('orders')->latest()->get()->map(function ($c) {
                $orderCount = $c->orders->count();
                return [
                    'name' => $c->name,
                    'id' => $c->customer_code,
                    'email' => $c->email,
                    'whatsapp' => $c->whatsapp,
                    'history' => $orderCount . ' pesanan',
                    'status' => ucfirst($c->status),
                ];
            })->toArray();

            if (empty($customers)) {
                $customers = [
                    [
                        'name' => 'Ayu Pramesti',
                        'id' => 'PLG-CONTOH-001',
                        'email' => 'ayu.pramesti@example.com',
                        'whatsapp' => '+62 8•• •••• 7890',
                        'history' => '1 pesanan aktif',
                        'status' => 'Aktif',
                    ],
                    [
                        'name' => 'Budi Santoso & Keluarga',
                        'id' => 'PLG-CONTOH-002',
                        'email' => 'budi.santoso@example.com',
                        'whatsapp' => '+62 8•• •••• 5432',
                        'history' => '1 sesi aktif',
                        'status' => 'Aktif',
                    ],
                ];
            }

            return view('admin.pelanggan', compact('customers', 'activeTab'));
        })->name('admin.pelanggan');

        // Paket
        Route::get('/paket', function () {
            $packages = \App\Models\Package::all();
            return view('admin.paket', compact('packages'));
        })->name('admin.paket');

        // Galeri
        Route::get('/galeri', function () {
            $activeTab = request('tab', 'semua');

            $allGalleries = [
                [
                    'title' => 'Cerita Keluarga Ayu',
                    'order_code' => 'CH-CONTOH-001',
                    'owner' => 'Ayu Pramesti',
                    'photos' => '6 / 50',
                    'note' => 'Sampel ilustrasi',
                    'status' => 'Penyuntingan',
                    'access' => 'Privat',
                    'action' => 'Detail →',
                ],
                [
                    'title' => 'Potret Nadia',
                    'order_code' => 'CH-CONTOH-002',
                    'owner' => 'Nadia Putri',
                    'photos' => '0 / 20',
                    'note' => '',
                    'status' => 'Draf',
                    'access' => 'Belum aktif',
                    'action' => 'Edit →',
                ],
                [
                    'title' => 'Cerita Dimas & Rani',
                    'order_code' => 'CH-CONTOH-003',
                    'owner' => 'Dimas & Rani',
                    'photos' => '18 / 50',
                    'note' => '',
                    'status' => 'Penyuntingan',
                    'access' => 'Privat',
                    'action' => 'Detail →',
                ],
                [
                    'title' => 'Editorial Ruang Reka',
                    'order_code' => 'CH-CONTOH-004',
                    'owner' => 'Ruang Reka',
                    'photos' => '0 / 80',
                    'note' => '',
                    'status' => 'Draf',
                    'access' => 'Belum aktif',
                    'action' => 'Edit →',
                ],
                [
                    'title' => 'Potret Sinta',
                    'order_code' => 'CH-CONTOH-005',
                    'owner' => 'Sinta Lestari',
                    'photos' => '0 / 20',
                    'note' => '',
                    'status' => 'Draf',
                    'access' => 'Belum aktif',
                    'action' => 'Edit →',
                ],
                [
                    'title' => 'Cerita Maya Sari',
                    'order_code' => 'CH-CONTOH-006',
                    'owner' => 'Maya Sari',
                    'photos' => '42 / 50',
                    'note' => '',
                    'status' => 'Siap',
                    'access' => 'Privat',
                    'action' => 'Pratinjau →',
                ],
                [
                    'title' => 'Potret Laras',
                    'order_code' => 'CH-CONTOH-007',
                    'owner' => 'Laras Dewi',
                    'photos' => '20 / 20',
                    'note' => 'Klien telah mengunduh zip',
                    'status' => 'Diterbitkan',
                    'access' => 'Terbuka',
                    'action' => 'Buka link ↗',
                ],
                [
                    'title' => 'Cerita Bima & Citra',
                    'order_code' => 'CH-CONTOH-008',
                    'owner' => 'Bima Aditya',
                    'photos' => '50 / 50',
                    'note' => 'Masa aktif s/d Des 2026',
                    'status' => 'Diterbitkan',
                    'access' => 'Privat',
                    'action' => 'Buka link ↗',
                ],
            ];

            $galleries = match ($activeTab) {
                'draf' => array_filter($allGalleries, fn($g) => $g['status'] === 'Draf'),
                'penyuntingan' => array_filter($allGalleries, fn($g) => $g['status'] === 'Penyuntingan'),
                'siap' => array_filter($allGalleries, fn($g) => $g['status'] === 'Siap'),
                'diterbitkan' => array_filter($allGalleries, fn($g) => $g['status'] === 'Diterbitkan'),
                default => $allGalleries,
            };

            return view('admin.galeri', compact('galleries', 'activeTab'));
        })->name('admin.galeri');

        // Pengaturan
        Route::get('/pengaturan', function () {
            return view('admin.pengaturan');
        })->name('admin.pengaturan');

        Route::post('/pengaturan', function () {
            return redirect()->route('admin.pengaturan')
                ->with('success', 'Pengaturan studio berhasil disimpan.');
        })->name('admin.pengaturan.save');
    });
});
