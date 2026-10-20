<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah ada cookie token admin ('admin_auth_token')
        $token = $request->cookie('admin_auth_token');

        if (!$token) {
            // Jika tidak ada cookie token, pastikan logout jika masih ada session
            if (Auth::check()) {
                Auth::logout();
            }
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Sesi Anda telah berakhir atau token autentikasi tidak ditemukan. Silakan login terlebih dahulu.']);
        }

        // 2. Cek apakah user sedang login di Auth
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Silakan masuk ke akun admin terlebih dahulu.']);
        }

        // 3. Validasi token cocok dengan token user (remember_token)
        $user = Auth::user();
        if ($user->remember_token !== $token) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->withCookie(cookie()->forget('admin_auth_token'))
                ->withErrors(['email' => 'Token autentikasi tidak valid. Silakan login kembali.']);
        }

        return $next($request);
    }
}
