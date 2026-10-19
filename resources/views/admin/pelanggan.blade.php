@extends('layouts.admin')

@section('title', 'Pelanggan — Clara Hayes Photography')
@section('breadcrumb', 'Pelanggan / Pelanggan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:3090) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pelanggan</h1>
            <p class="admin-page-subtitle">Kontak, riwayat sesi, dan catatan pribadi untuk layanan yang lebih personal.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-add-customer">
            Tambah pelanggan
        </button>
    </div>

    {{-- FEEDBACK NOTICE (FIGMA 141:3101) --}}
    <div class="admin-alert-box alert-success-box">
        <strong class="admin-alert-title text-green">Data pelanggan tersimpan</strong>
        <p class="admin-alert-desc text-green">
            Contoh feedback: catatan Nadia Putri diperbarui. Riwayat pesanan dan pembayaran tidak berubah.
        </p>
    </div>

    {{-- PANEL UTAMA TABEL PELANGGAN (FIGMA 141:3104) --}}
    <div class="admin-panel admin-orders-panel">
        {{-- TABS (FIGMA 141:3105) --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.pelanggan', ['tab' => 'aktif']) }}" class="admin-tab-item {{ $activeTab === 'aktif' ? 'is-active' : '' }}">
                Aktif (18)
            </a>
            <a href="{{ route('admin.pelanggan', ['tab' => 'diarsipkan']) }}" class="admin-tab-item {{ $activeTab === 'diarsipkan' ? 'is-active' : '' }}">
                Diarsipkan (4)
            </a>
            <a href="{{ route('admin.pelanggan', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua
            </a>
        </div>

        {{-- FILTER & SEARCH (FIGMA 141:3112) --}}
        <div class="admin-filter-bar">
            <div class="admin-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari nama, email, atau WhatsApp" class="admin-search-input" value="{{ request('search') }}">
            </div>

            <div class="admin-select-dropdown">
                <span>Semua riwayat</span>
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

        {{-- TABEL RECORD PELANGGAN (FIGMA 141:3122) --}}
        <div class="admin-table-container">
            <table class="admin-orders-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Pelanggan / ID</th>
                        <th style="width: 25%;">Email ilustrasi</th>
                        <th style="width: 20%;">WhatsApp</th>
                        <th style="width: 15%;">Riwayat</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 7%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $c)
                        <tr>
                            <td>
                                <div class="table-cell-order">
                                    <strong class="order-customer-name">{{ $c['name'] }}</strong>
                                    <span class="order-code-mono text-muted">{{ $c['id'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="package-name-text">{{ $c['email'] }}</span>
                            </td>
                            <td>
                                <span class="order-code-mono">{{ $c['whatsapp'] }}</span>
                            </td>
                            <td>
                                <span class="schedule-date-text">{{ $c['history'] }}</span>
                            </td>
                            <td>
                                <span class="badge-status-payment status-terverifikasi">
                                    {{ $c['status'] }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.pesanan.detail', ['id' => 'CH-CONTOH-001']) }}" class="admin-table-action-link">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINASI (FIGMA 141:3139) --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ count($customers) }} dari 18 pelanggan aktif</span>
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
            Semua kontak dalam desain ini bersifat ilustratif. Batasi akses hanya untuk kebutuhan pelayanan studio.
        </p>
    </div>

    {{-- MODAL DIALOG TAMBAH PELANGGAN (FIGMA 141:3288) --}}
    <div class="admin-modal-overlay" id="modal-add-customer">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Tambah pelanggan</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-add-customer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">Rekam kontak pemesan untuk persiapan sesi studio</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nama lengkap *</label>
                    <input type="text" class="admin-form-input" placeholder="Nama pemesan" required>
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">ID Pelanggan (otomatis)</label>
                    <input type="text" class="admin-form-input font-mono" value="PLG-CONTOH-008" readonly>
                </div>
            </div>

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Email *</label>
                    <input type="email" class="admin-form-input" placeholder="email@example.com" required>
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">WhatsApp *</label>
                    <input type="tel" class="admin-form-input" placeholder="+62 8..." required>
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Catatan preferensi gaya / keluarga</label>
                <textarea class="admin-form-input" rows="3" placeholder="Contoh: Menyukai nuansa outdoor di pagi hari"></textarea>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-add-customer">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Data pelanggan baru berhasil ditambahkan.')">
                    Simpan pelanggan
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:3153) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-open-add-customer');
            const modal = document.getElementById('modal-add-customer');
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

