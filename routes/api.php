<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // API Simpan Pelanggan Baru
    Route::post('/pelanggan', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $nextCount = Customer::count() + 1;
        $customerCode = 'PLG-' . date('Y') . '-' . str_pad($nextCount, 3, '0', STR_PAD_LEFT);

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

    // API Ambil Daftar Pelanggan
    Route::get('/pelanggan', function () {
        $customers = Customer::with('orders')->latest()->get()->map(function ($c) {
            return [
                'id' => $c->customer_code,
                'name' => $c->name,
                'email' => $c->email,
                'whatsapp' => $c->whatsapp,
                'history' => $c->orders->count() . ' pesanan',
                'status' => ucfirst($c->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    });
});
