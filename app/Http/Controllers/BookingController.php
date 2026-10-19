<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Models\StudioSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Tampilkan halaman formulir pemesanan publik
     */
    public function create(Request $request)
    {
        $dbPackages = Package::where('is_active', true)->get();

        $packages = $dbPackages->map(function ($pkg) {
            $imageMap = [
                'potret-pribadi' => 'paket-1.png',
                'cerita-keluarga' => 'paket-2.png',
                'editorial-bisnis' => 'paket-3.png',
            ];

            return [
                'id' => $pkg->slug,
                'code' => $pkg->code,
                'name' => $pkg->name,
                'price' => 'Rp' . number_format($pkg->price, 0, ',', '.'),
                'raw_price' => $pkg->price,
                'duration' => $pkg->duration_minutes ?? 60,
                'duration_label' => $pkg->duration,
                'location_note' => $pkg->slug === 'cerita-keluarga'
                    ? 'Cerita Keluarga mencakup dua lokasi. Alamat dan lokasi akhir dibahas saat perencanaan.'
                    : ($pkg->slug === 'editorial-bisnis'
                        ? 'Editorial Bisnis mencakup beberapa lokasi studio & komersial pilihan Anda.'
                        : 'Potret Pribadi mencakup satu lokasi pilihan Anda.'),
                'image' => $imageMap[$pkg->slug] ?? 'paket-1.png',
                'desc' => $pkg->description,
                'items' => is_array($pkg->features) ? $pkg->features : [],
            ];
        })->toArray();

        // Fallback jika database paket belum lengkap
        if (empty($packages)) {
            $packages = [
                [
                    'id' => 'potret-pribadi',
                    'name' => 'Potret Pribadi',
                    'price' => 'Rp4.500.000',
                    'raw_price' => 4500000,
                    'duration' => 45,
                    'duration_label' => '45 menit',
                    'location_note' => 'Potret Pribadi mencakup satu lokasi pilihan Anda.',
                    'image' => 'paket-1.png',
                    'items' => [
                        'Sesi 45 menit dengan panduan ringan',
                        'Satu lokasi pilihan Anda',
                        '20 foto diedit dengan hati-hati',
                        'Galeri online pribadi',
                        'Izin cetak pribadi',
                    ],
                ],
                [
                    'id' => 'cerita-keluarga',
                    'name' => 'Cerita Keluarga',
                    'price' => 'Rp9.500.000',
                    'raw_price' => 9500000,
                    'duration' => 90,
                    'duration_label' => '90 menit',
                    'location_note' => 'Cerita Keluarga mencakup dua lokasi. Alamat dan lokasi akhir dibahas saat perencanaan.',
                    'image' => 'paket-2.png',
                    'items' => [
                        'Sesi 90 menit dengan narasi yang lebih dalam',
                        'Dua lokasi dan dua ganti kostum',
                        '50 foto diedit dengan hati-hati',
                        'Panggilan perencanaan & panduan styling',
                        'Galeri pribadi + izin cetak',
                    ],
                ],
                [
                    'id' => 'editorial-bisnis',
                    'name' => 'Editorial Bisnis',
                    'price' => 'Rp18.000.000',
                    'raw_price' => 18000000,
                    'duration' => 240,
                    'duration_label' => '240 menit',
                    'location_note' => 'Editorial Bisnis mencakup beberapa lokasi studio & komersial pilihan Anda.',
                    'image' => 'paket-3.png',
                    'items' => [
                        'Sesi setengah hari untuk konten bisnis',
                        'Beberapa lokasi & ganti kostum',
                        '80 foto diedit untuk media sosial & iklan',
                        'Arahan kreatif & moodboard',
                        'Izin komersial untuk merek',
                    ],
                ],
            ];
        }

        $selectedPackageId = $request->query('package', 'cerita-keluarga');

        return view('pemesanan', compact('packages', 'selectedPackageId'));
    }

    /**
     * Proses penyimpanan pemesanan baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|string',
            'session_date' => 'nullable|string',
            'session_time' => 'nullable|string',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'lokasi' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $packageSlug = $validated['package_id'];
        $package = Package::where('slug', $packageSlug)->first();

        $imageMap = [
            'potret-pribadi' => 'paket-1.png',
            'cerita-keluarga' => 'paket-2.png',
            'editorial-bisnis' => 'paket-3.png',
        ];

        $packageName = $package ? $package->name : ucwords(str_replace('-', ' ', $packageSlug));
        $packagePrice = $package ? $package->price : 9500000;
        $packagePriceFormatted = 'Rp' . number_format($packagePrice, 0, ',', '.');
        $packageImage = $imageMap[$packageSlug] ?? 'paket-2.png';
        $packageDesc = $package ? $package->description : 'Sesi foto terarah studio.';
        $packageItems = $package && is_array($package->features) ? $package->features : [
            'Sesi foto studio terkurasi',
            'File resolusi tinggi via cloud pribadi',
            'Izin cetak pribadi'
        ];
        $lokasiHint = $packageSlug === 'cerita-keluarga'
            ? 'Dua lokasi termasuk dalam paket. Alamat dan lokasi akhir dibahas saat perencanaan.'
            : ($packageSlug === 'editorial-bisnis'
                ? 'Beberapa lokasi studio & komersial pilihan Anda.'
                : 'Satu lokasi pilihan Anda.');

        // 1. Simpan atau perbarui Customer
        $customer = Customer::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['nama_lengkap'],
                'whatsapp' => $validated['whatsapp'],
                'customer_code' => 'CUST-' . date('Y') . '-' . strtoupper(Str::random(4)),
                'status' => 'aktif',
            ]
        );

        // 2. Generate Nomor Unik
        $nextOrderNum = Order::count() + 1;
        $orderCode = 'CH-CONTOH-' . str_pad($nextOrderNum, 3, '0', STR_PAD_LEFT);
        $invoiceNumber = 'INV-CH-' . date('Y') . '-' . str_pad($nextOrderNum, 3, '0', STR_PAD_LEFT);

        // Tanggal sesi
        $rawSessionDate = $validated['session_date'] ?? date('Y-m-d', strtotime('+7 days'));
        // Konversi ke format Y-m-d bila dalam string bahasa
        $sessionDateSql = date('Y-m-d', strtotime($rawSessionDate) ?: strtotime('+7 days'));

        $rawTimeSlot = ($validated['session_time'] ?? '15.30') . '–17.00 WIB · 90 menit';

        // 3. Simpan Order ke Database
        $order = Order::create([
            'order_code' => $orderCode,
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customer->id,
            'package_id' => $package ? $package->id : null,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => $customer->whatsapp,
            'package_name' => $packageName,
            'total_price' => $packagePrice,
            'session_date' => $sessionDateSql,
            'session_time_slot' => $rawTimeSlot,
            'location' => $validated['lokasi'] ?? 'Studio Utama Clara Hayes',
            'customer_notes' => $validated['catatan'] ?? null,
            'session_status' => 'Permintaan',
            'payment_status' => 'Belum dibayar',
        ]);

        // Simpan juga ke session untuk aliran UX instan tanpa login
        $bookingData = [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'invoice_number' => $order->invoice_number,
            'package_id' => $packageSlug,
            'package_name' => $order->package_name,
            'package_price' => $packagePriceFormatted,
            'package_raw_price' => $packagePrice,
            'package_image' => $packageImage,
            'package_desc' => $packageDesc,
            'package_items' => $packageItems,
            'lokasi_hint' => $lokasiHint,
            'session_date' => $validated['session_date'] ?? date('l, d F Y', strtotime($sessionDateSql)),
            'session_time' => $rawTimeSlot,
            'nama_lengkap' => $order->customer_name,
            'email' => $order->customer_email,
            'whatsapp' => $order->customer_whatsapp,
            'lokasi' => $order->location,
            'catatan' => $order->customer_notes,
        ];

        session(['last_booking' => $bookingData]);

        return redirect()->route('checkout');
    }

    /**
     * Tampilkan halaman Checkout
     */
    public function checkout()
    {
        $defaultOrder = Order::latest()->first();

        $data = session('last_booking');

        if (!$data && $defaultOrder) {
            $data = [
                'order_id' => $defaultOrder->id,
                'order_code' => $defaultOrder->order_code,
                'invoice_number' => $defaultOrder->invoice_number,
                'package_name' => $defaultOrder->package_name,
                'package_price' => 'Rp' . number_format($defaultOrder->total_price, 0, ',', '.'),
                'package_image' => 'paket-2.png',
                'package_desc' => 'Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.',
                'lokasi_hint' => 'Dua lokasi termasuk dalam paket. Alamat dan lokasi akhir dibahas saat perencanaan.',
                'package_items' => [
                    'Sesi 90 menit dengan narasi yang lebih dalam',
                    'Dua lokasi dan dua ganti kostum',
                    '50 foto diedit dengan hati-hati',
                    'Panggilan perencanaan & panduan styling',
                    'Galeri pribadi + izin cetak',
                ],
                'session_date' => $defaultOrder->session_date ? $defaultOrder->session_date->format('d F Y') : '17 Oktober 2026',
                'session_time' => $defaultOrder->session_time_slot,
                'nama_lengkap' => $defaultOrder->customer_name,
                'email' => $defaultOrder->customer_email,
                'whatsapp' => $defaultOrder->customer_whatsapp,
                'lokasi' => $defaultOrder->location,
                'catatan' => $defaultOrder->customer_notes,
            ];
        }

        return view('checkout', compact('data'));
    }

    /**
     * Tampilkan form Konfirmasi Pembayaran
     */
    public function showPaymentConfirmation(Request $request)
    {
        $data = session('last_booking');

        if (!$data) {
            $order = Order::latest()->first();
            if ($order) {
                $data = [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'invoice_number' => $order->invoice_number,
                    'package_name' => $order->package_name,
                    'package_price' => 'Rp' . number_format($order->total_price, 0, ',', '.'),
                    'package_duration' => 90,
                    'package_image' => 'paket-2.png',
                    'package_desc' => 'Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.',
                    'lokasi_hint' => 'Dua lokasi termasuk dalam paket. Alamat dan lokasi akhir dibahas saat perencanaan.',
                    'package_items' => [
                        'Sesi 90 menit dengan narasi yang lebih dalam',
                        'Dua lokasi dan dua ganti kostum',
                        '50 foto diedit dengan hati-hati',
                        'Panggilan perencanaan & panduan styling',
                        'Galeri pribadi + izin cetak',
                    ],
                    'session_date' => $order->session_date ? $order->session_date->format('d F Y') : 'Sabtu, 17 Oktober 2026',
                    'session_time' => $order->session_time_slot,
                    'nama_lengkap' => $order->customer_name,
                    'email' => $order->customer_email,
                    'whatsapp' => $order->customer_whatsapp,
                    'lokasi' => $order->location,
                    'catatan' => $order->customer_notes,
                ];
            }
        }

        $metode = $request->query('metode', 'transfer');
        $isSubmitted = $request->query('status') === 'success' || session()->has('submitted_payment');
        
        $submittedPayment = session('submitted_payment');
        if (!$submittedPayment && $isSubmitted) {
            $latestPayment = Payment::latest()->first();
            if ($latestPayment) {
                $submittedPayment = [
                    'nama_pengirim' => $latestPayment->sender_name,
                    'tanggal_transfer' => $latestPayment->transfer_date ? $latestPayment->transfer_date->format('Y-m-d') : date('Y-m-d'),
                    'bank_sumber' => $latestPayment->bank_source ?? 'Bank BCA',
                    'nominal' => 'Rp' . number_format($latestPayment->nominal, 0, ',', '.'),
                    'catatan_pembayaran' => $latestPayment->sender_notes,
                    'file_name' => $latestPayment->file_original_name ?? 'bukti-transfer.pdf',
                    'file_ext' => $latestPayment->file_extension ?? 'PDF',
                ];
            }
        }

        return view('konfirmasi-pembayaran', compact('data', 'metode', 'isSubmitted', 'submittedPayment'));
    }

    /**
     * Simpan bukti transfer pembayaran ke storage & database
     */
    public function storePaymentConfirmation(Request $request)
    {
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'tanggal_transfer' => 'required|date',
            'bank_sumber' => 'required|string|max:100',
            'nominal' => 'required|string',
            'catatan_pembayaran' => 'nullable|string',
            'struk_pembayaran' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $orderData = session('last_booking');
        $order = null;

        if ($orderData && isset($orderData['order_code'])) {
            $order = Order::where('order_code', $orderData['order_code'])->first();
        }

        if (!$order) {
            $order = Order::latest()->first();
        }

        // Simpan berkas bukti bila ada
        $filePath = null;
        $fileOriginalName = 'bukti-transfer.pdf';
        $fileExt = 'PDF';

        if ($request->hasFile('struk_pembayaran')) {
            $file = $request->file('struk_pembayaran');
            $fileOriginalName = $file->getClientOriginalName();
            $fileExt = strtoupper($file->getClientOriginalExtension());
            $filePath = $file->store('payments', 'public');
        }

        // Parse nominal number
        $rawNominal = preg_replace('/[^0-9]/', '', $validated['nominal']);
        $nominalInt = (int)$rawNominal ?: ($order ? $order->total_price : 9500000);

        $paymentCode = 'BYR-' . date('Y') . '-' . str_pad(Payment::count() + 1, 3, '0', STR_PAD_LEFT);

        if ($order) {
            $payment = Payment::create([
                'payment_code' => $paymentCode,
                'order_id' => $order->id,
                'sender_name' => $validated['nama_pengirim'],
                'transfer_date' => $validated['tanggal_transfer'],
                'bank_source' => $validated['bank_sumber'],
                'nominal' => $nominalInt,
                'sender_notes' => $validated['catatan_pembayaran'] ?? null,
                'proof_file' => $filePath,
                'file_original_name' => $fileOriginalName,
                'file_extension' => $fileExt,
                'status' => 'Bukti terkirim',
            ]);

            // Update status pesanan
            $order->update([
                'payment_status' => 'Bukti terkirim',
            ]);
        }

        $submittedData = [
            'nama_pengirim' => $validated['nama_pengirim'],
            'tanggal_transfer' => $validated['tanggal_transfer'],
            'bank_sumber' => $validated['bank_sumber'],
            'nominal' => 'Rp' . number_format($nominalInt, 0, ',', '.'),
            'catatan_pembayaran' => $validated['catatan_pembayaran'] ?? '',
            'file_name' => $fileOriginalName,
            'file_ext' => $fileExt,
        ];

        session(['submitted_payment' => $submittedData]);

        return redirect()->route('konfirmasi', ['status' => 'success'])
            ->with('success', 'Bukti Anda sudah diterima oleh studio. Pantau hasil pemeriksaan melalui status pesanan.');
    }
}
