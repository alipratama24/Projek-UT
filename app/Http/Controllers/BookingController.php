<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Models\StudioSlot;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Dapatkan peta jadwal dan ketersediaan slot studio berdasarkan data admin di StudioSlot & Order aktif
     */
    private function getScheduleMap(): array
    {
        // 1. Ambil semua StudioSlot
        $studioSlots = StudioSlot::orderBy('date')->orderBy('start_time')->get();

        // 2. Ambil semua Order aktif
        $activeOrders = Order::whereNotNull('session_date')
            ->whereNotIn('session_status', ['Dibatalkan'])
            ->get();

        $scheduleMap = [];

        // Petakan slot studio yang diinput admin
        foreach ($studioSlots as $slot) {
            $dateStr = $slot->date ? $slot->date->format('Y-m-d') : null;
            if (! $dateStr) {
                continue;
            }

            $startClean = substr($slot->start_time, 0, 5);
            $startFormatted = str_replace(':', '.', $startClean);
            $endClean = substr($slot->end_time, 0, 5);
            $endFormatted = str_replace(':', '.', $endClean);

            $statusLabel = match ($slot->type) {
                'available' => 'Tersedia',
                'booked' => 'Penuh',
                'blocked' => 'Blokir',
                default => 'Tersedia'
            };

            $isAvailable = ($slot->type === 'available');

            if (! isset($scheduleMap[$dateStr])) {
                $scheduleMap[$dateStr] = [
                    'has_custom_slots' => true,
                    'is_blocked' => false,
                    'slots' => [],
                ];
            }

            $scheduleMap[$dateStr]['slots'][] = [
                'time' => $startFormatted,
                'end_time' => $endFormatted,
                'time_display' => "{$startFormatted}–{$endFormatted}",
                'status' => $statusLabel,
                'is_available' => $isAvailable,
                'type' => $slot->type,
                'note' => $slot->note,
            ];

            if ($slot->type === 'blocked' && (empty($slot->start_time) || ($slot->start_time <= '09:00:00' && $slot->end_time >= '17:00:00'))) {
                $scheduleMap[$dateStr]['is_blocked'] = true;
            }
        }

        // Petakan juga pesanan aktif dari pelanggan
        foreach ($activeOrders as $ord) {
            $dateStr = $ord->session_date ? $ord->session_date->format('Y-m-d') : null;
            if (! $dateStr) {
                continue;
            }

            // Ekstrak jam mulai, misal "15.30–17.00 WIB" -> "15.30"
            if (preg_match('/^(\d{1,2}[:.]\d{2})/u', $ord->session_time_slot, $m)) {
                $ordStart = str_replace(':', '.', $m[1]);

                if (isset($scheduleMap[$dateStr])) {
                    $matched = false;
                    foreach ($scheduleMap[$dateStr]['slots'] as &$slotItem) {
                        if ($slotItem['time'] === $ordStart) {
                            $slotItem['is_available'] = false;
                            $slotItem['status'] = 'Penuh';
                            $slotItem['type'] = 'booked';
                            $matched = true;
                        }
                    }
                    unset($slotItem);

                    if (! $matched) {
                        $scheduleMap[$dateStr]['slots'][] = [
                            'time' => $ordStart,
                            'end_time' => null,
                            'time_display' => $ordStart,
                            'status' => 'Penuh',
                            'is_available' => false,
                            'type' => 'booked',
                            'note' => 'Sesi '.$ord->customer_name,
                        ];
                    }
                } else {
                    $defaultSlots = [
                        ['time' => '09.00', 'end_time' => '10.30', 'time_display' => '09.00', 'status' => 'Tersedia', 'is_available' => true, 'type' => 'available'],
                        ['time' => '13.00', 'end_time' => '14.30', 'time_display' => '13.00', 'status' => 'Tersedia', 'is_available' => true, 'type' => 'available'],
                        ['time' => '15.30', 'end_time' => '17.00', 'time_display' => '15.30', 'status' => 'Tersedia', 'is_available' => true, 'type' => 'available'],
                    ];
                    foreach ($defaultSlots as &$ds) {
                        if ($ds['time'] === $ordStart) {
                            $ds['is_available'] = false;
                            $ds['status'] = 'Penuh';
                            $ds['type'] = 'booked';
                        }
                    }
                    unset($ds);

                    $scheduleMap[$dateStr] = [
                        'has_custom_slots' => false,
                        'is_blocked' => false,
                        'slots' => $defaultSlots,
                    ];
                }
            }
        }

        return $scheduleMap;
    }

    /**
     * Dapatkan daftar slot untuk tanggal tertentu
     */
    private function getSlotsForDate(string $dateStr, array $scheduleMap): array
    {
        if (isset($scheduleMap[$dateStr]) && ! empty($scheduleMap[$dateStr]['slots'])) {
            return $scheduleMap[$dateStr]['slots'];
        }

        // Default studio slots
        return [
            ['time' => '09.00', 'end_time' => '10.30', 'time_display' => '09.00', 'status' => 'Tersedia', 'is_available' => true, 'type' => 'available'],
            ['time' => '13.00', 'end_time' => '14.30', 'time_display' => '13.00', 'status' => 'Tersedia', 'is_available' => true, 'type' => 'available'],
            ['time' => '15.30', 'end_time' => '17.00', 'time_display' => '15.30', 'status' => 'Tersedia', 'is_available' => true, 'type' => 'available'],
        ];
    }

    /**
     * Tampilkan halaman formulir pemesanan publik
     */
    public function create(Request $request)
    {
        $dbPackages = Package::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

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
                'location_note' => $pkg->tagline ?: ($locationNotes[$pkg->slug] ?? 'Satu lokasi pilihan Anda.'),
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

        // Ambil peta slot & ketersediaan studio
        $scheduleMap = $this->getScheduleMap();

        // Cari tanggal default terdekat yang masih memiliki slot tersedia
        $defaultSelectedDate = $today->copy()->addDay();
        for ($i = 1; $i <= 14; $i++) {
            $candDate = $today->copy()->addDays($i);
            $candDateStr = $candDate->format('Y-m-d');
            $slotsForCand = $this->getSlotsForDate($candDateStr, $scheduleMap);
            $hasAvail = collect($slotsForCand)->contains('is_available', true);
            if ($hasAvail) {
                $defaultSelectedDate = $candDate;
                break;
            }
        }

        $initialSlots = $this->getSlotsForDate($defaultSelectedDate->format('Y-m-d'), $scheduleMap);
        $firstAvailableSlot = collect($initialSlots)->firstWhere('is_available', true);
        $initialSelectedSlot = $firstAvailableSlot ? $firstAvailableSlot['time'] : ($initialSlots[0]['time'] ?? '09.00');

        return view('pemesanan', compact(
            'packages',
            'selectedPackageId',
            'bookingMonth',
            'startOfMonth',
            'daysInMonth',
            'dayOfWeekOffset',
            'today',
            'defaultSelectedDate',
            'scheduleMap',
            'initialSlots',
            'initialSelectedSlot'
        ));
    }

    /**
     * Proses penyimpanan pemesanan baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|string',
            'session_date' => 'required|date',
            'session_time' => 'required|string',
            'nama_lengkap' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|min:8|max:50',
            'lokasi' => 'required|string|min:3|max:255',
            'catatan' => 'nullable|string|max:1000',
            'persetujuan' => 'required|accepted',
        ], [
            'package_id.required' => 'Silakan pilih paket foto terlebih dahulu.',
            'session_date.required' => 'Tanggal sesi foto wajib dipilih.',
            'session_date.date' => 'Format tanggal sesi tidak valid.',
            'session_time.required' => 'Waktu / slot sesi wajib dipilih.',
            'nama_lengkap.required' => 'Nama lengkap pemesan wajib diisi.',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'email.email' => 'Format alamat email tidak valid (contoh: nama@email.com).',
            'whatsapp.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'whatsapp.min' => 'Nomor WhatsApp minimal 8 digit.',
            'lokasi.required' => 'Lokasi sesi foto wajib diisi.',
            'lokasi.min' => 'Lokasi sesi minimal 3 karakter.',
            'persetujuan.required' => 'Persetujuan Syarat & Ketentuan wajib dicentang.',
            'persetujuan.accepted' => 'Anda harus mencentang persetujuan Kebijakan Privasi dan Syarat Pemesanan sebelum melanjutkan.',
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

        $defaultDurations = [
            'birthday' => 60,
            'graduation' => 60,
            'family-session' => 90,
            'prewedding' => 180,
        ];
        $fallbackDuration = $defaultDurations[$packageSlug] ?? 90;
        $durationMinutes = $package ? ($package->duration_minutes ?: $fallbackDuration) : $fallbackDuration;
        $sessionTimeInput = trim($validated['session_time'] ?? '15.30');

        if (preg_match('/^(\d{1,2}[:.]\d{2})\s*[–-]\s*(\d{1,2}[:.]\d{2})/u', $sessionTimeInput, $matches)) {
            $startTime = str_replace(':', '.', $matches[1]);
            $endTime = str_replace(':', '.', $matches[2]);
            $rawTimeSlot = "{$startTime}–{$endTime} WIB · {$durationMinutes} menit";
        } else {
            $startTime = str_replace(':', '.', $sessionTimeInput);
            if (preg_match('/^(\d{1,2})[:.](\d{2})$/', $startTime, $timeParts)) {
                $startHour = (int) $timeParts[1];
                $startMin = (int) $timeParts[2];
                $totalMinutes = ($startHour * 60) + $startMin + $durationMinutes;
                $endHour = floor($totalMinutes / 60) % 24;
                $endMin = $totalMinutes % 60;
                $endTime = sprintf('%02d.%02d', $endHour, $endMin);
                $rawTimeSlot = "{$startTime}–{$endTime} WIB · {$durationMinutes} menit";
            } else {
                $endTime = '17.00';
                $rawTimeSlot = "{$startTime}–{$endTime} WIB · {$durationMinutes} menit";
            }
        }

        $startSql = (strlen($startTime) <= 5 ? str_replace('.', ':', $startTime).':00' : '15:30:00');
        $endSql = (strlen($endTime) <= 5 ? str_replace('.', ':', $endTime).':00' : '17:00:00');

        // Pastikan slot tidak sedang diblokir oleh admin
        $isBlockedSlot = StudioSlot::whereDate('date', $sessionDateSql)
            ->where('start_time', 'like', substr($startSql, 0, 5).'%')
            ->where('type', 'blocked')
            ->exists();

        if ($isBlockedSlot) {
            return back()->withErrors([
                'session_time' => 'Slot waktu yang Anda pilih sedang diblokir oleh studio. Silakan pilih jam atau tanggal lain.',
            ])->withInput();
        }

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

            // Sinkronkan ke StudioSlot agar langsung terdaftar di kalender admin
            $startSql = (strlen($startTime) <= 5 ? str_replace('.', ':', $startTime).':00' : '15:30:00');
            $endSql = (strlen($endTime) <= 5 ? str_replace('.', ':', $endTime).':00' : '17:00:00');

            $matchingSlot = StudioSlot::whereDate('date', $sessionDateSql)
                ->where('start_time', 'like', substr($startSql, 0, 5).'%')
                ->first();

            if ($matchingSlot) {
                $matchingSlot->update([
                    'type' => 'booked',
                    'order_id' => $order->id,
                    'note' => $packageName.' - '.$customer->name,
                ]);
            } else {
                StudioSlot::create([
                    'date' => $sessionDateSql,
                    'start_time' => $startSql,
                    'end_time' => $endSql,
                    'type' => 'booked',
                    'order_id' => $order->id,
                    'note' => $packageName.' - '.$customer->name,
                ]);
            }
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

        if (! $order) {
            $sessionBooking = session('last_booking');
            if ($sessionBooking && isset($sessionBooking['order_code'])) {
                $order = Order::with('package')->where('order_code', $sessionBooking['order_code'])->first();
            }
        }

        if (! $order) {
            $order = Order::with('package')->latest()->first();
        }

        $data = null;
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
                'payment_status' => $order->payment_status,
                'internal_notes' => $order->internal_notes,
            ];
        } else {
            $data = session('last_booking', [
                'order_code' => 'CH-2026-001',
                'invoice_number' => 'INV-CH-2026-001',
                'package_name' => 'Cerita Keluarga',
                'package_price' => 'Rp2.450.000',
                'nama_lengkap' => 'Pelanggan',
                'payment_status' => 'Belum dibayar',
                'internal_notes' => null,
            ]);
        }

        $metode = $request->query('metode', 'transfer');
        $isSubmitted = $request->query('status') === 'success' || session()->has('submitted_payment');

        // Deteksi apakah pesanan berstatus Perlu Perbaikan Bukti
        $isNeedsRevision = false;
        $revisionNote = null;
        $previousPayment = null;

        if ($order) {
            $latestPayment = $order->payments()->latest('id')->first();
            $payStatusLower = strtolower($order->payment_status ?? '');
            $payRecStatusLower = $latestPayment ? strtolower($latestPayment->status ?? '') : '';

            if (in_array($payStatusLower, ['perlu perbaikan', 'perlu-perbaikan']) || in_array($payRecStatusLower, ['perlu perbaikan', 'perlu-perbaikan'])) {
                $isNeedsRevision = true;
                $revisionNote = $order->internal_notes ?: ($latestPayment ? $latestPayment->internal_review_notes : 'Bukti transfer tidak terbaca jelas atau nominal tidak sesuai. Mohon unggah kembali bukti transfer yang valid.');
                $previousPayment = $latestPayment;

                // Jika sedang dalam status perlu perbaikan, jangan kunci form sebagai submitted
                if ($request->query('status') !== 'success') {
                    $isSubmitted = false;
                    session()->forget('submitted_payment');
                }
            }
        }

        $submittedPayment = session('submitted_payment');
        if (! $submittedPayment && $isSubmitted) {
            $latestPayment = $order ? $order->payments()->latest('id')->first() : Payment::latest('id')->first();
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

        return view('konfirmasi-pembayaran', compact('data', 'metode', 'isSubmitted', 'submittedPayment', 'isNeedsRevision', 'revisionNote', 'previousPayment'));
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

        // Cek apakah ini pengiriman ulang bukti perbaikan
        $isRevision = false;
        if ($order) {
            $payStatusLower = strtolower($order->payment_status ?? '');
            $latestPay = $order->payments()->latest('id')->first();
            $latestPayStatusLower = $latestPay ? strtolower($latestPay->status ?? '') : '';
            if (in_array($payStatusLower, ['perlu perbaikan', 'perlu-perbaikan']) || in_array($latestPayStatusLower, ['perlu perbaikan', 'perlu-perbaikan'])) {
                $isRevision = true;
            }
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
                'internal_notes' => $isRevision ? 'Bukti perbaikan telah diunggah oleh pelanggan pada '.now()->format('d M Y H:i').'. Menunggu pemeriksaan ulang studio.' : $order->internal_notes,
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

        $successMessage = $isRevision
            ? 'Bukti perbaikan pembayaran berhasil dikirimkan ke studio. Tim naro capture akan segera meninjau kembali.'
            : 'Bukti Anda sudah diterima oleh studio. Pantau hasil pemeriksaan melalui status pesanan.';

        return redirect()->route('konfirmasi', [
            'order' => $order ? $order->order_code : '',
            'status' => 'success',
        ])->with('success', $successMessage);
    }
}
