@extends('layouts.admin')

@section('title', 'Galeri Pelanggan — Clara Hayes Photography')
@section('breadcrumb', 'Galeri / Galeri pelanggan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:4119) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Galeri pelanggan</h1>
            <p class="admin-page-subtitle">Kelola hasil sesi dan akses pribadi. Contoh galeri tahap lanjutan, bukan sesi yang sudah berlangsung hari ini.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-create-gallery">
            Buat galeri
        </button>
    </div>

    {{-- BARIS KONTEN STATUS (FIGMA 141:4130) --}}
    <div class="admin-status-summary-bar">
        <span class="status-indicator-item text-blue">● 2 dalam penyuntingan</span>
        <span class="status-indicator-item text-muted">● 1 draf kosong</span>
        <span class="status-indicator-item text-green">● 2 siap · Ilustratif</span>
    </div>

    {{-- PANEL UTAMA TABEL GALERI (FIGMA 141:4134) --}}
    <div class="admin-panel admin-orders-panel">
        {{-- TABS (FIGMA 141:4135) --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.galeri', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua galeri (5)
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'draf']) }}" class="admin-tab-item {{ $activeTab === 'draf' ? 'is-active' : '' }}">
                Draf
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'penyuntingan']) }}" class="admin-tab-item {{ $activeTab === 'penyuntingan' ? 'is-active' : '' }}">
                Penyuntingan
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'siap']) }}" class="admin-tab-item {{ $activeTab === 'siap' ? 'is-active' : '' }}">
                Siap
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'diterbitkan']) }}" class="admin-tab-item {{ $activeTab === 'diterbitkan' ? 'is-active' : '' }}">
                Diterbitkan
            </a>
        </div>

        {{-- FILTER & SEARCH (FIGMA 141:4146) --}}
        <div class="admin-filter-bar">
            <div class="admin-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari judul, pelanggan, atau pesanan" class="admin-search-input" value="{{ request('search') }}">
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
                <span>Semua periode</span>
            </div>
        </div>

        {{-- TABEL RECORD GALERI (FIGMA 141:4156) --}}
        <div class="admin-table-container">
            <table class="admin-orders-table">
                <thead>
                    <tr>
                        <th style="width: 26%;">Galeri / sampul</th>
                        <th style="width: 24%;">Pesanan / pemilik</th>
                        <th style="width: 18%;">Foto / target</th>
                        <th style="width: 14%;">Status</th>
                        <th style="width: 10%;">Akses</th>
                        <th style="width: 8%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($galleries as $g)
                        <tr>
                            <td>
                                <strong class="order-customer-name">{{ $g['title'] }}</strong>
                            </td>
                            <td>
                                <div class="table-cell-order">
                                    <span class="order-code-mono">{{ $g['order_code'] }}</span>
                                    <span class="schedule-time-text">{{ $g['owner'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-package">
                                    <span class="order-code-mono">{{ $g['photos'] }}</span>
                                    <span class="schedule-time-text">{{ $g['note'] ?? '' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-status-session status-{{ Str::slug($g['status']) }}">
                                    {{ $g['status'] }}
                                </span>
                            </td>
                            <td>
                                <span class="schedule-date-text">{{ $g['access'] }}</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ url('/galeri-klien') }}" target="_blank" class="admin-table-action-link">
                                    {{ $g['action'] }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINASI (FIGMA 141:4169) --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ count($galleries) }} dari 5 galeri contoh</span>
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

    {{-- FEEDBACK NOTICE (FIGMA 141:4182) --}}
    <div class="admin-alert-box alert-success-box">
        <strong class="admin-alert-title text-green">Galeri draf berhasil dibuat</strong>
        <p class="admin-alert-desc text-green">
            Potret Nadia belum memiliki foto dan belum diterbitkan. Unggah foto untuk mulai mengelola koleksi.
        </p>
    </div>

    <p class="admin-table-footer-hint">
        Status dan jumlah galeri adalah data ilustrasi. Tidak ada tautan pengiriman atau akses pelanggan nyata.
    </p>

    {{-- MODAL DIALOG BUAT GALERI (FIGMA 141:4298) --}}
    <div class="admin-modal-overlay" id="modal-create-gallery">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Buat galeri pelanggan</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-create-gallery">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">Inisiasi ruang foto online pribadi untuk pesanan yang telah dikonfirmasi</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Judul galeri *</label>
                    <input type="text" class="admin-form-input" placeholder="Contoh: Potret Sinta" required>
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Pesanan terkait *</label>
                    <select class="admin-form-input">
                        <option selected>CH-CONTOH-005 · Sinta Lestari</option>
                        <option>CH-CONTOH-006 · Maya Sari</option>
                        <option>CH-CONTOH-004 · Ruang Reka</option>
                    </select>
                </div>
            </div>

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Target jumlah foto</label>
                    <input type="number" class="admin-form-input" value="20">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Tipe akses</label>
                    <select class="admin-form-input">
                        <option selected>Privat (PIN / Tautan Terproteksi)</option>
                        <option>Terbuka (Portofolio)</option>
                    </select>
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Unggah foto batch awal (opsional)</label>
                <input type="file" class="admin-form-input" multiple>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-create-gallery">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Galeri baru berhasil dibuat sebagai draf.')">
                    Buat galeri draf
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:4186) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-open-create-gallery');
            const modal = document.getElementById('modal-create-gallery');
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

