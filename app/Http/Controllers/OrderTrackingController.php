<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Halaman Cek Status Pesanan Publik
     */
    public function show(Request $request)
    {
        $searchCode = trim($request->query('kode', ''));
        $searchContact = trim($request->query('kontak', ''));
        $manualStatus = $request->query('status');

        $order = null;

        if (!empty($searchCode)) {
            $order = Order::with(['latestPayment', 'package'])->where(function ($q) use ($searchCode) {
                $q->where('order_code', $searchCode)
                  ->orWhere('invoice_number', $searchCode);
            })->first();
        }

        // Jika tidak dicari dengan kode, ambil pesanan dari session atau pesanan terbaru
        if (!$order && session()->has('last_booking')) {
            $sess = session('last_booking');
            if (isset($sess['order_code'])) {
                $order = Order::with(['latestPayment', 'package'])->where('order_code', $sess['order_code'])->first();
            }
        }

        if (!$order) {
            $order = Order::with(['latestPayment', 'package'])->latest()->first();
        }

        // Tentukan mapping status UI Figma:
        // 'sedang-diperiksa', 'menunggu-pembayaran', 'dikonfirmasi', 'perbaikan-bukti', 'sesi-selesai', 'dibatalkan', 'kedaluwarsa'
        $statusKey = 'sedang-diperiksa';

        if ($manualStatus) {
            $statusKey = $manualStatus;
        } elseif ($order) {
            if ($order->session_status === 'Dibatalkan') {
                $statusKey = 'dibatalkan';
            } elseif ($order->session_status === 'Selesai') {
                $statusKey = 'sesi-selesai';
            } elseif ($order->payment_status === 'Perlu perbaikan') {
                $statusKey = 'perbaikan-bukti';
            } elseif ($order->session_status === 'Dikonfirmasi') {
                $statusKey = 'dikonfirmasi';
            } elseif ($order->payment_status === 'Bukti terkirim') {
                $statusKey = 'sedang-diperiksa';
            } else {
                $statusKey = 'menunggu-pembayaran';
            }
        }

        // Samarkan email & telepon untuk privasi sesuai UI Figma
        $rawEmail = $order ? $order->customer_email : 'ayu.pramesti@gmail.com';
        $rawPhone = $order ? $order->customer_whatsapp : '+62 812 3456 7890';

        $maskedEmail = $this->maskEmail($rawEmail);
        $maskedPhone = $this->maskPhone($rawPhone);

        // Tentukan gambar paket yang sesuai
        $packageImage = 'paket-family.jpg';
        if ($order && $order->package && !empty($order->package->image)) {
            $packageImage = $order->package->image;
        } elseif ($order && !empty($order->package_name)) {
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

        $packageDesc = ($order && $order->package && !empty($order->package->description))
            ? $order->package->description
            : 'Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.';

        $lokasiHint = ($order && $order->package && !empty($order->package->tagline))
            ? $order->package->tagline
            : 'Dua lokasi termasuk dalam paket. Alamat dan lokasi akhir dibahas saat perencanaan.';

        $packageItems = ($order && $order->package && is_array($order->package->features))
            ? $order->package->features
            : [
                'Dua lokasi dan dua ganti kostum',
                '50 foto diedit dengan hati-hati',
                'Panggilan perencanaan & panduan styling',
                'Galeri pribadi + izin cetak',
            ];

        $data = [
            'order_code' => $order ? $order->order_code : 'CH-CONTOH-001',
            'invoice_number' => $order ? $order->invoice_number : 'INV-CH-2026-001',
            'package_name' => $order ? $order->package_name : 'Cerita Keluarga',
            'package_price' => $order ? 'Rp' . number_format($order->total_price, 0, ',', '.') : 'Rp9.500.000',
            'package_image' => $packageImage,
            'package_duration' => $order && $order->package ? ($order->package->duration_minutes ?? 90) : 90,
            'package_desc' => $packageDesc,
            'lokasi_hint' => $lokasiHint,
            'package_items' => $packageItems,
            'session_date' => $order && $order->session_date ? $order->session_date->format('l, d F Y') : 'Sabtu, 17 Oktober 2026',
            'session_time' => $order ? $order->session_time_slot : '15.30–17.00 WIB · 90 menit',
            'nama_lengkap' => $order ? $order->customer_name : 'Ayu Pramesti',
            'email' => $rawEmail,
            'whatsapp' => $rawPhone,
            'lokasi' => $order ? $order->location : 'Rumah keluarga di Jakarta Selatan & taman terdekat',
            'catatan' => $order ? $order->customer_notes : 'Kami ingin mengabadikan momen bersama kedua anak kami.',
        ];

        $hasSearched = !empty($searchCode) || !empty($manualStatus) || session()->has('last_booking');

        return view('cek-pesanan', compact(
            'data',
            'searchCode',
            'searchContact',
            'statusKey',
            'maskedEmail',
            'maskedPhone',
            'hasSearched'
        ));
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;
        $name = $parts[0];
        $domain = $parts[1];
        $firstChar = substr($name, 0, 1);
        return $firstChar . '•••@' . $domain;
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) < 7) return $phone;
        $start = substr($phone, 0, 6);
        $end = substr($phone, -4);
        return $start . ' •••• ' . $end;
    }
}
