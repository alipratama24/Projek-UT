<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Halaman Cek Status Pesanan Publik (1 Field: Kode Pesanan / Invoice)
     */
    public function show(Request $request)
    {
        $searchCode = trim($request->query('kode', ''));
        $manualStatus = $request->query('status');

        $isSearching = $request->has('kode') || ! empty($manualStatus);
        $order = null;

        if ($isSearching && ! empty($searchCode)) {
            $order = Order::with(['latestPayment', 'package'])
                ->where(function ($q) use ($searchCode) {
                    $q->where('order_code', $searchCode)
                        ->orWhere('invoice_number', $searchCode);
                })
                ->first();
        }

        $hasSearched = $isSearching;
        $orderFound = (bool) $order;
        $data = null;
        $statusKey = 'menunggu-pembayaran';
        $maskedEmail = '-';
        $maskedPhone = '-';

        if ($order) {
            if ($manualStatus) {
                $statusKey = $manualStatus;
            } elseif ($order->session_status === 'Dibatalkan') {
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

            $rawEmail = $order->customer_email;
            $rawPhone = $order->customer_whatsapp;
            $maskedEmail = $this->maskEmail($rawEmail);
            $maskedPhone = $this->maskPhone($rawPhone);

            // Tentukan gambar paket yang sesuai
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

            $packageDesc = ($order->package && ! empty($order->package->description))
                ? $order->package->description
                : 'Sesi studio naro capture.';

            $lokasiHint = ($order->package && ! empty($order->package->tagline))
                ? $order->package->tagline
                : 'Lokasi dan detail sesi studio.';

            $packageItems = ($order->package && is_array($order->package->features))
                ? $order->package->features
                : [
                    'Sesi foto studio terarah',
                    'File diedit dengan teliti',
                    'Akses galeri digital pribadi',
                ];

            $data = [
                'order_code' => $order->order_code,
                'invoice_number' => $order->invoice_number,
                'package_name' => $order->package_name,
                'package_price' => 'Rp'.number_format($order->total_price, 0, ',', '.'),
                'package_image' => $packageImage,
                'package_duration' => $order->package ? ($order->package->duration_minutes ?? 90) : 90,
                'package_desc' => $packageDesc,
                'lokasi_hint' => $lokasiHint,
                'package_items' => $packageItems,
                'session_date' => $order->session_date ? $order->session_date->format('l, d F Y') : 'Jadwal belum ditentukan',
                'session_time' => $order->session_time_slot ?: 'Waktu belum ditentukan',
                'nama_lengkap' => $order->customer_name,
                'email' => $rawEmail,
                'whatsapp' => $rawPhone,
                'lokasi' => $order->location ?: 'Studio Utama naro capture',
                'catatan' => $order->customer_notes,
                'session_status' => $order->session_status,
                'payment_status' => $order->payment_status,
                'internal_notes' => $order->internal_notes,
            ];
        }

        return view('cek-pesanan', compact(
            'data',
            'order',
            'searchCode',
            'statusKey',
            'maskedEmail',
            'maskedPhone',
            'hasSearched',
            'orderFound'
        ));
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        $name = $parts[0];
        $domain = $parts[1];
        $firstChar = substr($name, 0, 1);

        return $firstChar.'•••@'.$domain;
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) < 7) {
            return $phone;
        }
        $start = substr($phone, 0, 6);
        $end = substr($phone, -4);

        return $start.' •••• '.$end;
    }
}
