<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Admin dengan data langsung dari database
     */
    public function index()
    {
        $now = Carbon::now();
        $currentMonthName = $now->translatedFormat('F');

        // Metrik 1: Total Pesanan & Status Real-Time
        $totalOrders = Order::count();
        $totalOrdersMonth = $totalOrders;
        $permintaanCount = Order::where('session_status', 'Permintaan')->count();
        $dikonfirmasiCount = Order::where('session_status', 'Dikonfirmasi')->count();
        $selesaiCount = Order::where('session_status', 'Selesai')->count();

        // Metrik 2: Nilai Pesanan
        $totalNominal = Order::sum('total_price');
        $terverifikasiNominal = Payment::where('status', 'Terverifikasi')->sum('nominal');
        $pendingNominal = max(0, $totalNominal - $terverifikasiNominal);

        // Metrik 3: Sesi Mendatang
        $sesiMendatangCount = Order::where('session_date', '>=', $now->toDateString())
            ->where('session_status', '!=', 'Dibatalkan')
            ->count();

        // Metrik 4: Galeri Dalam Proses
        $galeriProsesCount = Gallery::whereIn('status', ['Penyuntingan', 'Draf'])->count();
        $galeriPenyuntingan = Gallery::where('status', 'Penyuntingan')->count();
        $galeriDraf = Gallery::where('status', 'Draf')->count();

        // Banner Alert: Bukti perlu ditinjau
        $buktiPendingCount = Payment::where('status', 'Bukti terkirim')->count();

        // Prioritas Hari Ini: Ditentukan secara terurut berdasarkan urgensi riil
        // 1. Verifikasi bukti transfer yang baru masuk
        $prioritasOrder = Order::with('latestPayment')
            ->where('payment_status', 'Bukti terkirim')
            ->latest()
            ->first();

        // 2. Jika tidak ada bukti, tinjau permintaan sesi baru
        if (! $prioritasOrder) {
            $prioritasOrder = Order::with('latestPayment')
                ->where('session_status', 'Permintaan')
                ->latest()
                ->first();
        }

        // 3. Jika tidak ada, ambil pesanan belum dibayar yang terbaru
        if (! $prioritasOrder) {
            $prioritasOrder = Order::with('latestPayment')
                ->where('payment_status', 'Belum dibayar')
                ->latest()
                ->first();
        }

        // Sesi Terdekat
        $sesiTerdekat = Order::where('session_status', '!=', 'Dibatalkan')
            ->orderBy('session_date', 'asc')
            ->limit(3)
            ->get();

        // Tren Bulanan (4 bulan terakhir)
        $monthlyTrend = [];
        for ($i = 3; $i >= 0; $i--) {
            $monthDate = (clone $now)->subMonths($i);
            $cnt = Order::whereMonth('created_at', $monthDate->month)
                ->whereYear('created_at', $monthDate->year)
                ->count();
            $monthlyTrend[] = [
                'label' => $monthDate->format('M'),
                'count' => $cnt,
                'height' => min(120, max(40, $cnt * 20)),
                'is_current' => $i === 0,
            ];
        }

        // Aktivitas Terbaru (gabungan Order & Payment)
        $recentOrders = Order::latest()->limit(2)->get();
        $recentPayments = Payment::latest()->limit(2)->get();

        return view('admin.dashboard', compact(
            'now',
            'currentMonthName',
            'totalOrdersMonth',
            'permintaanCount',
            'dikonfirmasiCount',
            'selesaiCount',
            'totalNominal',
            'terverifikasiNominal',
            'pendingNominal',
            'sesiMendatangCount',
            'galeriProsesCount',
            'galeriPenyuntingan',
            'galeriDraf',
            'buktiPendingCount',
            'prioritasOrder',
            'sesiTerdekat',
            'monthlyTrend',
            'recentOrders',
            'recentPayments'
        ));
    }
}
