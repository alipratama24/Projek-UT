<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPaymentController extends Controller
{
    /**
     * Daftar Pembayaran Admin dengan Filter Tab
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'semua');
        $search = trim($request->query('search', ''));

        $query = Payment::with('order')->latest();

        if ($activeTab === 'bukti-terkirim') {
            $query->where('status', 'Bukti terkirim');
        } elseif ($activeTab === 'terverifikasi') {
            $query->where('status', 'Terverifikasi');
        } elseif ($activeTab === 'perlu-perbaikan') {
            $query->where('status', 'Perlu perbaikan');
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_code', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_code', 'like', "%{$search}%")
                            ->orWhere('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $dbPayments = $query->get();

        $payments = $dbPayments->map(function ($pay) {
            $receivedDate = $pay->transfer_date ? $pay->transfer_date->format('d M') : ($pay->created_at ? $pay->created_at->format('d M') : '-');
            $receivedTime = $pay->created_at ? $pay->created_at->format('H.i') : '-';

            return [
                'id' => $pay->payment_code,
                'sender' => $pay->sender_name,
                'invoice' => $pay->order ? $pay->order->invoice_number : '-',
                'order_code' => $pay->order ? $pay->order->order_code : '-',
                'nominal' => 'Rp'.number_format($pay->nominal, 0, ',', '.'),
                'received_date' => $receivedDate,
                'received_time' => $receivedTime,
                'status' => $pay->status,
            ];
        })->toArray();

        $unpaidInvoices = Order::where(function ($q) {
            $q->whereNull('payment_status')
                ->orWhere('payment_status', '!=', 'Terverifikasi');
        })->get();

        // Jika semua invoice saat ini sudah terverifikasi, tetap sertakan semua invoice untuk opsi pencatatan
        if ($unpaidInvoices->isEmpty()) {
            $unpaidInvoices = Order::latest()->get();
        }

        // Metrik Dinamis Pembayaran
        $pendingCount = Payment::where('status', 'Bukti terkirim')->count();
        $pendingNominal = Payment::where('status', 'Bukti terkirim')->sum('nominal');

        $fixCount = Payment::where('status', 'Perlu perbaikan')->count();
        $fixNominal = Payment::where('status', 'Perlu perbaikan')->sum('nominal');

        $verifiedCount = Payment::where('status', 'Terverifikasi')->count();
        $verifiedNominal = Payment::where('status', 'Terverifikasi')->sum('nominal');

        $unpaidOrders = Order::where(function ($q) {
            $q->whereNull('payment_status')
                ->orWhere('payment_status', 'Belum dibayar');
        })->get();
        $unpaidCount = $unpaidOrders->count();
        $unpaidNominal = $unpaidOrders->sum('total_price');

        // Deteksi bukti tertua yang masih pending
        $oldestPendingPayment = Payment::where('status', 'Bukti terkirim')->oldest('created_at')->first();
        $oldestPendingDate = null;
        if ($oldestPendingPayment) {
            $oldestPendingDate = $oldestPendingPayment->transfer_date
                ? $oldestPendingPayment->transfer_date->translatedFormat('d F')
                : ($oldestPendingPayment->created_at ? $oldestPendingPayment->created_at->translatedFormat('d F') : 'Hari ini');
        }

        return view('admin.pembayaran', compact(
            'payments',
            'activeTab',
            'unpaidInvoices',
            'pendingCount',
            'pendingNominal',
            'fixCount',
            'fixNominal',
            'verifiedCount',
            'verifiedNominal',
            'unpaidCount',
            'unpaidNominal',
            'oldestPendingPayment',
            'oldestPendingDate'
        ));
    }

    /**
     * Simpan Pembayaran Manual
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'metode_penerimaan' => 'required|string',
            'nominal' => 'required|string',
            'tanggal_penerimaan' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        // Bersihkan angka nominal jika ada karakter non-angka
        $cleanNominal = (int) preg_replace('/[^0-9]/', '', $validated['nominal']);
        if ($cleanNominal <= 0) {
            $cleanNominal = 1000000;
        }

        $order = Order::where('invoice_number', $validated['invoice_number'])->first();

        // Buat nomor payment_code unik
        $nextPaymentCount = Payment::count() + 1;
        $paymentCode = 'BYR-MANUAL-'.str_pad($nextPaymentCount, 3, '0', STR_PAD_LEFT);

        $senderName = $order ? $order->customer_name : 'Pembayaran Tunai Studio';

        $payment = Payment::create([
            'payment_code' => $paymentCode,
            'order_id' => $order ? $order->id : null,
            'sender_name' => $senderName,
            'transfer_date' => $validated['tanggal_penerimaan'],
            'bank_source' => $validated['metode_penerimaan'],
            'nominal' => $cleanNominal,
            'sender_notes' => $validated['catatan'] ?? 'Pembayaran manual studio naro capture',
            'file_extension' => 'MANUAL',
            'status' => 'Terverifikasi',
            'verified_at' => now(),
            'internal_review_notes' => 'Dicatat secara manual oleh admin/kasir: '.$validated['metode_penerimaan'],
        ]);

        if ($order) {
            $order->update([
                'payment_status' => 'Terverifikasi',
                'session_status' => $order->session_status === 'Menunggu pembayaran' ? 'Dikonfirmasi' : $order->session_status,
            ]);
        }

        return redirect()->route('admin.pembayaran')
            ->with('success', 'Pembayaran manual sebesar Rp'.number_format($cleanNominal, 0, ',', '.').' untuk '.$validated['invoice_number'].' berhasil disimpan dan diverifikasi.');
    }

    /**
     * Cari atau inisialisasi pembayaran berdasarkan ID pembayaran, order code, atau nomor invoice.
     *
     * @param  string|int  $id
     * @return array{0: ?Payment, 1: ?Order}
     */
    private function resolvePaymentOrOrder($id): array
    {
        // 1. Cari Payment langsung berdasarkan payment_code atau primary key
        $payment = Payment::with('order.package')
            ->where('payment_code', $id)
            ->orWhere('id', $id)
            ->first();

        if ($payment) {
            return [$payment, $payment->order];
        }

        // 2. Cari Payment lewat order_code atau invoice_number pada relasi order
        // Prioritaskan pembayaran yang memiliki lampiran berkas bukti fisik
        $payment = Payment::with('order.package')
            ->whereHas('order', function ($q) use ($id) {
                $q->where('order_code', $id)
                    ->orWhere('invoice_number', $id);
            })
            ->whereNotNull('proof_file')
            ->where('proof_file', '!=', '')
            ->latest()
            ->first()
            ?? Payment::with('order.package')
            ->whereHas('order', function ($q) use ($id) {
                $q->where('order_code', $id)
                    ->orWhere('invoice_number', $id);
            })
            ->latest()
            ->first();

        if ($payment) {
            return [$payment, $payment->order];
        }

        // 3. Jika belum ada row Payment, cari apakah $id adalah Order langsung
        $order = Order::with(['package', 'payments'])
            ->where('order_code', $id)
            ->orWhere('invoice_number', $id)
            ->orWhere('id', $id)
            ->first();

        if ($order) {
            $payment = $order->payments()->latest('id')->first();

            return [$payment, $order];
        }

        return [null, null];
    }

    /**
     * Detail Pembayaran
     */
    public function show($id)
    {
        [$paymentModel, $order] = $this->resolvePaymentOrOrder($id);

        if ($paymentModel) {
            $order = $paymentModel->order ?: $order;
            $transferDate = $paymentModel->transfer_date ? $paymentModel->transfer_date->format('d F Y') : '-';
            $submittedAt = $paymentModel->created_at ? $paymentModel->created_at->format('d M Y, H.i \W\I\B') : '-';

            $scheduleStr = '-';
            if ($order && $order->session_date) {
                $scheduleStr = $order->session_date->format('d M Y').' · '.$order->session_time_slot;
            }

            // Cek apakah berkas fisik benar-benar ada di storage / disk
            $targetPayment = $paymentModel;
            if (empty($targetPayment->proof_file) && $order) {
                $altPayment = $order->payments()->whereNotNull('proof_file')->where('proof_file', '!=', '')->latest()->first();
                if ($altPayment) {
                    $targetPayment = $altPayment;
                }
            }

            $hasRealFile = false;
            $fileUrl = null;
            $fileName = null;
            $fileMeta = null;
            $isImage = false;
            $isPdf = false;

            if (! empty($targetPayment->proof_file)) {
                $hasRealFile = true;
                $cleanProof = ltrim($targetPayment->proof_file, '/');
                if (str_starts_with($cleanProof, 'storage/')) {
                    $cleanProof = substr($cleanProof, 8);
                }

                if (filter_var($targetPayment->proof_file, FILTER_VALIDATE_URL)) {
                    $fileUrl = $targetPayment->proof_file;
                } else {
                    $fileUrl = route('admin.pembayaran.view-file', ['id' => $targetPayment->payment_code ?: $targetPayment->id]);
                }

                $fileName = $targetPayment->file_original_name ?: basename($targetPayment->proof_file);
                $ext = strtolower($targetPayment->file_extension ?: pathinfo($fileName, PATHINFO_EXTENSION));
                if ($ext === 'none' || empty($ext)) {
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                }
                $fileMeta = strtoupper($ext ?: 'BERKAS').' · Bukti Unggahan Pelanggan';
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp']);
                $isPdf = ($ext === 'pdf') || str_ends_with(strtolower($cleanProof), '.pdf');
            }

            $payment = [
                'id' => $paymentModel->payment_code,
                'sender' => $paymentModel->sender_name,
                'status_badge' => $paymentModel->status === 'Terverifikasi' ? 'Terverifikasi' : ($paymentModel->status ?: 'Bukti terkirim · Belum terverifikasi'),
                'has_file' => $hasRealFile,
                'is_image' => $isImage,
                'is_pdf' => $isPdf,
                'file_name' => $fileName,
                'file_meta' => $fileMeta,
                'file_path' => $fileUrl,
                'clean_proof' => $cleanProof ?? null,
                'storage_url' => isset($cleanProof) ? asset('storage/'.$cleanProof) : null,
                'transfer_date' => $transferDate,
                'bank_source' => $paymentModel->bank_source ?? 'Transfer Bank',
                'nominal' => 'Rp'.number_format($paymentModel->nominal, 0, ',', '.'),
                'sender_note' => $paymentModel->sender_notes ?? 'Tidak ada catatan pengirim.',
                'submitted_at' => $submittedAt,
                'invoice' => $order ? $order->invoice_number : '-',
                'order_code' => $order ? $order->order_code : '-',
                'package_name' => $order ? $order->package_name : '-',
                'session_schedule' => $scheduleStr,
                'internal_review_notes' => $paymentModel->internal_review_notes ?: ($order ? $order->internal_notes : null),
            ];
        } elseif ($order) {
            $scheduleStr = '-';
            if ($order->session_date) {
                $scheduleStr = $order->session_date->format('d M Y').' · '.$order->session_time_slot;
            }

            $orderPayment = $order->payments()->whereNotNull('proof_file')->where('proof_file', '!=', '')->latest('id')->first()
                ?: $order->payments()->latest('id')->first();

            $hasRealFile = false;
            $fileUrl = null;
            $fileName = null;
            $fileMeta = null;
            $isImage = false;
            $isPdf = false;

            if ($orderPayment && ! empty($orderPayment->proof_file)) {
                $hasRealFile = true;
                $cleanProof = ltrim($orderPayment->proof_file, '/');
                if (str_starts_with($cleanProof, 'storage/')) {
                    $cleanProof = substr($cleanProof, 8);
                }

                if (filter_var($orderPayment->proof_file, FILTER_VALIDATE_URL)) {
                    $fileUrl = $orderPayment->proof_file;
                } else {
                    $fileUrl = route('admin.pembayaran.view-file', ['id' => $orderPayment->payment_code ?: $order->order_code]);
                }

                $fileName = $orderPayment->file_original_name ?: basename($orderPayment->proof_file);
                $ext = strtolower($orderPayment->file_extension ?: pathinfo($fileName, PATHINFO_EXTENSION));
                if ($ext === 'none' || empty($ext)) {
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                }
                $fileMeta = strtoupper($ext ?: 'BERKAS').' · Bukti Unggahan Pelanggan';
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp']);
                $isPdf = ($ext === 'pdf') || str_ends_with(strtolower($cleanProof), '.pdf');
            }

            $payment = [
                'id' => $order->order_code,
                'sender' => $order->customer_name,
                'status_badge' => $order->payment_status ?: 'Belum dibayar',
                'has_file' => $hasRealFile,
                'is_image' => $isImage,
                'is_pdf' => $isPdf,
                'file_name' => $fileName,
                'file_meta' => $fileMeta,
                'file_path' => $fileUrl,
                'clean_proof' => $cleanProof ?? null,
                'storage_url' => isset($cleanProof) ? asset('storage/'.$cleanProof) : null,
                'transfer_date' => $order->created_at ? $order->created_at->format('d F Y') : date('d F Y'),
                'bank_source' => $orderPayment ? ($orderPayment->bank_source ?? 'Transfer Bank') : 'Menunggu Pembayaran',
                'nominal' => 'Rp'.number_format($orderPayment ? $orderPayment->nominal : $order->total_price, 0, ',', '.'),
                'sender_note' => $orderPayment ? ($orderPayment->sender_notes ?? $order->customer_notes) : ($order->customer_notes ?? 'Belum ada catatan transfer dari pelanggan.'),
                'submitted_at' => $order->created_at ? $order->created_at->format('d M Y, H.i \W\I\B') : date('d M Y, H.i \W\I\B'),
                'invoice' => $order->invoice_number,
                'order_code' => $order->order_code,
                'package_name' => $order->package_name,
                'session_schedule' => $scheduleStr,
                'internal_review_notes' => $order->internal_notes,
            ];
        } else {
            abort(404, 'Data pembayaran atau pesanan tidak ditemukan.');
        }

        return view('admin.pembayaran-detail', compact('payment'));
    }

    /**
     * Verifikasi Pembayaran
     */
    public function verifikasi(Request $request, $id)
    {
        [$payment, $order] = $this->resolvePaymentOrOrder($id);

        if (! $payment && ! $order) {
            abort(404, 'Data pembayaran atau pesanan tidak ditemukan.');
        }

        $note = $request->input('catatan', 'Telah diverifikasi sesuai mutasi rekening studio.');

        if ($payment) {
            $payment->update([
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => $note,
            ]);

            if ($payment->order) {
                $payment->order->update([
                    'payment_status' => 'Terverifikasi',
                    'session_status' => in_array($payment->order->session_status, ['Menunggu pembayaran', 'Permintaan']) ? 'Dikonfirmasi' : $payment->order->session_status,
                    'internal_notes' => $note,
                ]);
            }
        } elseif ($order) {
            $payment = Payment::create([
                'payment_code' => 'BYR-'.$order->order_code,
                'order_id' => $order->id,
                'sender_name' => $order->customer_name,
                'transfer_date' => now()->toDateString(),
                'bank_source' => 'Manual Studio',
                'nominal' => $order->total_price,
                'sender_notes' => $order->customer_notes,
                'proof_file' => null,
                'file_original_name' => null,
                'file_extension' => 'NONE',
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => $note,
            ]);

            $order->update([
                'payment_status' => 'Terverifikasi',
                'session_status' => in_array($order->session_status, ['Menunggu pembayaran', 'Permintaan']) ? 'Dikonfirmasi' : $order->session_status,
                'internal_notes' => $note,
            ]);
        }

        return redirect()->route('admin.pembayaran.detail', ['id' => $id])
            ->with('success', 'Pembayaran '.$id.' telah berhasil diverifikasi.');
    }

    /**
     * Minta Perbaikan Bukti
     */
    public function mintaPerbaikan(Request $request, $id)
    {
        [$payment, $order] = $this->resolvePaymentOrOrder($id);

        if (! $payment && ! $order) {
            abort(404, 'Data pembayaran atau pesanan tidak ditemukan.');
        }

        $alasan = $request->input('alasan', 'Bukti transfer tidak terbaca atau nominal tidak sesuai.');
        $instruksi = $request->input('pesan_instruksi');
        $fullNote = $instruksi ? ($alasan.' — Instruksi: '.$instruksi) : $alasan;

        if ($payment) {
            $payment->update([
                'status' => 'Perlu perbaikan',
                'internal_review_notes' => $fullNote,
            ]);

            if ($payment->order) {
                $payment->order->update([
                    'payment_status' => 'Perlu perbaikan',
                    'internal_notes' => $fullNote,
                ]);
            }
        } elseif ($order) {
            $payment = Payment::create([
                'payment_code' => 'BYR-'.$order->order_code,
                'order_id' => $order->id,
                'sender_name' => $order->customer_name,
                'transfer_date' => now()->toDateString(),
                'bank_source' => 'Transfer Bank',
                'nominal' => $order->total_price,
                'sender_notes' => $order->customer_notes,
                'proof_file' => null,
                'file_original_name' => null,
                'file_extension' => 'NONE',
                'status' => 'Perlu perbaikan',
                'internal_review_notes' => $fullNote,
            ]);

            $order->update([
                'payment_status' => 'Perlu perbaikan',
                'internal_notes' => $fullNote,
            ]);
        }

        return redirect()->route('admin.pembayaran.detail', ['id' => $id])
            ->with('success', 'Status perbaikan telah dikirimkan untuk pembayaran '.$id.'.');
    }

    /**
     * Update Catatan Internal Pembayaran
     */
    public function updateCatatan(Request $request, $id)
    {
        [$payment, $order] = $this->resolvePaymentOrOrder($id);

        if (! $payment && ! $order) {
            abort(404, 'Data pembayaran atau pesanan tidak ditemukan.');
        }

        $note = $request->input('catatan');

        if ($payment) {
            $payment->update([
                'internal_review_notes' => $note,
            ]);

            if ($payment->order) {
                $payment->order->update([
                    'internal_notes' => $note,
                ]);
            }
        } elseif ($order) {
            $payment = Payment::create([
                'payment_code' => 'BYR-'.$order->order_code,
                'order_id' => $order->id,
                'sender_name' => $order->customer_name,
                'transfer_date' => now()->toDateString(),
                'bank_source' => 'Menunggu Pembayaran',
                'nominal' => $order->total_price,
                'sender_notes' => $order->customer_notes,
                'proof_file' => null,
                'file_original_name' => null,
                'file_extension' => 'NONE',
                'status' => $order->payment_status ?: 'Belum dibayar',
                'internal_review_notes' => $note,
            ]);

            $order->update([
                'internal_notes' => $note,
            ]);
        }

        return redirect()->route('admin.pembayaran.detail', ['id' => $id])
            ->with('success', 'Catatan internal pembayaran '.$id.' berhasil diperbarui.');
    }

    /**
     * Tampilkan atau streaming langsung berkas bukti fisik pembayaran
     */
    public function viewFile($id)
    {
        [$payment, $order] = $this->resolvePaymentOrOrder($id);
        $targetPayment = $payment;
        if ((! $targetPayment || empty($targetPayment->proof_file)) && $order) {
            $targetPayment = $order->payments()->whereNotNull('proof_file')->where('proof_file', '!=', '')->latest()->first();
        }

        if (! $targetPayment || empty($targetPayment->proof_file)) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        $proof = $targetPayment->proof_file;
        $cleanProof = ltrim($proof, '/');
        if (str_starts_with($cleanProof, 'storage/')) {
            $cleanProof = substr($cleanProof, 8);
        }

        $fullPath = null;
        if (Storage::disk('public')->exists($cleanProof)) {
            $fullPath = Storage::disk('public')->path($cleanProof);
        } elseif (file_exists(public_path('images/'.$proof))) {
            $fullPath = public_path('images/'.$proof);
        } elseif (file_exists(public_path('storage/'.$cleanProof))) {
            $fullPath = public_path('storage/'.$cleanProof);
        } elseif (file_exists(storage_path('app/public/'.$cleanProof))) {
            $fullPath = storage_path('app/public/'.$cleanProof);
        }

        if (! $fullPath || ! file_exists($fullPath)) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        return response()->file($fullPath, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
