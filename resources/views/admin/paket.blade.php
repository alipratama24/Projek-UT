@extends('layouts.admin')

@section('title', 'Paket Layanan — Clara Hayes Photography')
@section('breadcrumb', 'Paket Layanan / Paket layanan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:3580) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Paket layanan</h1>
            <p class="admin-page-subtitle">3 paket aktif · Harga, cakupan sesi, dan informasi untuk pemesanan baru.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-add-package">
            Tambah paket
        </button>
    </div>

    {{-- FILTER & SEARCH (FIGMA 141:3591) --}}
    <div class="admin-filter-bar">
        <div class="admin-search-input-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Cari paket layanan" class="admin-search-input">
        </div>

        <div class="admin-select-dropdown">
            <span>Paket aktif</span>
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
            <span>Semua versi</span>
        </div>
    </div>

    {{-- 3 KARTU PAKET LAYANAN (FIGMA 141:3601) --}}
    <div class="admin-packages-cards-grid">
        {{-- PAKET 1: POTRET PRIBADI --}}
        <div class="admin-panel admin-package-mgmt-card">
            <img src="{{ asset('images/paket-1.png') }}" alt="Potret Pribadi" class="admin-package-mgmt-img">
            <div class="admin-package-mgmt-body">
                <div class="admin-package-card-header">
                    <span class="status-indicator-item text-green">● Aktif</span>
                    <span class="order-code-mono text-muted">PKT-CONTOH-001</span>
                </div>
                <h2 class="admin-package-mgmt-title">Potret Pribadi</h2>
                <p class="admin-package-mgmt-desc">
                    Sesi singkat untuk menangkap diri Anda dengan santai dan alami, cocok untuk profil pribadi atau kenangan pribadi.
                </p>
                <strong class="admin-package-mgmt-price">Rp4.500.000</strong>
                <span class="admin-package-mgmt-meta">45 menit · 20 foto · Per sesi</span>

                <hr class="divider">

                <ul class="features-checklist">
                    <li><span class="check-mark">✓</span> Sesi 45 menit dengan panduan ringan</li>
                    <li><span class="check-mark">✓</span> Satu lokasi pilihan Anda</li>
                    <li><span class="check-mark">✓</span> 20 foto diedit dengan hati-hati</li>
                    <li><span class="check-mark">✓</span> Galeri online pribadi</li>
                    <li><span class="check-mark">✓</span> Izin cetak pribadi</li>
                </ul>

                <hr class="divider">

                <div class="admin-btn-group-2">
                    <button type="button" class="admin-btn-outline flex-1" onclick="alert('Lihat detail paket.')">Detail</button>
                    <button type="button" class="admin-btn-outline flex-1" onclick="alert('Edit paket.')">Edit paket</button>
                </div>
            </div>
        </div>

        {{-- PAKET 2: CERITA KELUARGA --}}
        <div class="admin-panel admin-package-mgmt-card">
            <img src="{{ asset('images/paket-2.png') }}" alt="Cerita Keluarga" class="admin-package-mgmt-img">
            <div class="admin-package-mgmt-body">
                <div class="admin-package-card-header">
                    <span class="status-indicator-item text-green">● Aktif</span>
                    <span class="order-code-mono text-muted">PKT-CONTOH-002</span>
                </div>
                <h2 class="admin-package-mgmt-title">Cerita Keluarga</h2>
                <p class="admin-package-mgmt-desc">
                    Untuk keluarga, pasangan, dan momen-momen yang ingin dikenang bersama.
                </p>
                <strong class="admin-package-mgmt-price">Rp9.500.000</strong>
                <span class="admin-package-mgmt-meta">90 menit · 50 foto · Per sesi</span>

                <hr class="divider">

                <ul class="features-checklist">
                    <li><span class="check-mark">✓</span> Sesi 90 menit dengan narasi yang lebih dalam</li>
                    <li><span class="check-mark">✓</span> Dua lokasi dan dua ganti kostum</li>
                    <li><span class="check-mark">✓</span> 50 foto diedit dengan hati-hati</li>
                    <li><span class="check-mark">✓</span> Panggilan perencanaan & panduan styling</li>
                    <li><span class="check-mark">✓</span> Galeri pribadi + izin cetak</li>
                </ul>

                <hr class="divider">

                <div class="admin-btn-group-2">
                    <button type="button" class="admin-btn-outline flex-1" onclick="alert('Lihat detail paket.')">Detail</button>
                    <button type="button" class="admin-btn-outline flex-1" onclick="alert('Edit paket.')">Edit paket</button>
                </div>
            </div>
        </div>

        {{-- PAKET 3: EDITORIAL BISNIS --}}
        <div class="admin-panel admin-package-mgmt-card">
            <img src="{{ asset('images/paket-3.png') }}" alt="Editorial Bisnis" class="admin-package-mgmt-img">
            <div class="admin-package-mgmt-body">
                <div class="admin-package-card-header">
                    <span class="status-indicator-item text-green">● Aktif</span>
                    <span class="order-code-mono text-muted">PKT-CONTOH-003</span>
                </div>
                <h2 class="admin-package-mgmt-title">Editorial Bisnis</h2>
                <p class="admin-package-mgmt-desc">
                    Fotografi yang disusun untuk merek lokal, kreator, dan bisnis yang ingin terlihat otentik.
                </p>
                <strong class="admin-package-mgmt-price">Rp18.000.000</strong>
                <span class="admin-package-mgmt-meta">Setengah hari · 80 foto · Per sesi</span>

                <hr class="divider">

                <ul class="features-checklist">
                    <li><span class="check-mark">✓</span> Sesi setengah hari untuk konten bisnis</li>
                    <li><span class="check-mark">✓</span> Beberapa lokasi & ganti kostum</li>
                    <li><span class="check-mark">✓</span> 80 foto diedit untuk media sosial & iklan</li>
                    <li><span class="check-mark">✓</span> Arahan kreatif & moodboard</li>
                    <li><span class="check-mark">✓</span> Izin komersial untuk merek</li>
                </ul>

                <hr class="divider">

                <div class="admin-btn-group-2">
                    <button type="button" class="admin-btn-outline flex-1" onclick="alert('Lihat detail paket.')">Detail</button>
                    <button type="button" class="admin-btn-outline flex-1" onclick="alert('Edit paket.')">Edit paket</button>
                </div>
            </div>
        </div>
    </div>

    {{-- FEEDBACK NOTICE (FIGMA 141:3680) --}}
    <div class="admin-alert-box alert-success-box">
        <strong class="admin-alert-title text-green">Paket tersimpan</strong>
        <p class="admin-alert-desc text-green">
            Contoh feedback: deskripsi diperbarui. Pesanan yang sudah dibuat tetap memakai harga dan cakupan saat dipesan.
        </p>
    </div>

    {{-- MODAL DIALOG TAMBAH PAKET (FIGMA 141:3784) --}}
    <div class="admin-modal-overlay" id="modal-add-package">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Tambah paket layanan</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-add-package">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">Konfigurasi paket sesi baru yang dapat dipilih pelanggan</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nama paket *</label>
                    <input type="text" class="admin-form-input" placeholder="Contoh: Sesi Wisuda" required>
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Harga sesi *</label>
                    <input type="text" class="admin-form-input font-mono" placeholder="Rp 0" required>
                </div>
            </div>

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Durasi sesi (menit) *</label>
                    <input type="number" class="admin-form-input" value="60" required>
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Target foto diedit *</label>
                    <input type="number" class="admin-form-input" value="30" required>
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Deskripsi singkat *</label>
                <textarea class="admin-form-input" rows="2" placeholder="Deskripsi untuk kartu paket pemesanan"></textarea>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Fasilitas yang didapat (satu baris per poin)</label>
                <textarea class="admin-form-input" rows="3" placeholder="Satu lokasi studio&#10;30 foto diedit resolusi penuh&#10;Galeri online pribadi"></textarea>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-add-package">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Paket layanan baru berhasil ditambahkan.')">
                    Simpan paket
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:3683) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-open-add-package');
            const modal = document.getElementById('modal-add-package');
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

