<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Backoffice — Clara Hayes Photography')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="admin-body">
    <div class="admin-layout">
        {{-- SIDEBAR STUDIO (FIGMA 141:867) --}}
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="admin-brand-name">clara hayes.</span>
                <span class="admin-brand-tag">STUDIO FOTOGRAFI / BACKOFFICE</span>
            </div>

            <nav class="admin-nav-menu">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ url('/admin/pesanan') }}" class="admin-nav-item {{ request()->is('admin/pesanan*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Pesanan</span>
                </a>
                <a href="{{ url('/admin/pembayaran') }}" class="admin-nav-item {{ request()->is('admin/pembayaran*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <span>Pembayaran</span>
                </a>
                <a href="{{ url('/admin/jadwal') }}" class="admin-nav-item {{ request()->is('admin/jadwal*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Jadwal</span>
                </a>
                <a href="{{ url('/admin/pelanggan') }}" class="admin-nav-item {{ request()->is('admin/pelanggan*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Pelanggan</span>
                </a>
                <a href="{{ url('/admin/paket') }}" class="admin-nav-item {{ request()->is('admin/paket*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span>Paket Layanan</span>
                </a>
                <a href="{{ url('/admin/galeri') }}" class="admin-nav-item {{ request()->is('admin/galeri*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <span>Galeri</span>
                </a>
                <a href="{{ url('/admin/pengaturan') }}" class="admin-nav-item {{ request()->is('admin/pengaturan*') ? 'is-active' : '' }}">
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ url('/') }}" class="admin-link-client" target="_blank">Lihat situs pelanggan ↗</a>
                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="admin-link-logout" style="background: none; border: none; cursor: pointer; padding: 0; font-family: inherit; font-size: inherit; text-align: left; width: 100%;">Keluar →</button>
                </form>
                <hr class="divider">
                <div class="admin-profile-meta">
                    <strong class="admin-profile-name">{{ Auth::check() ? Auth::user()->name : 'CH · Clara Hayes' }}</strong>
                    <span class="admin-profile-role">{{ Auth::check() ? Auth::user()->email : 'admin@clarahayes.com' }}</span>
                </div>
            </div>
        </aside>

        {{-- AREA KERJA UTAMA --}}
        <div class="admin-main">
            {{-- TOPBAR (FIGMA 141:880) --}}
            <header class="admin-topbar">
                <span class="admin-breadcrumb">Studio / @yield('breadcrumb', 'Dashboard / Selamat pagi, Clara')</span>
                <div class="admin-topbar-right">
                    <span class="admin-location-time">WIB · Jakarta</span>
                    <button type="button" class="admin-btn-bell" title="Pemberitahuan">
                        <img src="{{ asset('images/icon-bell.svg') }}" alt="Bell" width="18" height="18">
                    </button>
                </div>
            </header>

            {{-- KONTEN UTAMA --}}
            <div class="admin-content">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>

