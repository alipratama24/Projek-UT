<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Daftar Pesanan Admin dengan Filter Tab
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'semua');

        $query = Order::with(['customer', 'package'])->latest();

        if ($activeTab === 'permintaan') {
            $query->where('session_status', 'Permintaan');
        } elseif ($activeTab === 'dikonfirmasi') {
            $query->where('session_status', 'Dikonfirmasi');
        } elseif ($activeTab === 'selesai') {
            $query->where('session_status', 'Selesai');
        }

        $dbOrders = $query->get();

        $orders = $dbOrders->map(function ($order) {
            $dateStr = $order->session_date ? $order->session_date->format('d M Y') : '10 Okt 2026';
            $timeShort = explode(' ', $order->session_time_slot)[0] ?? '09.00–10.00';

            return [
                'id' => $order->id,
                'code' => $order->order_code,
                'customer' => $order->customer_name,
                'package' => $order->package_name,
                'value' => 'Rp' . number_format($order->total_price, 0, ',', '.'),
                'date' => $dateStr,
                'time' => $timeShort,
                'session_status' => $order->session_status,
                'payment_status' => $order->payment_status,
            ];
        })->toArray();

        return view('admin.pesanan', compact('orders', 'activeTab'));
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
            $dateFormatted = $orderModel->session_date ? $orderModel->session_date->format('l, d F Y') : 'Sabtu, 17 Oktober 2026';
            $createdDate = $orderModel->created_at ? $orderModel->created_at->format('d F Y') : '5 Oktober 2026';
            $customerCode = $orderModel->customer ? $orderModel->customer->customer_code : 'PLG-CONTOH-001';

            $features = ($orderModel->package && is_array($orderModel->package->features)) 
                ? $orderModel->package->features 
                : [
                    'Sesi terarah dengan panduan pose natural',
                    'Akses galeri digital studio terkurasi',
                    'Izin cetak berlisensi pribadi'
                ];

            $paymentSummary = match($orderModel->payment_status) {
                'Terverifikasi' => 'Terverifikasi · Lunas',
                'Bukti terkirim' => 'Bukti terkirim · Belum terverifikasi',
                'Perlu perbaikan' => 'Perlu perbaikan bukti transfer',
                default => 'Belum dibayar'
            };

            $latestPayment = $orderModel->payments()->latest()->first();

            $order = [
                'id' => $orderModel->id,
                'code' => $orderModel->order_code,
                'customer' => $orderModel->customer_name,
                'customer_code' => $customerCode,
                'email' => $orderModel->customer_email,
                'phone' => $orderModel->customer_whatsapp,
                'created_date' => $createdDate,
                'package_name' => $orderModel->package_name,
                'package_image' => 'paket-2.png',
                'package_desc' => $orderModel->package ? $orderModel->package->description : 'Sesi foto berkesan.',
                'package_price' => 'Rp' . number_format($orderModel->total_price, 0, ',', '.'),
                'date_formatted' => $dateFormatted,
                'time_formatted' => $orderModel->session_time_slot,
                'location' => $orderModel->location,
                'features' => $features,
                'customer_note' => $orderModel->customer_notes ?? 'Tidak ada catatan tambahan dari pemesan.',
                'internal_note' => $orderModel->internal_notes ?? 'Siapkan panggilan perencanaan pra-sesi.',
                'invoice_number' => $orderModel->invoice_number,
                'payment_summary' => $paymentSummary,
                'payment_status_badge' => $orderModel->payment_status,
                'session_status' => $orderModel->session_status,
                'submitted_nominal' => 'Rp' . number_format($latestPayment ? $latestPayment->nominal : $orderModel->total_price, 0, ',', '.'),
                'submitted_date' => $latestPayment && $latestPayment->transfer_date ? $latestPayment->transfer_date->format('d M Y') : '5 Okt 2026',
            ];
        } else {
            // Fallback mock agar layout tetap utuh jika ID tidak ditemukan
            $order = [
                'code' => $id ?: 'CH-CONTOH-001',
                'customer' => 'Ayu Pramesti',
                'customer_code' => 'PLG-CONTOH-001',
                'email' => 'ayu.pramesti@example.com',
                'phone' => '+62 812 3456 7890',
                'created_date' => '5 Oktober 2026',
                'package_name' => 'Cerita Keluarga',
                'package_image' => 'paket-2.png',
                'package_desc' => 'Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.',
                'package_price' => 'Rp9.500.000',
                'date_formatted' => 'Sabtu, 17 Oktober 2026',
                'time_formatted' => '15.30–17.00 WIB · 90 menit',
                'location' => 'Rumah keluarga di Jakarta Selatan & taman terdekat',
                'features' => [
                    'Sesi 90 menit dengan narasi yang lebih dalam',
                    'Dua lokasi dan dua ganti kostum',
                    '50 foto diedit dengan hati-hati',
                    'Panggilan perencanaan & panduan styling',
                    'Galeri pribadi + izin cetak',
                ],
                'customer_note' => 'Kami ingin mengabadikan momen bersama kedua anak kami.',
                'internal_note' => 'Siapkan panggilan perencanaan untuk menyepakati dua lokasi dan panduan kostum.',
                'invoice_number' => 'INV-CH-2026-001',
                'payment_summary' => 'Bukti terkirim · Belum terverifikasi',
                'payment_status_badge' => 'Menunggu tinjauan bukti',
                'session_status' => 'Permintaan',
                'submitted_nominal' => 'Rp9.500.000',
                'submitted_date' => '5 Okt 2026',
            ];
        }

        return view('admin.pesanan-detail', compact('order'));
    }

    /**
     * Konfirmasi sesi pesanan
     */
    public function konfirmasiSesi($id)
    {
        $order = Order::where('order_code', $id)->orWhere('id', $id)->first();
        if ($order) {
            $order->update(['session_status' => 'Dikonfirmasi']);
        }

        return redirect()->route('admin.pesanan.detail', ['id' => $id])
            ->with('success', 'Sesi pesanan ' . $id . ' telah berhasil dikonfirmasi.');
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
                'internal_notes' => ($order->internal_notes ? $order->internal_notes . "\n" : '') . 'Alasan pembatalan: ' . $request->input('alasan', 'Dibatalkan oleh admin'),
            ]);
        }

        return redirect()->route('admin.pesanan.detail', ['id' => $id])
            ->with('success', 'Pesanan ' . $id . ' telah berhasil dibatalkan.');
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
            ->with('success', 'Pesanan ' . $id . ' telah diarsipkan.');
    }
}
