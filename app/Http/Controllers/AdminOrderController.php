<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminOrderController extends Controller
{
    /**
     * Daftar Pesanan Admin dengan Filter Tab
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'semua');
        $search = trim($request->query('search', ''));

        $query = Order::with(['customer', 'package'])->latest();

        if ($activeTab === 'permintaan') {
            $query->where('session_status', 'Permintaan');
        } elseif ($activeTab === 'dikonfirmasi') {
            $query->where('session_status', 'Dikonfirmasi');
        } elseif ($activeTab === 'selesai') {
            $query->where('session_status', 'Selesai');
        } elseif ($activeTab === 'arsip') {
            $query->where('session_status', 'Arsip');
        } elseif ($activeTab === 'draft') {
            $query->where('session_status', 'Draft');
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('package_name', 'like', "%{$search}%");
            });
        }

        $dbOrders = $query->get();

        $orders = $dbOrders->map(function ($order) {
            $dateStr = $order->session_date ? $order->session_date->format('d M Y') : '-';
            $timeShort = ! empty($order->session_time_slot) ? (explode(' ', $order->session_time_slot)[0] ?? '-') : '-';

            return [
                'id' => $order->id,
                'code' => $order->order_code,
                'customer' => $order->customer_name,
                'package' => $order->package_name,
                'value' => 'Rp'.number_format($order->total_price, 0, ',', '.'),
                'date' => $dateStr,
                'time' => $timeShort,
                'session_status' => $order->session_status,
                'payment_status' => $order->payment_status ?: 'Belum dibayar',
            ];
        })->toArray();

        // Hitung metrik dinamis
        $now = Carbon::now();
        $ordersThisMonthCount = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        if ($ordersThisMonthCount === 0) {
            $ordersThisMonthCount = Order::count();
        }

        $permintaanCount = Order::where('session_status', 'Permintaan')->count();
        $dikonfirmasiCount = Order::where('session_status', 'Dikonfirmasi')->count();
        $selesaiCount = Order::where('session_status', 'Selesai')->count();
        $arsipCount = Order::where('session_status', 'Arsip')->count();
        $totalOrdersCount = Order::count();

        return view('admin.pesanan', compact(
            'orders',
            'activeTab',
            'ordersThisMonthCount',
            'permintaanCount',
            'dikonfirmasiCount',
            'selesaiCount',
            'arsipCount',
            'totalOrdersCount'
        ));
    }

    /**
     * Detail Pesanan Admin
     */
    public function show($id)
    {
        $orderModel = Order::with(['customer', 'package', 'payments'])->where('order_code', $id)
            ->orWhere('id', $id)
            ->first();

        if ($orderModel) {
            $dateFormatted = $orderModel->session_date ? $orderModel->session_date->format('l, d F Y') : '-';
            $createdDate = $orderModel->created_at ? $orderModel->created_at->format('d F Y') : '-';
            $createdDateShort = $orderModel->created_at ? $orderModel->created_at->format('d M') : date('d M');
            $customerCode = $orderModel->customer ? $orderModel->customer->customer_code : 'PLG-'.str_pad($orderModel->id, 3, '0', STR_PAD_LEFT);

            $features = ($orderModel->package && is_array($orderModel->package->features))
                ? $orderModel->package->features
                : [
                    'Sesi terarah dengan panduan pose natural',
                    'Akses galeri digital studio terkurasi',
                    'Izin cetak berlisensi pribadi',
                ];

            $paymentSummary = match ($orderModel->payment_status) {
                'Terverifikasi' => 'Terverifikasi · Lunas',
                'Bukti terkirim' => 'Bukti terkirim · Belum terverifikasi',
                'Perlu perbaikan' => 'Perlu perbaikan bukti transfer',
                default => 'Belum dibayar'
            };

            $latestPayment = $orderModel->payments()->latest('id')->first();
            $hasPayment = (bool) $latestPayment && ($orderModel->payment_status !== 'Belum dibayar' || ! empty($latestPayment->proof_file));

            $paymentSubmittedDate = '-';
            $submittedNominal = null;
            $hasRealFile = false;
            $proofFileUrl = null;
            $proofFileName = null;
            $isImage = false;
            $bankSource = null;
            $senderName = null;
            $senderNote = null;

            if ($latestPayment) {
                $paymentSubmittedDate = $latestPayment->created_at ? $latestPayment->created_at->format('d M') : ($latestPayment->transfer_date ? $latestPayment->transfer_date->format('d M') : date('d M'));
                $submittedNominal = 'Rp'.number_format($latestPayment->nominal, 0, ',', '.');
                $bankSource = $latestPayment->bank_source ?? 'Transfer Bank';
                $senderName = $latestPayment->sender_name ?? $orderModel->customer_name;
                $senderNote = $latestPayment->sender_notes;

                if (! empty($latestPayment->proof_file)) {
                    $hasRealFile = true;
                    $cleanProof = ltrim($latestPayment->proof_file, '/');
                    if (str_starts_with($cleanProof, 'storage/')) {
                        $cleanProof = substr($cleanProof, 8);
                    }

                    if (filter_var($latestPayment->proof_file, FILTER_VALIDATE_URL)) {
                        $proofFileUrl = $latestPayment->proof_file;
                    } else {
                        $proofFileUrl = route('admin.pembayaran.view-file', ['id' => $latestPayment->payment_code ?: $latestPayment->id]);
                    }

                    $proofFileName = $latestPayment->file_original_name ?: basename($latestPayment->proof_file);
                    $ext = strtolower($latestPayment->file_extension ?: pathinfo($proofFileName, PATHINFO_EXTENSION));
                    if ($ext === 'none' || empty($ext)) {
                        $ext = strtolower(pathinfo($proofFileName, PATHINFO_EXTENSION));
                    }
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                    $isPdf = ($ext === 'pdf') || str_ends_with(strtolower($cleanProof), '.pdf');
                }
            }

            // Tentukan gambar paket yang sesuai
            $packageImage = 'paket-family.jpg';
            if ($orderModel->package && ! empty($orderModel->package->image)) {
                $packageImage = $orderModel->package->image;
            } else {
                $pkgNameLower = strtolower($orderModel->package_name ?? '');
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

            $order = [
                'id' => $orderModel->id,
                'code' => $orderModel->order_code,
                'customer' => $orderModel->customer_name,
                'customer_code' => $customerCode,
                'email' => $orderModel->customer_email,
                'phone' => $orderModel->customer_whatsapp,
                'created_date' => $createdDate,
                'created_date_short' => $createdDateShort,
                'package_name' => $orderModel->package_name,
                'package_image' => $packageImage,
                'package_desc' => $orderModel->package ? $orderModel->package->description : 'Sesi foto berkesan.',
                'package_price' => 'Rp'.number_format($orderModel->total_price, 0, ',', '.'),
                'date_formatted' => $dateFormatted,
                'time_formatted' => $orderModel->session_time_slot,
                'location' => $orderModel->location,
                'features' => $features,
                'customer_note' => $orderModel->customer_notes ?? 'Tidak ada catatan tambahan dari pemesan.',
                'internal_note' => $orderModel->internal_notes ?? 'Siapkan panggilan perencanaan pra-sesi.',
                'invoice_number' => $orderModel->invoice_number,
                'payment_summary' => $paymentSummary,
                'payment_status_badge' => $orderModel->payment_status ?: 'Belum dibayar',
                'session_status' => $orderModel->session_status,
                'has_payment' => $hasPayment,
                'has_proof_file' => $hasRealFile,
                'proof_file_url' => $proofFileUrl,
                'proof_file_name' => $proofFileName,
                'proof_is_image' => $isImage,
                'proof_is_pdf' => $isPdf ?? false,
                'bank_source' => $bankSource,
                'sender_name' => $senderName,
                'sender_note' => $senderNote,
                'payment_status' => $orderModel->payment_status ?: 'Belum dibayar',
                'payment_verified_at' => $latestPayment && $latestPayment->verified_at ? $latestPayment->verified_at->translatedFormat('d M Y, H.i \W\I\B') : null,
                'payment_code' => $latestPayment ? $latestPayment->payment_code : null,
                'submitted_nominal' => $submittedNominal,
                'submitted_date' => $latestPayment && $latestPayment->transfer_date ? $latestPayment->transfer_date->format('d M Y') : null,
                'payment_submitted_date' => $paymentSubmittedDate,
            ];

            // Galeri Foto Terkait
            $galleryModel = $orderModel->gallery;
            $galleryData = null;
            if ($galleryModel) {
                $galleryData = [
                    'id' => $galleryModel->id,
                    'title' => $galleryModel->title,
                    'slug' => $galleryModel->slug,
                    'status' => $galleryModel->status,
                    'access_type' => $galleryModel->access_type,
                    'pin_code' => $galleryModel->pin_code,
                    'photos_count' => $galleryModel->photos->count(),
                    'target_photos' => $galleryModel->target_photos,
                    'url' => route('galeri-klien', ['slug' => $galleryModel->slug]),
                ];
            }
            $order['gallery'] = $galleryData;
        } else {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        return view('admin.pesanan-detail', compact('order'));
    }

    /**
     * Tandai sesi pemotretan telah selesai dilaksanakan
     */
    public function selesaikanSesi($id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $order->update(['session_status' => 'Selesai']);

        return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
            ->with('success', 'Sesi foto pesanan '.$order->order_code.' telah ditandai Selesai. Silakan lanjutkan kurasi & unggah foto ke galeri.');
    }

    /**
     * Konfirmasi sesi pesanan
     */
    public function konfirmasiSesi($id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();

        if (! in_array($order->payment_status, ['Terverifikasi', 'Lunas'])) {
            return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
                ->with('error', 'Sesi belum dapat dikonfirmasi karena pembayaran belum terverifikasi atau lunas.');
        }

        $order->update(['session_status' => 'Dikonfirmasi']);

        return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
            ->with('success', 'Sesi pesanan '.$order->order_code.' telah berhasil dikonfirmasi.');
    }

    /**
     * Verifikasi Pembayaran langsung dari Halaman Detail Pesanan
     */
    public function verifikasiPembayaran(Request $request, $id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $payment = $order->payments()->latest('id')->first();

        if (! $payment) {
            Payment::create([
                'payment_code' => 'BYR-'.$order->order_code,
                'order_id' => $order->id,
                'sender_name' => $order->customer_name,
                'transfer_date' => now()->toDateString(),
                'bank_source' => 'Transfer Bank',
                'nominal' => $order->total_price,
                'file_extension' => 'DIRECT',
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => 'Diverifikasi langsung dari halaman Detail Pesanan.',
            ]);
        } else {
            $payment->update([
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => ($payment->internal_review_notes ? $payment->internal_review_notes."\n" : '').'Diverifikasi langsung dari detail pesanan.',
            ]);
        }

        $order->update([
            'payment_status' => 'Terverifikasi',
        ]);

        return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
            ->with('success', 'Pembayaran untuk pesanan '.$order->order_code.' berhasil diverifikasi.');
    }

    /**
     * Verifikasi Pembayaran sekaligus Konfirmasi Sesi dalam 1 Klik
     */
    public function verifikasiDanKonfirmasi(Request $request, $id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $payment = $order->payments()->latest('id')->first();

        if (! $payment) {
            Payment::create([
                'payment_code' => 'BYR-'.$order->order_code,
                'order_id' => $order->id,
                'sender_name' => $order->customer_name,
                'transfer_date' => now()->toDateString(),
                'bank_source' => 'Transfer Bank',
                'nominal' => $order->total_price,
                'file_extension' => 'DIRECT',
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => 'Diverifikasi & dikonfirmasi sekaligus dari Detail Pesanan.',
            ]);
        } else {
            $payment->update([
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => ($payment->internal_review_notes ? $payment->internal_review_notes."\n" : '').'Diverifikasi sekaligus konfirmasi jadwal.',
            ]);
        }

        $order->update([
            'payment_status' => 'Terverifikasi',
            'session_status' => 'Dikonfirmasi',
        ]);

        return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
            ->with('success', 'Pembayaran diverifikasi dan sesi pesanan '.$order->order_code.' berhasil dikonfirmasi.');
    }

    /**
     * Minta perbaikan bukti pembayaran langsung dari Halaman Detail Pesanan
     */
    public function mintaPerbaikanBukti(Request $request, $id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $payment = $order->payments()->latest('id')->first();
        $alasan = $request->input('alasan', 'Bukti transfer tidak terbaca atau kurang jelas. Harap unggah ulang.');

        if ($payment) {
            $payment->update([
                'status' => 'Perlu perbaikan',
                'internal_review_notes' => ($payment->internal_review_notes ? $payment->internal_review_notes."\n" : '').'Permintaan perbaikan: '.$alasan,
            ]);
        }

        $order->update([
            'payment_status' => 'Perlu perbaikan',
            'internal_notes' => $alasan,
        ]);

        return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
            ->with('success', 'Permintaan perbaikan bukti transfer berhasil dikirim ke pelanggan.');
    }

    /**
     * Batalkan pesanan
     */
    public function batalkanSesi(Request $request, $id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->first();
        if ($order) {
            $order->update([
                'session_status' => 'Dibatalkan',
                'internal_notes' => ($order->internal_notes ? $order->internal_notes."\n" : '').'Alasan pembatalan: '.$request->input('alasan', 'Dibatalkan oleh admin'),
            ]);
        }

        return redirect()->route('admin.pesanan.detail', ['id' => $id])
            ->with('success', 'Pesanan '.$id.' telah berhasil dibatalkan.');
    }

    /**
     * Update detail pesanan
     */
    public function update(Request $request, $id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();

        $validated = $request->validate([
            'session_date' => 'nullable|string',
            'session_time_slot' => 'nullable|string',
            'location' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        $updates = [];
        if (! empty($validated['session_date'])) {
            try {
                $updates['session_date'] = Carbon::parse($validated['session_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Biarkan jika format tidak standar
            }
        }
        if (isset($validated['session_time_slot'])) {
            $updates['session_time_slot'] = $validated['session_time_slot'];
        }
        if (isset($validated['location'])) {
            $updates['location'] = $validated['location'];
        }
        if (isset($validated['internal_notes'])) {
            $updates['internal_notes'] = $validated['internal_notes'];
        }

        $order->update($updates);

        return redirect()->route('admin.pesanan.detail', ['id' => $order->order_code])
            ->with('success', 'Detail pesanan '.$order->order_code.' berhasil diperbarui.');
    }

    /**
     * Arsipkan pesanan
     */
    public function arsipkanSesi($id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->first();
        if ($order) {
            $order->update(['session_status' => 'Diarsipkan']);
        }

        return redirect()->route('admin.pesanan')
            ->with('success', 'Pesanan '.$id.' telah diarsipkan.');
    }

    /**
     * Ubah status sesi pesanan menjadi Draft
     */
    public function draftSesi($id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();

        // Validasi: hanya status sesi selain Dikonfirmasi dan Selesai yang dapat di-draft-kan
        if (in_array($order->session_status, ['Dikonfirmasi', 'Selesai'])) {
            return redirect()->back()
                ->with('error', 'Pesanan dengan status sesi "'.$order->session_status.'" tidak dapat diubah menjadi draft. Hanya data dengan status selain Dikonfirmasi dan Selesai yang dapat di draft kan.');
        }

        $order->update(['session_status' => 'Draft']);

        // Putus relasi slot studio jika ada
        $order->studioSlots()->update(['order_id' => null, 'type' => 'available']);

        return redirect()->back()
            ->with('success', 'Status sesi pesanan '.$order->order_code.' berhasil diubah menjadi Draft.');
    }

    /**
     * Hapus pesanan permanen (misal duplikat atau pesanan keliru)
     */
    public function destroy($id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->firstOrFail();
        $code = $order->order_code;

        // Validasi: hanya data dengan status Sesi selain Dikonfirmasi dan Selesai yang dapat dihapus
        if (in_array($order->session_status, ['Dikonfirmasi', 'Selesai'])) {
            return redirect()->back()
                ->with('error', 'Pesanan dengan status sesi "'.$order->session_status.'" tidak dapat dihapus. Hanya data dengan status sesi selain Dikonfirmasi dan Selesai yang dapat dihapus.');
        }

        // Hapus relasi pembayaran dan berkas buktinya jika ada
        foreach ($order->payments as $payment) {
            if ($payment->proof_file && ! Str::startsWith($payment->proof_file, ['http://', 'https://'])) {
                Storage::disk('public')->delete($payment->proof_file);
            }
            $payment->delete();
        }

        // Putus relasi slot studio
        $order->studioSlots()->update(['order_id' => null, 'type' => 'available']);

        // Hapus galeri terkait jika ada
        if ($order->gallery) {
            $order->gallery->delete();
        }

        $order->delete();

        return redirect()->route('admin.pesanan')
            ->with('success', 'Pesanan '.$code.' berhasil dihapus dari sistem.');
    }
}
