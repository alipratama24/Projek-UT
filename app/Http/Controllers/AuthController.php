<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login admin backoffice
     */
    public function showLoginForm(Request $request)
    {
        $token = $request->cookie('admin_auth_token');

        // Jika sudah login dan memiliki token valid di cookie, arahkan langsung ke dashboard
        if (Auth::check() && $token && Auth::user()->remember_token === $token) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Proses autentikasi login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Generate token unik khusus sesi admin
            $user = Auth::user();
            $token = Str::random(60);

            // Simpan token di database user
            $user->remember_token = $token;
            $user->save();

            // Set cookie admin_auth_token (misal berlaku 1440 menit / 1 hari)
            $cookie = cookie('admin_auth_token', $token, 1440, null, null, false, true);

            return redirect()->intended(route('admin.dashboard'))
                ->withCookie($cookie)
                ->with('success', 'Selamat datang kembali di Backoffice Clara Hayes.');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Logout admin
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->remember_token = null;
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hapus cookie token autentikasi admin
        $cookie = cookie()->forget('admin_auth_token');

        return redirect()->route('admin.login')
            ->withCookie($cookie)
            ->with('success', 'Anda telah berhasil keluar dari sesi admin.');
    }
}
