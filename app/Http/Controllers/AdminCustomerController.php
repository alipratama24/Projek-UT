<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    /**
     * Tampilkan daftar pelanggan
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'aktif');
        $search = trim($request->query('search', ''));

        $query = Customer::with('orders')->latest();
        if ($activeTab === 'aktif') {
            $query->where('status', 'aktif');
        } elseif ($activeTab === 'diarsipkan' || $activeTab === 'riwayat') {
            $query->whereIn('status', ['diarsipkan', 'riwayat']);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        $aktifCount = Customer::where('status', 'aktif')->count();
        $diarsipkanCount = Customer::whereIn('status', ['diarsipkan', 'riwayat'])->count();
        $totalCount = Customer::count();

        $customers = $query->get()->map(function ($c) {
            $orderCount = $c->orders->count();
            $latestOrder = $c->orders->sortByDesc('created_at')->first();

            return [
                'raw_id' => $c->id,
                'name' => $c->name,
                'id' => $c->customer_code,
                'email' => $c->email,
                'whatsapp' => $c->whatsapp,
                'history' => $orderCount.' pesanan',
                'status' => ucfirst($c->status),
                'latest_order_code' => $latestOrder ? $latestOrder->order_code : null,
            ];
        })->toArray();

        return view('admin.pelanggan', compact('customers', 'activeTab', 'aktifCount', 'diarsipkanCount', 'totalCount'));
    }

    /**
     * Detail Profil Pelanggan & Riwayat Pesanan
     */
    public function show($id)
    {
        $customer = Customer::with(['orders.package', 'orders.payments'])
            ->where('customer_code', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // Cari semua pesanan yang terhubung lewat customer_id atau email/whatsapp
        $orders = Order::with(['package', 'payments'])
            ->where('customer_id', $customer->id)
            ->orWhere('customer_email', $customer->email)
            ->orWhere('customer_whatsapp', $customer->whatsapp)
            ->latest('session_date')
            ->get();

        $totalSpending = $orders->sum('total_price');
        $completedSessions = $orders->where('session_status', 'Selesai')->count();
        $upcomingSessions = $orders->whereIn('session_status', ['Permintaan', 'Dikonfirmasi'])->count();

        // Bersihkan nomor WhatsApp untuk link langsung
        $cleanWa = preg_replace('/[^0-9]/', '', $customer->whatsapp);
        if (str_starts_with($cleanWa, '0')) {
            $cleanWa = '62'.substr($cleanWa, 1);
        }
        $whatsappLink = 'https://wa.me/'.$cleanWa;

        return view('admin.pelanggan-detail', compact(
            'customer',
            'orders',
            'totalSpending',
            'completedSessions',
            'upcomingSessions',
            'whatsappLink'
        ));
    }

    /**
     * Tambah Pelanggan Baru
     */
    public function store(Request $request)
    {
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

        return redirect()->route('admin.pelanggan.detail', ['id' => $customer->customer_code])
            ->with('success', 'Pelanggan '.$customer->name.' ('.$customerCode.') berhasil ditambahkan.');
    }

    /**
     * Perbarui Profil Pelanggan
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::where('customer_code', $id)->orWhere('id', $id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'status' => 'required|in:aktif,diarsipkan',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.pelanggan.detail', ['id' => $customer->customer_code])
            ->with('success', 'Profil pelanggan '.$customer->name.' berhasil diperbarui.');
    }

    /**
     * Ubah status arsip pelanggan
     */
    public function toggleStatus($id)
    {
        $customer = Customer::where('customer_code', $id)->orWhere('id', $id)->firstOrFail();
        $newStatus = $customer->status === 'aktif' ? 'diarsipkan' : 'aktif';
        $customer->update(['status' => $newStatus]);

        $pesan = $newStatus === 'aktif' ? 'diaktifkan kembali.' : 'diarsipkan.';

        return redirect()->route('admin.pelanggan.detail', ['id' => $customer->customer_code])
            ->with('success', 'Pelanggan '.$customer->name.' berhasil '.$pesan);
    }
}
