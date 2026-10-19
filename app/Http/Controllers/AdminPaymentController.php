<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    /**
     * Daftar Pembayaran Admin dengan Filter Tab
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'semua');

        $query = Payment::with('order')->latest();

        if ($activeTab === 'bukti-terkirim') {
            $query->where('status', 'Bukti terkirim');
        } elseif ($activeTab === 'terverifikasi') {
            $query->where('status', 'Terverifikasi');
        } elseif ($activeTab === 'perlu-perbaikan') {
            $query->where('status', 'Perlu perbaikan');
        }

        $dbPayments = $query->get();

        $payments = $dbPayments->map(function ($pay) {
            $receivedDate = $pay->transfer_date ? $pay->transfer_date->format('d M') : '5 Okt';
            $receivedTime = $pay->created_at ? $pay->created_at->format('H.i') : '16.10';

            return [
                'id' => $pay->payment_code,
                'sender' => $pay->sender_name,
                'invoice' => $pay->order ? $pay->order->invoice_number : 'INV-CH-2026-001',
                'order_code' => $pay->order ? $pay->order->order_code : 'CH-CONTOH-001',
                'nominal' => 'Rp' . number_format($pay->nominal, 0, ',', '.'),
                'received_date' => $receivedDate,
                'received_time' => $receivedTime,
                'status' => $pay->status,
            ];
        })->toArray();

        return view('admin.pembayaran', compact('payments', 'activeTab'));
    }

    /**
     * Detail Pembayaran
     */
    public function show($id)
    {
        $paymentModel = Payment::with('order.package')
            ->where('payment_code', $id)
            ->orWhere('id', $id)
            ->first();

        if ($paymentModel) {
            $order = $paymentModel->order;
            $transferDate = $paymentModel->transfer_date ? $paymentModel->transfer_date->format('d F Y') : '5 Oktober 2026';
            $submittedAt = $paymentModel->created_at ? $paymentModel->created_at->format('d M Y, H.i \W\I\B') : '5 Okt 2026, 16.10 WIB';

            $scheduleStr = '17 Okt 2026 · 15.30–17.00 WIB';
            if ($order && $order->session_date) {
                $scheduleStr = $order->session_date->format('d M Y') . ' · ' . $order->session_time_slot;
            }

            $payment = [
                'id' => $paymentModel->payment_code,
                'sender' => $paymentModel->sender_name,
                'status_badge' => $paymentModel->status === 'Terverifikasi' ? 'Terverifikasi' : 'Bukti terkirim · Belum terverifikasi',
                'file_name' => $paymentModel->file_original_name ?? 'bukti-transfer-contoh.pdf',
                'file_meta' => ($paymentModel->file_extension ?? 'PDF') . ' · Bukti Unggahan',
                'file_path' => $paymentModel->proof_file ? asset('storage/' . $paymentModel->proof_file) : null,
                'transfer_date' => $transferDate,
                'bank_source' => $paymentModel->bank_source ?? 'Bank BCA',
                'nominal' => 'Rp' . number_format($paymentModel->nominal, 0, ',', '.'),
                'sender_note' => $paymentModel->sender_notes ?? 'Pembayaran sesi studio Clara Hayes.',
                'submitted_at' => $submittedAt,
                'invoice' => $order ? $order->invoice_number : 'INV-CH-2026-001',
                'order_code' => $order ? $order->order_code : 'CH-CONTOH-001',
                'package_name' => $order ? $order->package_name : 'Cerita Keluarga',
                'session_schedule' => $scheduleStr,
            ];
        } else {
            $payment = [
                'id' => $id ?: 'BYR-CONTOH-001',
                'sender' => 'Ayu Pramesti',
                'status_badge' => 'Bukti terkirim · Belum terverifikasi',
                'file_name' => 'bukti-transfer-contoh.pdf',
                'file_meta' => 'PDF · 1 halaman',
                'file_path' => null,
                'transfer_date' => '5 Oktober 2026',
                'bank_source' => 'Bank BCA',
                'nominal' => 'Rp9.500.000',
                'sender_note' => 'Pembayaran sesi Cerita Keluarga 17 Oktober.',
                'submitted_at' => '5 Okt 2026, 16.10 WIB',
                'invoice' => 'INV-CH-2026-001',
                'order_code' => 'CH-CONTOH-001',
                'package_name' => 'Cerita Keluarga',
                'session_schedule' => '17 Okt 2026 · 15.30–17.00 WIB',
            ];
        }

        return view('admin.pembayaran-detail', compact('payment'));
    }

    /**
     * Verifikasi Pembayaran
     */
    public function verifikasi(Request $request, $id)
    {
        $payment = Payment::where('payment_code', $id)->orWhere('id', $id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'Terverifikasi',
                'verified_at' => now(),
                'internal_review_notes' => $request->input('catatan', 'Telah diverifikasi sesuai mutasi rekening.'),
            ]);

            if ($payment->order) {
                $payment->order->update([
                    'payment_status' => 'Terverifikasi',
                    'session_status' => 'Dikonfirmasi',
                ]);
            }
        }

        return redirect()->route('admin.pembayaran')
            ->with('success', 'Pembayaran ' . $id . ' telah berhasil diverifikasi.');
    }

    /**
     * Minta Perbaikan Bukti
     */
    public function mintaPerbaikan(Request $request, $id)
    {
        $payment = Payment::where('payment_code', $id)->orWhere('id', $id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'Perlu perbaikan',
                'internal_review_notes' => $request->input('alasan', 'Bukti transfer tidak terbaca atau nominal tidak sesuai.'),
            ]);

            if ($payment->order) {
                $payment->order->update([
                    'payment_status' => 'Perlu perbaikan',
                ]);
            }
        }

        return redirect()->route('admin.pembayaran.detail', ['id' => $id])
            ->with('success', 'Status perbaikan telah dikirimkan untuk pembayaran ' . $id . '.');
    }
}
