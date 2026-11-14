<?php

namespace App\Http\Controllers;

use App\Models\StudioSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan studio
     */
    public function index()
    {
        $settings = StudioSetting::pluck('value', 'key')->toArray();

        return view('admin.pengaturan', compact('settings'));
    }

    /**
     * Simpan perubahan pengaturan ke database
     */
    public function save(Request $request)
    {
        $fields = [
            'studio_name',
            'admin_name',
            'studio_email',
            'studio_whatsapp',
            'studio_location',
            'timezone',
            'booking_mode',
            'payment_method',
            'invoice_prefix',
            'bank_account_name',
            'bank_account_number',
            'privacy_policy',
            'cancellation_policy',
            'refund_policy',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                StudioSetting::set($field, $request->input($field));
            }
        }

        return redirect()->route('admin.pengaturan')
            ->with('success', 'Pengaturan studio berhasil diperbarui dan disimpan ke database.');
    }
}
