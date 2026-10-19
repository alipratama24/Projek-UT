@extends('layouts.admin')

@section('title', 'Pembayaran — Clara Hayes Photography')
@section('breadcrumb', 'Pembayaran / Pembayaran')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:1701) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pembayaran</h1>
            <p class="admin-page-subtitle">Cocokkan bukti dengan invoice. Verifikasi tidak menyetujui jadwal sesi.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-manual-payment">
            Catat pembayaran manual
        </button>
    </div>

    {{-- 4 KARTU METRIK PEMBAYARAN (FIGMA 141:1712) --}}
    <div class="admin-metrics-grid">
        <div class="admin-panel admin-payment-metric-card">
            <span class="payment-metric-label">Bukti menunggu</span>
            <strong class="payment-metric-val">3</strong>
            <span class="payment-metric-sub">Rp23.500.000 diajukan</span>
        </div>
        <div class="admin-panel admin-payment-metric-card">
            <span class="payment-metric-label">Perlu perbaikan</span>
            <strong class="payment-metric-val">1</strong>
            <span class="payment-metric-sub">Rp4.500.000 diajukan</span>
        </div>
        <div class="admin-panel admin-payment-metric-card">
            <span class="payment-metric-label">Terverifikasi Oktober</span>
            <strong class="payment-metric-val">8</strong>
            <span class="payment-metric-sub">Rp81.000.000 tercatat</span>
        </div>
        <div class="admin-panel admin-payment-metric-card">
            <span class="payment-metric-label">Belum dibayar</span>
            <strong class="payment-metric-val">1</strong>
            <span class="payment-metric-sub">Rp18.000.000 pada invoice</span>
        </div>
    </div>

    {{-- FEEDBACK BANNER (JIKA ADA NOTIFIKASI) --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box">
            <strong class="admin-alert-title">Catatan pembayaran disimpan</strong>
            <p class="admin-alert-desc">{{ session('success') }}</p>
        </div>
    @endif

    {{-- PANEL UTAMA TABEL PEMBAYARAN (FIGMA 141:1729) --}}
    <div class="admin-panel admin-orders-panel">
        {{-- TABS (FIGMA 141:1730) --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.pembayaran', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua
            </a>
            <a href="{{ route('admin.pembayaran', ['tab' => 'bukti-terkirim']) }}" class="admin-tab-item {{ $activeTab === 'bukti-terkirim' ? 'is-active' : '' }}">
                Bukti terkirim (3)
            </a>
            <a href="{{ route('admin.pembayaran', ['tab' => 'belum-dibayar']) }}" class="admin-tab-item {{ $activeTab === 'belum-dibayar' ? 'is-active' : '' }}">
                Belum dibayar
            </a>
            <a href="{{ route('admin.pembayaran', ['tab' => 'terverifikasi']) }}" class="admin-tab-item {{ $activeTab === 'terverifikasi' ? 'is-active' : '' }}">
                Terverifikasi
            </a>
            <a href="{{ route('admin.pembayaran', ['tab' => 'perlu-perbaikan']) }}" class="admin-tab-item {{ $activeTab === 'perlu-perbaikan' ? 'is-active' : '' }}">
                Perlu perbaikan
            </a>
        </div>

        {{-- FILTER & SEARCH (FIGMA 141:1741) --}}
        <div class="admin-filter-bar">
            <div class="admin-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari ID pembayaran, invoice, atau pelanggan" class="admin-search-input" value="{{ request('search') }}">
            </div>

            <div class="admin-select-dropdown">
                <span>Semua sumber</span>
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

        {{-- PEMBERITAHUAN ANTREAN (FIGMA 141:1751) --}}
        <div class="admin-alert-box alert-warning-box">
            <strong class="admin-alert-title text-gold">Bukti tertua diterima 5 Oktober</strong>
            <p class="admin-alert-desc text-gold">Tinjau antrean sesuai waktu kirim. Penanda ini bukan janji waktu penyelesaian.</p>
        </div>

        {{-- TABEL RECORD PEMBAYARAN (FIGMA 141:1754) --}}
        <div class="admin-table-container">
            <table class="admin-orders-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Pembayaran / pengirim</th>
                        <th style="width: 25%;">Invoice / pesanan</th>
                        <th style="width: 15%;">Nominal</th>
                        <th style="width: 15%;">Diterima</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 8%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $p)
                        <tr>
                            <td>
                                <div class="table-cell-order">
                                    <span class="order-code-mono">{{ $p['id'] }}</span>
                                    <strong class="order-customer-name">{{ $p['sender'] }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-package">
                                    <span class="order-code-mono">{{ $p['invoice'] }}</span>
                                    <span class="schedule-time-text">{{ $p['order_code'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="order-code-mono">{{ $p['nominal'] }}</span>
                            </td>
                            <td>
                                <div class="table-cell-schedule">
                                    <span class="schedule-date-text">{{ $p['received_date'] }}</span>
                                    <span class="schedule-time-text">{{ $p['received_time'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-status-payment status-{{ Str::slug($p['status']) }}">
                                    {{ $p['status'] }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.pembayaran.detail', ['id' => $p['id']]) }}" class="admin-table-action-link">
                                    Tinjau →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINASI (FIGMA 141:1763) --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ count($payments) }} dari 3 bukti terkirim</span>
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

        <p class="admin-table-footer-hint">
            ID pembayaran berbeda dari kode pesanan dan nomor invoice. Transaksi terverifikasi tidak dapat dihapus.
        </p>
    </div>

    {{-- MODAL DIALOG CATAT PEMBAYARAN MANUAL (FIGMA 141:2208) --}}
    <div class="admin-modal-overlay" id="modal-manual-payment">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Catat pembayaran manual</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-manual-payment">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">Rekam transaksi tunai atau transfer langsung di studio</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nomor invoice terkait *</label>
                    <input type="text" class="admin-form-input font-mono" placeholder="INV-CH-2026-..." value="INV-CH-2026-004">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Metode penerimaan *</label>
                    <select class="admin-form-input">
                        <option selected>Tunai di Kasir Studio</option>
                        <option>EDC / Kartu Debit</option>
                        <option>Transfer Bank Langsung</option>
                    </select>
                </div>
            </div>

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nominal diterima *</label>
                    <input type="text" class="admin-form-input font-mono" placeholder="Rp 0" value="Rp18.000.000">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Tanggal penerimaan *</label>
                    <input type="date" class="admin-form-input" value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Catatan kasir / administrasi</label>
                <textarea class="admin-form-input" rows="3">Diterima langsung di studio oleh Clara Hayes.</textarea>
            </div>

            <div class="admin-alert-box alert-success-box">
                <strong class="admin-alert-title text-green">Pencatatan resmi</strong>
                <p class="admin-alert-desc text-green">
                    Pembayaran manual akan tercatat ke pembukuan dan memvalidasi invoice terkait.
                </p>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-manual-payment">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Pembayaran manual berhasil disimpan.')">
                    Simpan pembayaran
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:1780) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-open-manual-payment');
            const modal = document.getElementById('modal-manual-payment');
            if (btn && modal) {
                btn.addEventListener('click', () => modal.classList.add('is-open'));
            }

            document.querySelectorAll('[data-close]').forEach(b => {
                b.addEventListener('click', () => {
                    const id = b.getAttribute('data-close');
                    const m = document.getElementById(id);
                    if (m) m.classList.remove('is-open');
                });
            });
        });
    </script>
@endsection

