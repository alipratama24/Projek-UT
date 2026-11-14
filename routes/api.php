<?php

use App\Models\Customer;
use App\Models\Gallery;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Models\StudioSetting;
use App\Models\StudioSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    /*
    |----------------------------------------------------------------------
    | 1. Dashboard Metrics API
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard/metrik', function () {
        $now = Carbon::now();
        $totalOrders = Order::count();
        $totalNominal = Order::sum('total_price');
        $terverifikasiNominal = Payment::where('status', 'Terverifikasi')->sum('nominal');
        $sesiMendatang = Order::where('session_date', '>=', $now->toDateString())
            ->where('session_status', '!=', 'Dibatalkan')
            ->count();
        $galeriProses = Gallery::whereIn('status', ['Penyuntingan', 'Draf'])->count();
        $buktiPending = Payment::where('status', 'Bukti terkirim')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_pesanan' => $totalOrders,
                'total_omzet' => $totalNominal,
                'omzet_terverifikasi' => $terverifikasiNominal,
                'omzet_pending' => max(0, $totalNominal - $terverifikasiNominal),
                'sesi_mendatang' => $sesiMendatang,
                'galeri_dalam_proses' => $galeriProses,
                'bukti_menunggu_review' => $buktiPending,
            ],
        ]);
    });

    /*
    |----------------------------------------------------------------------
    | 2. Pelanggan (Customer) API
    |----------------------------------------------------------------------
    */
    Route::get('/pelanggan', function () {
        $customers = Customer::with('orders')->latest()->get()->map(function ($c) {
            return [
                'id' => $c->customer_code,
                'name' => $c->name,
                'email' => $c->email,
                'whatsapp' => $c->whatsapp,
                'notes' => $c->notes,
                'history' => $c->orders->count().' pesanan',
                'status' => ucfirst($c->status),
                'created_at' => $c->created_at ? $c->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    });

    Route::post('/pelanggan', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $nextCount = Customer::count() + 1;
        $customerCode = 'PLG-'.date('Y').'-'.str_pad($nextCount, 3, '0', STR_PAD_LEFT);

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'aktif',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan baru berhasil ditambahkan melalui API.',
            'data' => [
                'id' => $customer->customer_code,
                'name' => $customer->name,
                'email' => $customer->email,
                'whatsapp' => $customer->whatsapp,
                'notes' => $customer->notes,
                'history' => '0 pesanan',
                'status' => 'Aktif',
            ],
        ], 201);
    });

    /*
    |----------------------------------------------------------------------
    | 3. Paket Layanan (Package) API
    |----------------------------------------------------------------------
    */
    Route::get('/paket', function () {
        $packages = Package::where('is_active', true)->latest()->get()->map(function ($pkg) {
            return [
                'id' => $pkg->id,
                'slug' => $pkg->slug,
                'code' => $pkg->code,
                'name' => $pkg->name,
                'tagline' => $pkg->tagline,
                'description' => $pkg->description,
                'price' => $pkg->price,
                'price_formatted' => 'Rp'.number_format($pkg->price, 0, ',', '.'),
                'duration' => $pkg->duration,
                'duration_minutes' => $pkg->duration_minutes,
                'photo_count' => $pkg->photo_count,
                'features' => is_array($pkg->features) ? $pkg->features : [],
                'image' => $pkg->image,
                'is_featured' => (bool) $pkg->is_featured,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    });

    Route::get('/paket/{id}', function ($id) {
        $pkg = Package::where('id', $id)->orWhere('slug', $id)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $pkg,
        ]);
    });

    Route::post('/paket', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required',
            'duration_minutes' => 'required|numeric',
            'photo_count' => 'required|numeric',
            'description' => 'required|string',
            'features' => 'nullable|array',
        ]);

        $cleanPrice = (int) preg_replace('/[^0-9]/', '', (string) $validated['price']);
        $slug = Str::slug($validated['name']);
        $code = 'PKG-'.strtoupper(str_replace('-', '_', $slug));

        $pkg = Package::create([
            'slug' => $slug,
            'code' => $code,
            'name' => $validated['name'],
            'tagline' => $validated['description'],
            'description' => $validated['description'],
            'price' => $cleanPrice,
            'duration' => $validated['duration_minutes'].' menit',
            'duration_minutes' => (int) $validated['duration_minutes'],
            'photo_count' => (int) $validated['photo_count'],
            'photo_count_label' => $validated['photo_count'].' foto retouched',
            'features' => $validated['features'] ?? [],
            'image' => 'paket-birthday.jpg',
            'is_active' => true,
            'is_featured' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paket layanan berhasil dibuat.',
            'data' => $pkg,
        ], 201);
    });

    Route::put('/paket/{id}', function (Request $request, $id) {
        $pkg = Package::where('id', $id)->orWhere('slug', $id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required',
            'duration_minutes' => 'sometimes|required|numeric',
            'photo_count' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'features' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['price'])) {
            $validated['price'] = (int) preg_replace('/[^0-9]/', '', (string) $validated['price']);
        }

        $pkg->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paket layanan berhasil diperbarui.',
            'data' => $pkg,
        ]);
    });

    Route::delete('/paket/{id}', function ($id) {
        $pkg = Package::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        $pkg->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paket layanan berhasil dihapus.',
        ]);
    });

    /*
    |----------------------------------------------------------------------
    | 4. Pesanan (Order) API
    |----------------------------------------------------------------------
    */
    Route::get('/pesanan', function (Request $request) {
        $tab = $request->query('status');
        $query = Order::with(['customer', 'package'])->latest();

        if ($tab) {
            $query->where('session_status', $tab);
        }

        $orders = $query->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'code' => $order->order_code,
                'invoice' => $order->invoice_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_whatsapp' => $order->customer_whatsapp,
                'package_name' => $order->package_name,
                'total_price' => $order->total_price,
                'total_price_formatted' => 'Rp'.number_format($order->total_price, 0, ',', '.'),
                'session_date' => $order->session_date ? $order->session_date->format('Y-m-d') : null,
                'session_time' => $order->session_time_slot,
                'location' => $order->location,
                'session_status' => $order->session_status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at ? $order->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    });

    Route::get('/pesanan/{id}', function ($id) {
        $order = Order::with(['customer', 'package', 'payments'])
            ->where('order_code', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    });

    Route::post('/pesanan/{id}/konfirmasi', function ($id) {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $order->update(['session_status' => 'Dikonfirmasi']);

        return response()->json([
            'success' => true,
            'message' => 'Sesi pesanan berhasil dikonfirmasi.',
            'data' => $order,
        ]);
    });

    Route::post('/pesanan/{id}/batalkan', function (Request $request, $id) {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $alasan = $request->input('alasan', 'Dibatalkan melalui API');

        $order->update([
            'session_status' => 'Dibatalkan',
            'internal_notes' => ($order->internal_notes ? $order->internal_notes."\n" : '').'Alasan pembatalan: '.$alasan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi pesanan berhasil dibatalkan.',
            'data' => $order,
        ]);
    });

    Route::post('/pesanan/{id}/arsipkan', function ($id) {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $order->update(['session_status' => 'Diarsipkan']);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diarsipkan.',
            'data' => $order,
        ]);
    });

    /*
    |----------------------------------------------------------------------
    | 5. Pembayaran (Payment) API
    |----------------------------------------------------------------------
    */
    Route::get('/pembayaran', function (Request $request) {
        $status = $request->query('status');
        $query = Payment::with('order')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $payments = $query->get()->map(function ($pay) {
            return [
                'id' => $pay->payment_code,
                'sender' => $pay->sender_name,
                'invoice' => $pay->order ? $pay->order->invoice_number : null,
                'order_code' => $pay->order ? $pay->order->order_code : null,
                'nominal' => $pay->nominal,
                'nominal_formatted' => 'Rp'.number_format($pay->nominal, 0, ',', '.'),
                'bank_source' => $pay->bank_source,
                'transfer_date' => $pay->transfer_date ? $pay->transfer_date->format('Y-m-d') : null,
                'status' => $pay->status,
                'proof_file' => $pay->proof_file ? asset('storage/'.$pay->proof_file) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    });

    Route::get('/pembayaran/{id}', function ($id) {
        $pay = Payment::with('order.package')
            ->where('payment_code', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $pay,
        ]);
    });

    Route::post('/pembayaran/{id}/verifikasi', function (Request $request, $id) {
        $pay = Payment::where('payment_code', $id)->orWhere('id', $id)->firstOrFail();
        $catatan = $request->input('catatan', 'Diverifikasi via API.');

        $pay->update([
            'status' => 'Terverifikasi',
            'verified_at' => now(),
            'internal_review_notes' => $catatan,
        ]);

        if ($pay->order) {
            $pay->order->update([
                'payment_status' => 'Terverifikasi',
                'session_status' => 'Dikonfirmasi',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diverifikasi.',
            'data' => $pay,
        ]);
    });

    Route::post('/pembayaran/{id}/minta-perbaikan', function (Request $request, $id) {
        $pay = Payment::where('payment_code', $id)->orWhere('id', $id)->firstOrFail();
        $alasan = $request->input('alasan', 'Bukti tidak terbaca jelas.');

        $pay->update([
            'status' => 'Perlu perbaikan',
            'internal_review_notes' => $alasan,
        ]);

        if ($pay->order) {
            $pay->order->update([
                'payment_status' => 'Perlu perbaikan',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran diubah menjadi Perlu perbaikan.',
            'data' => $pay,
        ]);
    });

    /*
    |----------------------------------------------------------------------
    | 6. Jadwal & Slot Studio (Studio Slot) API
    |----------------------------------------------------------------------
    */
    Route::get('/jadwal', function (Request $request) {
        $month = $request->query('bulan', Carbon::now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $month) ?: Carbon::now();

        $slots = StudioSlot::with('order')
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'bulan' => $month,
            'data' => $slots,
        ]);
    });

    Route::post('/jadwal/slot', function (Request $request) {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'type' => 'required|in:available,blocked,booked',
            'note' => 'nullable|string|max:255',
        ]);

        $slot = StudioSlot::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Slot studio berhasil ditambahkan.',
            'data' => $slot,
        ], 201);
    });

    Route::delete('/jadwal/slot/{id}', function ($id) {
        $slot = StudioSlot::findOrFail($id);
        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slot studio berhasil dihapus.',
        ]);
    });

    /*
    |----------------------------------------------------------------------
    | 7. Galeri Pelanggan (Gallery) API
    |----------------------------------------------------------------------
    */
    Route::get('/galeri', function () {
        $galleries = Gallery::with(['order', 'photos'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $galleries,
        ]);
    });

    Route::get('/galeri/{slug}', function ($slug) {
        $gallery = Gallery::with(['order', 'photos'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $gallery,
        ]);
    });

    Route::post('/galeri', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order_id' => 'nullable|exists:orders,id',
            'target_photos' => 'required|numeric|min:1',
            'access_type' => 'required|in:Privat,Terbuka',
            'pin_code' => 'nullable|string',
        ]);

        $order = null;
        if (! empty($validated['order_id'])) {
            $order = Order::find($validated['order_id']);
        }

        $gallery = Gallery::create([
            'slug' => Str::slug($validated['title']).'-'.Str::random(4),
            'title' => $validated['title'],
            'order_id' => $order ? $order->id : null,
            'client_name' => $order ? $order->customer_name : 'Klien Studio',
            'target_photos' => (int) $validated['target_photos'],
            'status' => 'Draf',
            'access_type' => $validated['access_type'],
            'pin_code' => $validated['pin_code'] ?? ($validated['access_type'] === 'Privat' ? '1234' : null),
            'cover_image' => 'work-1.jpg',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Galeri berhasil dibuat.',
            'data' => $gallery,
        ], 201);
    });

    /*
    |----------------------------------------------------------------------
    | 8. Pengaturan Studio (Settings) API
    |----------------------------------------------------------------------
    */
    Route::get('/pengaturan', function () {
        $settings = StudioSetting::pluck('value', 'key');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    });

    Route::post('/pengaturan', function (Request $request) {
        $payload = $request->all();

        foreach ($payload as $key => $val) {
            if (is_string($val) || is_numeric($val) || is_null($val)) {
                StudioSetting::set($key, (string) $val);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan studio berhasil diperbarui.',
            'data' => StudioSetting::pluck('value', 'key'),
        ]);
    });
});
