@extends('layouts.admin')

@section('title', 'Pesanan — naro capture')
@section('breadcrumb', 'Pesanan / Pesanan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:1035) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pesanan</h1>
            <p class="admin-page-subtitle">Kelola permintaan, persiapan sesi, dan riwayat pelanggan.</p>
        </div>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom: 16px; padding: 12px 16px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; color: #065F46; font-size: 13px;">
            <strong>Berhasil:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="admin-alert-box alert-danger-box" style="margin-bottom: 16px; padding: 12px 16px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 6px; color: #991B1B; font-size: 13px;">
            <strong>Perhatian:</strong> {{ session('error') }}
        </div>
    @endif

    {{-- BARIS KONTEN METRIK STATUS (FIGMA 141:1046) --}}
    <div class="admin-status-summary-bar">
        <span class="status-indicator-item text-blue">● {{ $totalOrdersCount ?? count($orders) }} total pesanan</span>
        <span class="status-indicator-item text-gold">● {{ $permintaanCount ?? 0 }} perlu ditinjau</span>
        <span class="status-indicator-item text-green">● {{ $dikonfirmasiCount ?? 0 }} sesi disetujui</span>
        <span class="status-indicator-item text-muted">● {{ $selesaiCount ?? 0 }} selesai · {{ $arsipCount ?? 0 }} diarsipkan</span>
    </div>

    {{-- PANEL UTAMA TABEL (FIGMA 141:1050) --}}
    <div class="admin-panel admin-orders-panel">
        {{-- PILIHAN TAB STATUS (FIGMA 141:1051) --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.pesanan', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua pesanan
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'permintaan']) }}" class="admin-tab-item {{ $activeTab === 'permintaan' ? 'is-active' : '' }}">
                Permintaan
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'dikonfirmasi']) }}" class="admin-tab-item {{ $activeTab === 'dikonfirmasi' ? 'is-active' : '' }}">
                Dikonfirmasi
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'selesai']) }}" class="admin-tab-item {{ $activeTab === 'selesai' ? 'is-active' : '' }}">
                Selesai
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'arsip']) }}" class="admin-tab-item {{ $activeTab === 'arsip' ? 'is-active' : '' }}">
                Arsip
            </a>
            <a href="{{ route('admin.pesanan', ['tab' => 'draft']) }}" class="admin-tab-item {{ $activeTab === 'draft' ? 'is-active' : '' }}">
                Draft
            </a>
        </div>

        {{-- FILTER & SEARCH (FIGMA 141:1062) --}}
        <div class="admin-filter-bar">
            <form method="GET" action="{{ route('admin.pesanan') }}" style="display:flex; gap:12px; align-items:center; width:100%; flex-wrap:wrap;">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="admin-search-input-wrap" style="flex:1; min-width:260px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" placeholder="Cari nama, paket, atau kode pesanan" class="admin-search-input" value="{{ request('search') }}">
                </div>

                <div class="admin-select-dropdown">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>{{ now()->translatedFormat('F Y') }}</span>
                </div>
            </form>
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
                        <th style="width: 12%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $item)
                        @php
                            $canDeleteOrDraft = !in_array($item['session_status'], ['Dikonfirmasi', 'Selesai']);
                        @endphp
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
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                    <a href="{{ route('admin.pesanan.detail', ['id' => $item['code']]) }}" class="admin-table-action-link" style="white-space: nowrap; font-size: 12px; padding: 4px 6px;">
                                        Detail →
                                    </a>

                                    @if($canDeleteOrDraft)
                                        @if($item['session_status'] !== 'Draft')
                                            <form action="{{ route('admin.pesanan.draft', ['id' => $item['code']]) }}" method="POST" onsubmit="return confirm('Ubah status pesanan {{ $item['code'] }} menjadi Draft?')" style="display:inline; margin:0;">
                                                @csrf
                                                <button type="submit" style="background:#F1F5F9; border:1px solid #CBD5E1; color:#475569; cursor:pointer; font-size:11px; font-weight:500; padding:4px 7px; border-radius:4px; display:inline-flex; align-items:center;" title="Ubah status pesanan menjadi Draft">
                                                    Draft
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.pesanan.destroy', ['id' => $item['code']]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan {{ $item['code'] }} secara permanen?')" style="display:inline; margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background:#FEF2F2; border:1px solid #FECACA; color:#DC2626; cursor:pointer; font-size:11px; font-weight:600; padding:4px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:3px;" title="Hapus pesanan {{ $item['code'] }}">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled style="background:#F3F4F6; border:1px solid #E5E7EB; color:#9CA3AF; cursor:not-allowed; font-size:11px; font-weight:500; padding:4px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:3px;" title="Status sesi {{ $item['session_status'] }} terkunci dan tidak dapat dihapus atau di-draft-kan">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path></svg>
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: #6E7A8B;">
                                Tidak ada data pesanan yang sesuai dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINASI (FIGMA 141:1091) --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ count($orders) }} dari {{ $totalOrdersCount ?? count($orders) }} pesanan</span>
            <div class="pagination-controls">
                <button type="button" class="btn-page-nav is-disabled" disabled>Sebelumnya</button>
                <div class="pagination-numbers">
                    <span class="page-num is-active">● 1</span>
                </div>
                <button type="button" class="btn-page-nav is-disabled" disabled>Berikutnya</button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:1104) --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Sistem Manajemen Pesanan · Akses terbatas admin
    </div>
@endsection

