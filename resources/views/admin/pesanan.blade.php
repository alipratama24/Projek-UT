@extends('layouts.admin')

@section('title', 'Pesanan — Clara Hayes Photography')
@section('breadcrumb', 'Pesanan / Pesanan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:1035) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pesanan</h1>
            <p class="admin-page-subtitle">Kelola permintaan, persiapan sesi, dan riwayat pelanggan.</p>
        </div>
        <a href="{{ route('pemesanan') }}" target="_blank" class="admin-btn-primary">
            Tambah pesanan
        </a>
    </div>

    {{-- BARIS KONTEN METRIK STATUS (FIGMA 141:1046) --}}
    <div class="admin-status-summary-bar">
        <span class="status-indicator-item text-blue">● 12 pesanan bulan ini</span>
        <span class="status-indicator-item text-gold">● 4 perlu ditinjau</span>
        <span class="status-indicator-item text-green">● 6 sesi disetujui</span>
    </div>

    {{-- PANEL UTAMA TABEL (FIGMA 141:1050) --}}
    <div class="admin-panel admin-orders-panel">
        {{-- PILIHAN TAB STATUS (FIGMA 141:1051) --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.pesanan', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua pesanan (24)
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'permintaan']) }}" class="admin-tab-item {{ $activeTab === 'permintaan' ? 'is-active' : '' }}">
                Permintaan (4)
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'dikonfirmasi']) }}" class="admin-tab-item {{ $activeTab === 'dikonfirmasi' ? 'is-active' : '' }}">
                Dikonfirmasi (6)
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'selesai']) }}" class="admin-tab-item {{ $activeTab === 'selesai' ? 'is-active' : '' }}">
                Selesai
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'arsip']) }}" class="admin-tab-item {{ $activeTab === 'arsip' ? 'is-active' : '' }}">
                Arsip
            </a>
        </div>

        {{-- FILTER & SEARCH (FIGMA 141:1062) --}}
        <div class="admin-filter-bar">
            <div class="admin-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari nama atau kode pesanan" class="admin-search-input" value="{{ request('search') }}">
            </div>

            <div class="admin-select-dropdown">
                <span>Semua status</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>

            <div class="admin-select-dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Oktober 2026</span>
            </div>
        </div>

        {{-- TABEL RECORD (FIGMA 141:1072) --}}
        <div class="admin-table-container">
            <table class="admin-orders-table">
                <thead>
                    <tr>
                        <th style="width: 24%;">Pesanan / pelanggan</th>
                        <th style="width: 22%;">Paket / nilai</th>
                        <th style="width: 18%;">Jadwal WIB</th>
                        <th style="width: 14%;">Sesi</th>
                        <th style="width: 14%;">Pembayaran</th>
                        <th style="width: 8%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $item)
                        <tr>
                            <td>
                                <div class="table-cell-order">
                                    <span class="order-code-mono">{{ $item['code'] }}</span>
                                    <strong class="order-customer-name">{{ $item['customer'] }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-package">
                                    <span class="package-name-text">{{ $item['package'] }}</span>
                                    <span class="package-value-text">{{ $item['value'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-schedule">
                                    <span class="schedule-date-text">{{ $item['date'] }}</span>
                                    <span class="schedule-time-text">{{ $item['time'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-status-session status-{{ Str::slug($item['session_status']) }}">
                                    {{ $item['session_status'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status-payment status-{{ Str::slug($item['payment_status']) }}">
                                    {{ $item['payment_status'] }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.pesanan.detail', ['id' => $item['code']]) }}" class="admin-table-action-link">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINASI (FIGMA 141:1091) --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ count($orders) }} dari 24 record</span>
            <div class="pagination-controls">
                <button type="button" class="btn-page-nav is-disabled" disabled>Sebelumnya</button>
                <div class="pagination-numbers">
                    <span class="page-num is-active">● 1</span>
                    <span class="page-num">2</span>
                    <span class="page-num">3</span>
                </div>
                <button type="button" class="btn-page-nav">Berikutnya</button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:1104) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>
@endsection

