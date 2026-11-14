<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
                'birthday' => 'paket-birthday.jpg',
                'family-session' => 'paket-family.jpg',
                'graduation' => 'paket-graduation.jpg',
                'prewedding' => 'paket-prewedding.jpg',
            ];

            $locationNotes = [
                'birthday' => 'Sesi indoor studio dengan dekorasi pesta ulang tahun terkurasi.',
                'family-session' => 'Family session mencakup 2 konsep background studio atau 1 lokasi rumah.',
                'graduation' => 'Sesi studio foto toga bersama keluarga inti dan teman terdekat.',
                'prewedding' => 'Sesi studio eksklusif & semi-outdoor terarah dengan moodboard puitis.',
            ];

            return [
                'id' => $pkg->slug,
                'code' => $pkg->code,
                'name' => $pkg->name,
                'price' => 'Rp'.number_format($pkg->price, 0, ',', '.'),
                'raw_price' => $pkg->price,
                'duration' => $pkg->duration_minutes ?? 60,
                'duration_label' => $pkg->duration,
                'location_note' => $locationNotes[$pkg->slug] ?? ($pkg->tagline ?: 'Satu lokasi pilihan Anda.'),
                'image' => $imageMap[$pkg->slug] ?? ($pkg->image ?: 'paket-birthday.jpg'),
                'desc' => $pkg->description,
                'items' => is_array($pkg->features) ? $pkg->features : [],
            ];
        })->toArray();

        // Fallback jika database paket belum lengkap
        if (empty($packages)) {
            $packages = [
                [
                    'id' => 'birthday',
                    'name' => 'Birthday',
                    'price' => 'Rp1.250.000',
                    'raw_price' => 1250000,
                    'duration' => 60,
                    'duration_label' => '60 menit',
                    'location_note' => 'Sesi indoor studio dengan dekorasi pesta ulang tahun terkurasi.',
                    'image' => 'paket-birthday.jpg',
                    'items' => [
                        'Properti kue & balon studio',
                        '15 foto edited resolusi tinggi',
                        'Akses private digital gallery 60 hari',
                        'Bebas bawa properti ultah sendiri',
                    ],
                ],
                [
                    'id' => 'graduation',
                    'name' => 'Graduation',
                    'price' => 'Rp1.500.000',
                    'raw_price' => 1500000,
                    'duration' => 60,
                    'duration_label' => '60 menit',
                    'location_note' => 'Sesi studio foto toga bersama keluarga inti dan teman terdekat.',
                    'image' => 'paket-graduation.jpg',
                    'items' => [
                        'Sesi toga wisudawan + keluarga inti',
                        '15 foto edited resolusi tinggi',
                        '1 cetak pembesar 10RS + frame kayu',
                        'Free peminjaman properti toga/ijazah mockup',
                    ],
                ],
                [
                    'id' => 'family-session',
                    'name' => 'Family Session',
                    'price' => 'Rp2.450.000',
                    'raw_price' => 2450000,
                    'duration' => 90,
                    'duration_label' => '90 menit',
                    'location_note' => 'Family session mencakup 2 konsep background studio atau 1 lokasi rumah.',
                    'image' => 'paket-family.jpg',
                    'items' => [
                        'Maksimal 6-8 anggota keluarga',
                        '2 konsep latar / ganti kostum',
                        '25 foto master edited & cetak 10R framed',
                        'Semua file original via private cloud',
                    ],
                ],
                [
                    'id' => 'prewedding',
                    'name' => 'Prewedding',
                    'price' => 'Rp4.800.000',
                    'raw_price' => 4800000,
                    'duration' => 180,
                    'duration_label' => '180 menit',
                    'location_note' => 'Sesi studio eksklusif & semi-outdoor terarah dengan moodboard puitis.',
                    'image' => 'paket-prewedding.jpg',
                    'items' => [
                        'Sesi studio & semi-outdoor',
                        '3 kali ganti busana/wardrobe',
                        '40 foto retouched detail warna filmic',
                        '1 album kolase eksklusif 20x30 cm',
                    ],
                ],
            ];
        }

        $selectedPackageId = $request->query('package', 'family-session');

        // Kalender Dinamis Sesi Studio
        $bookingMonth = Carbon::now();
        $startOfMonth = $bookingMonth->copy()->startOfMonth();
        $daysInMonth = $bookingMonth->daysInMonth;
        $dayOfWeekOffset = ($startOfMonth->dayOfWeekIso - 1); // 0 (Mon) to 6 (Sun)
        $today = Carbon::today();

        // Default tanggal terpilih: 3 hari ke depan dalam bulan ini
        $defaultSelectedDate = $today->copy()->addDays(3);
        if ($defaultSelectedDate->month !== $bookingMonth->month) {
            $defaultSelectedDate = $today->copy();
        }

        return view('pemesanan', compact(
            'packages',
            'selectedPackageId',
            'bookingMonth',
            'startOfMonth',
            'daysInMonth',
            'dayOfWeekOffset',
            'today',
            'defaultSelectedDate'
        ));
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
            'birthday' => 'paket-birthday.jpg',
            'family-session' => 'paket-family.jpg',
            'graduation' => 'paket-graduation.jpg',
            'prewedding' => 'paket-prewedding.jpg',
        ];

        $locationHints = [
            'birthday' => 'Sesi studio indoor dekorasi pesta ulang tahun terkurasi.',
            'family-session' => 'Family session mencakup 2 konsep background studio atau 1 lokasi rumah.',
            'graduation' => 'Sesi studio foto toga bersama keluarga inti dan teman terdekat.',
            'prewedding' => 'Sesi studio eksklusif & semi-outdoor terarah dengan moodboard puitis.',
        ];

        $packageName = $package ? $package->name : ucwords(str_replace('-', ' ', $packageSlug));
        $packagePrice = $package ? $package->price : 2450000;
        $packagePriceFormatted = 'Rp'.number_format($packagePrice, 0, ',', '.');
        $packageImage = $imageMap[$packageSlug] ?? ($package && $package->image ? $package->image : 'paket-family.jpg');
        $packageDesc = $package ? $package->description : 'Sesi foto terarah studio.';
        $packageItems = $package && is_array($package->features) ? $package->features : [
            'Sesi foto studio terkurasi',
            'File resolusi tinggi via cloud pribadi',
            'Izin cetak pribadi',
        ];
        $lokasiHint = $locationHints[$packageSlug] ?? ($package ? $package->tagline : 'Satu lokasi pilihan Anda.');

        // 1. Simpan atau perbarui Customer
        $customer = Customer::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['nama_lengkap'],
                'whatsapp' => $validated['whatsapp'],
                'customer_code' => 'CUST-'.date('Y').'-'.strtoupper(Str::random(4)),
                'status' => 'aktif',
            ]
        );

        // 2. Generate Nomor Unik
        $nextOrderNum = Order::count() + 1;
        $orderCode = 'CH-'.date('Y').'-'.str_pad($nextOrderNum, 3, '0', STR_PAD_LEFT);
        $invoiceNumber = 'INV-CH-'.date('Y').'-'.str_pad($nextOrderNum, 3, '0', STR_PAD_LEFT);

        // Tanggal sesi
        $rawSessionDate = $validated['session_date'] ?? date('Y-m-d', strtotime('+7 days'));
        // Konversi ke format Y-m-d bila dalam string bahasa
        $sessionDateSql = date('Y-m-d', strtotime($rawSessionDate) ?: strtotime('+7 days'));

        $rawTimeSlot = ($validated['session_time'] ?? '15.30').'–17.00 WIB · 90 menit';

        // Proteksi Idempotency / Anti-Duplicate Submission:
        // Jika pelanggan yang sama baru saja mengirim pesanan paket dan tanggal yang sama dalam 30 detik terakhir, gunakan pesanan tersebut
        $recentDuplicateOrder = Order::where('customer_email', $customer->email)
            ->where('package_name', $packageName)
            ->where('session_date', $sessionDateSql)
            ->where('payment_status', 'Belum dibayar')
            ->where('created_at', '>=', now()->subSeconds(30))
            ->latest()
            ->first();

        if ($recentDuplicateOrder) {
            $order = $recentDuplicateOrder;
        } else {
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
                'location' => $validated['lokasi'] ?? 'Studio Utama naro capture',
                'customer_notes' => $validated['catatan'] ?? null,
                'session_status' => 'Permintaan',
                'payment_status' => 'Belum dibayar',
            ]);
        }

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
    public function checkout(Request $request)
    {
        $orderCode = $request->query('order');
        $order = null;
        if ($orderCode) {
            $order = Order::with('package')->where('order_code', $orderCode)->orWhere('id', $orderCode)->first();
        }

        $data = null;
        if (! $order) {
            $sessionData = session('last_booking');
            if ($sessionData && isset($sessionData['order_code'])) {
                $order = Order::with('package')->where('order_code', $sessionData['order_code'])->first();
            }
        }

        if (! $order) {
            $order = Order::with('package')->latest()->first();
        }

        if ($order) {
            $packageImage = 'paket-family.jpg';
            if ($order->package && ! empty($order->package->image)) {
                $packageImage = $order->package->image;
            } elseif (! empty($order->package_name)) {
                $pkgNameLower = strtolower($order->package_name);
                if (str_contains($pkgNameLower, 'graduat') || str_contains($pkgNameLower, 'wisuda')) {
                    $packageImage = 'paket-graduation.jpg';
                } elseif (str_contains($pkgNameLower, 'birth') || str_contains($pkgNameLower, 'ulang tahun')) {
                    $packageImage = 'paket-birthday.jpg';
                } elseif (str_contains($pkgNameLower, 'prewed')) {
                    $packageImage = 'paket-prewedding.jpg';
                } elseif (str_contains($pkgNameLower, 'fam') || str_contains($pkgNameLower, 'keluarga')) {
                    $packageImage = 'paket-family.jpg';
                }
            }

            $data = [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'invoice_number' => $order->invoice_number,
                'package_name' => $order->package_name,
                'package_price' => 'Rp'.number_format($order->total_price, 0, ',', '.'),
                'package_duration' => $order->package ? ($order->package->duration_minutes ?? 90) : 90,
                'package_image' => $packageImage,
                'package_desc' => $order->package ? $order->package->description : 'Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.',
                'lokasi_hint' => $order->package ? $order->package->tagline : 'Dua lokasi termasuk dalam paket. Alamat dan lokasi akhir dibahas saat perencanaan.',
                'package_items' => ($order->package && is_array($order->package->features)) ? $order->package->features : [
                    'Sesi 90 menit dengan narasi yang lebih dalam',
                    'Dua lokasi dan dua ganti kostum',
                    '50 foto diedit dengan hati-hati',
                    'Panggilan perencanaan & panduan styling',
                    'Galeri pribadi + izin cetak',
                ],
                'session_date' => $order->session_date ? $order->session_date->format('l, d F Y') : date('l, d F Y'),
                'session_time' => $order->session_time_slot,
                'nama_lengkap' => $order->customer_name,
                'email' => $order->customer_email,
                'whatsapp' => $order->customer_whatsapp,
                'lokasi' => $order->location,
                'catatan' => $order->customer_notes,
                'payment_status' => $order->payment_status ?: 'Belum dibayar',
                'session_status' => $order->session_status ?: 'Permintaan',
            ];
        }

        return view('checkout', compact('data'));
    }

    /**
     * Unduh Draf Invoice PDF
     */
    public function downloadInvoice(Request $request)
    {
        $data = session('last_booking');

        if (! $data) {
            $orderCode = $request->query('order');
            $query = Order::query();
            if ($orderCode) {
                $query->where('order_code', $orderCode);
            }
            $order = $query->latest()->first();

            if ($order) {
                $data = [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'invoice_number' => $order->invoice_number,
                    'package_name' => $order->package_name,
                    'package_price' => 'Rp'.number_format($order->total_price, 0, ',', '.'),
                    'package_duration' => 90,
                    'session_date' => $order->session_date ? $order->session_date->format('d F Y') : date('d F Y'),
                    'session_time' => $order->session_time_slot,
                    'nama_lengkap' => $order->customer_name,
                    'email' => $order->customer_email,
                    'whatsapp' => $order->customer_whatsapp,
                    'lokasi' => $order->location,
                    'catatan' => $order->customer_notes,
                ];
            } else {
                return redirect()->route('checkout');
            }
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('data'));

        $filename = 'Invoice-'.Str::slug($data['invoice_number'] ?? 'Draf-Pesanan').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Tampilkan form Konfirmasi Pembayaran
     */
    public function showPaymentConfirmation(Request $request)
    {
        $orderCode = $request->query('order');
        $order = null;
        if ($orderCode) {
            $order = Order::with('package')->where('order_code', $orderCode)->orWhere('id', $orderCode)->first();
        }

        $data = null;
        if (! $order) {
            $data = session('last_booking');
            if ($data && isset($data['order_code'])) {
                $order = Order::with('package')->where('order_code', $data['order_code'])->first();
            }
        }

        if (! $order && ! $data) {
            $order = Order::with('package')->latest()->first();
        }

        if ($order && ! $data) {
            $packageImage = 'paket-family.jpg';
            if ($order->package && ! empty($order->package->image)) {
                $packageImage = $order->package->image;
            } elseif (! empty($order->package_name)) {
                $pkgNameLower = strtolower($order->package_name);
                if (str_contains($pkgNameLower, 'graduat') || str_contains($pkgNameLower, 'wisuda')) {
                    $packageImage = 'paket-graduation.jpg';
                } elseif (str_contains($pkgNameLower, 'birth') || str_contains($pkgNameLower, 'ulang tahun')) {
                    $packageImage = 'paket-birthday.jpg';
                } elseif (str_contains($pkgNameLower, 'prewed')) {
                    $packageImage = 'paket-prewedding.jpg';
                } elseif (str_contains($pkgNameLower, 'fam') || str_contains($pkgNameLower, 'keluarga')) {
                    $packageImage = 'paket-family.jpg';
                }
            }

            $data = [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'invoice_number' => $order->invoice_number,
                'package_name' => $order->package_name,
                'package_price' => 'Rp'.number_format($order->total_price, 0, ',', '.'),
                'package_duration' => $order->package ? ($order->package->duration_minutes ?? 90) : 90,
                'package_image' => $packageImage,
                'package_desc' => $order->package ? $order->package->description : 'Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.',
                'lokasi_hint' => $order->package ? $order->package->tagline : 'Dua lokasi termasuk dalam paket. Alamat dan lokasi akhir dibahas saat perencanaan.',
                'package_items' => ($order->package && is_array($order->package->features)) ? $order->package->features : [
                    'Sesi 90 menit dengan narasi yang lebih dalam',
                    'Dua lokasi dan dua ganti kostum',
                    '50 foto diedit dengan hati-hati',
                    'Panggilan perencanaan & panduan styling',
                    'Galeri pribadi + izin cetak',
                ],
                'session_date' => $order->session_date ? $order->session_date->format('l, d F Y') : date('l, d F Y'),
                'session_time' => $order->session_time_slot,
                'nama_lengkap' => $order->customer_name,
                'email' => $order->customer_email,
                'whatsapp' => $order->customer_whatsapp,
                'lokasi' => $order->location,
                'catatan' => $order->customer_notes,
            ];
        }

        $metode = $request->query('metode', 'transfer');
        $isSubmitted = $request->query('status') === 'success' || session()->has('submitted_payment');

        $submittedPayment = session('submitted_payment');
        if (! $submittedPayment && $isSubmitted) {
            $latestPayment = Payment::latest()->first();
            if ($latestPayment) {
                $submittedPayment = [
                    'nama_pengirim' => $latestPayment->sender_name,
                    'tanggal_transfer' => $latestPayment->transfer_date ? $latestPayment->transfer_date->format('Y-m-d') : date('Y-m-d'),
                    'bank_sumber' => $latestPayment->bank_source ?? 'Bank BCA',
                    'nominal' => 'Rp'.number_format($latestPayment->nominal, 0, ',', '.'),
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
            'order_code' => 'nullable|string',
            'nama_pengirim' => 'required|string|max:255',
            'tanggal_transfer' => 'required|date',
            'bank_sumber' => 'required|string|max:100',
            'nominal' => 'required|string',
            'catatan_pembayaran' => 'nullable|string',
            'struk_pembayaran' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $order = null;
        if (! empty($validated['order_code'])) {
            $order = Order::where('order_code', $validated['order_code'])->orWhere('id', $validated['order_code'])->first();
        }

        if (! $order) {
            $orderData = session('last_booking');
            if ($orderData && isset($orderData['order_code'])) {
                $order = Order::where('order_code', $orderData['order_code'])->first();
            }
        }

        if (! $order) {
            $order = Order::latest()->first();
        }

        // Simpan berkas bukti bila ada
        $filePath = null;
        $fileOriginalName = null;
        $fileExt = 'NONE';

        if ($request->hasFile('struk_pembayaran')) {
            $file = $request->file('struk_pembayaran');
            $fileOriginalName = $file->getClientOriginalName();
            $fileExt = strtoupper($file->getClientOriginalExtension());
            $filePath = $file->store('payments', 'public');
        }

        // Parse nominal number
        $rawNominal = preg_replace('/[^0-9]/', '', $validated['nominal']);
        $nominalInt = (int) $rawNominal ?: ($order ? $order->total_price : 9500000);

        $paymentCode = 'BYR-'.date('Y').'-'.str_pad(Payment::count() + 1, 3, '0', STR_PAD_LEFT);

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
            'nominal' => 'Rp'.number_format($nominalInt, 0, ',', '.'),
            'catatan_pembayaran' => $validated['catatan_pembayaran'] ?? '',
            'file_name' => $fileOriginalName,
            'file_ext' => $fileExt,
        ];

        session(['submitted_payment' => $submittedData]);

        return redirect()->route('konfirmasi', ['status' => 'success'])
            ->with('success', 'Bukti Anda sudah diterima oleh studio. Pantau hasil pemeriksaan melalui status pesanan.');
    }
}
