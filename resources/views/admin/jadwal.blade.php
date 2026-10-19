@extends('layouts.admin')

@section('title', 'Kalender Studio — Clara Hayes Photography')
@section('breadcrumb', 'Jadwal / Kalender studio')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:2454) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Kalender studio</h1>
            <p class="admin-page-subtitle">Pisahkan ketersediaan, permintaan tentatif, dan sesi yang disetujui. Zona waktu WIB.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-add-slot">
            Tambah slot
        </button>
    </div>

    {{-- FILTER & SEARCH BAR (FIGMA 141:2465) --}}
    <div class="admin-filter-bar">
        <div class="admin-search-input-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Cari sesi atau pelanggan" class="admin-search-input">
        </div>

        <div class="admin-select-dropdown">
            <span>Semua jenis slot</span>
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

    {{-- GRID KALENDER: KIRI (KALENDER BULAN) / KANAN (DETAIL TANGGAL DIPILIH) --}}
    <div class="admin-calendar-layout-grid">
        {{-- KOLOM KIRI: KALENDER INTERAKTIF --}}
        <div class="admin-panel admin-calendar-main-panel">
            {{-- NAVIGASI BULAN & PILIHAN TAMPILAN --}}
            <div class="admin-calendar-nav-bar">
                <h2 class="admin-calendar-month-heading">Oktober 2026</h2>
                <div class="admin-calendar-nav-actions">
                    <button type="button" class="admin-btn-outline btn-sm">‹</button>
                    <button type="button" class="admin-btn-outline btn-sm">Hari ini</button>
                    <button type="button" class="admin-btn-outline btn-sm">›</button>
                </div>
            </div>

            {{-- TAB TAMPILAN BULAN / MINGGU --}}
            <div class="admin-tabs" style="border-bottom: none; margin-bottom: 8px;">
                <span class="admin-tab-item is-active">Bulan</span>
                <span class="admin-tab-item">Minggu</span>
            </div>

            {{-- KEPALA HARI (SEN - MIN) --}}
            <div class="admin-calendar-weekdays">
                <span>Sen</span>
                <span>Sel</span>
                <span>Rab</span>
                <span>Kam</span>
                <span>Jum</span>
                <span>Sab</span>
                <span>Min</span>
            </div>

            {{-- GRID BULAN (7 KOLOM X 5 BARIS) --}}
            <div class="admin-calendar-month-grid">
                {{-- MINGGU 1 --}}
                <div class="calendar-day-cell is-muted"><span class="day-num">28</span></div>
                <div class="calendar-day-cell is-muted"><span class="day-num">29</span></div>
                <div class="calendar-day-cell is-muted"><span class="day-num">30</span></div>
                <div class="calendar-day-cell"><span class="day-num">1</span></div>
                <div class="calendar-day-cell"><span class="day-num">2</span></div>
                <div class="calendar-day-cell"><span class="day-num">3</span></div>
                <div class="calendar-day-cell"><span class="day-num">4</span></div>

                {{-- MINGGU 2 --}}
                <div class="calendar-day-cell"><span class="day-num">5</span></div>
                <div class="calendar-day-cell"><span class="day-num">6</span></div>
                <div class="calendar-day-cell"><span class="day-num">7</span></div>
                <div class="calendar-day-cell"><span class="day-num">8</span></div>
                <div class="calendar-day-cell"><span class="day-num">9</span></div>
                <div class="calendar-day-cell">
                    <span class="day-num">10</span>
                    <div class="cal-event-pill event-approved">09.00 Nadia<br>Disetujui</div>
                </div>
                <div class="calendar-day-cell"><span class="day-num">11</span></div>

                {{-- MINGGU 3 --}}
                <div class="calendar-day-cell">
                    <span class="day-num">12</span>
                    <div class="cal-event-pill event-approved">15.30 Dimas<br>Disetujui</div>
                </div>
                <div class="calendar-day-cell"><span class="day-num">13</span></div>
                <div class="calendar-day-cell"><span class="day-num">14</span></div>
                <div class="calendar-day-cell">
                    <span class="day-num">15</span>
                    <div class="cal-event-pill event-available">09.00–10.30<br>Tersedia</div>
                </div>
                <div class="calendar-day-cell"><span class="day-num">16</span></div>
                <div class="calendar-day-cell is-selected">
                    <span class="day-num">17</span>
                    <div class="cal-event-pill event-tentative">15.30 Ayu<br>Tentatif</div>
                </div>
                <div class="calendar-day-cell"><span class="day-num">18</span></div>

                {{-- MINGGU 4 --}}
                <div class="calendar-day-cell"><span class="day-num">19</span></div>
                <div class="calendar-day-cell">
                    <span class="day-num">20</span>
                    <div class="cal-event-pill event-tentative">09.00 Ruang<br>Tentatif</div>
                </div>
                <div class="calendar-day-cell">
                    <span class="day-num">21</span>
                    <div class="cal-event-pill event-blocked">Blok studio<br>Tidak tersedia</div>
                </div>
                <div class="calendar-day-cell"><span class="day-num">22</span></div>
                <div class="calendar-day-cell"><span class="day-num">23</span></div>
                <div class="calendar-day-cell">
                    <span class="day-num">24</span>
                    <div class="cal-event-pill event-approved">15.30 Maya<br>Disetujui</div>
                </div>
                <div class="calendar-day-cell"><span class="day-num">25</span></div>

                {{-- MINGGU 5 --}}
                <div class="calendar-day-cell"><span class="day-num">26</span></div>
                <div class="calendar-day-cell"><span class="day-num">27</span></div>
                <div class="calendar-day-cell"><span class="day-num">28</span></div>
                <div class="calendar-day-cell"><span class="day-num">29</span></div>
                <div class="calendar-day-cell"><span class="day-num">30</span></div>
                <div class="calendar-day-cell"><span class="day-num">31</span></div>
                <div class="calendar-day-cell is-muted"><span class="day-num">1</span></div>
            </div>

            {{-- LEGENDA INDIKATOR (FIGMA 141:2522) --}}
            <div class="admin-calendar-legend-bar">
                <span class="legend-item text-green">● Sesi disetujui</span>
                <span class="legend-item text-gold">● Permintaan tentatif</span>
                <span class="legend-item text-blue">● Tersedia</span>
                <span class="legend-item text-muted">● Blok studio</span>
            </div>
        </div>

        {{-- KOLOM KANAN: DETAIL TANGGAL DIPILIH (FIGMA 141:2527) --}}
        <div class="admin-calendar-sidebar-detail">
            {{-- PANEL SESI TANGGAL 17 OKTOBER --}}
            <div class="admin-panel">
                <h3 class="admin-panel-title">Sabtu, 17 Oktober</h3>
                <span class="status-indicator-item text-blue">● Tanggal dipilih</span>

                <div class="admin-selected-session-info">
                    <strong class="selected-customer-name">Ayu Pramesti</strong>
                    
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Pesanan / paket</label>
                        <p class="admin-meta-val font-mono">CH-CONTOH-001 · Cerita Keluarga</p>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu diminta</label>
                        <p class="admin-meta-val">15.30–17.00 WIB · 90 menit</p>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Lokasi</label>
                        <p class="admin-meta-val">Rumah keluarga, Jakarta Selatan & taman terdekat</p>
                    </div>

                    <span class="status-indicator-item text-gold">● Tentatif · Belum dikonfirmasi</span>
                </div>

                {{-- NOTICE TINJAU KETERSEDIAAN (FIGMA 141:2536) --}}
                <div class="admin-alert-box">
                    <strong class="admin-alert-title">Tinjau ketersediaan</strong>
                    <p class="admin-alert-desc">
                        Bukti pembayaran masih diperiksa. Slot permintaan bukan sesi yang telah disetujui.
                    </p>
                </div>

                <a href="{{ route('admin.pesanan.detail', ['id' => 'CH-CONTOH-001']) }}" class="admin-btn-outline w-full text-center">
                    Buka detail pesanan
                </a>
            </div>

            {{-- PANEL SLOT KETERSEDIAAN (FIGMA 141:2543) --}}
            <div class="admin-panel">
                <h3 class="admin-panel-title">Slot ketersediaan</h3>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Slot belum terpakai</label>
                    <p class="admin-meta-val">15 Okt 2026 · 09.00–10.30 WIB</p>
                </div>

                <span class="status-indicator-item text-blue">● Tersedia</span>

                <div class="admin-btn-group-2">
                    <button type="button" class="admin-btn-outline flex-1" id="btn-open-edit-slot">
                        Edit
                    </button>
                    <button type="button" class="admin-btn-outline btn-outline-danger flex-1" id="btn-open-delete-slot">
                        Hapus
                    </button>
                </div>

                <p class="admin-meta-hint">
                    Sesi terpesan tidak dapat dihapus sebagai slot kosong.
                </p>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG TAMBAH SLOT / BLOKIR (FIGMA 141:2559) --}}
    <div class="admin-modal-overlay" id="modal-add-slot">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Tambah slot waktu</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-add-slot">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">Buka ketersediaan sesi baru atau blokir studio</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Tanggal *</label>
                    <input type="date" class="admin-form-input" value="2026-10-18">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Jenis slot *</label>
                    <select class="admin-form-input">
                        <option selected>Slot Tersedia untuk Pelanggan</option>
                        <option>Blokir Studio (Libur / Maintenance)</option>
                    </select>
                </div>
            </div>

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Waktu mulai *</label>
                    <input type="time" class="admin-form-input" value="09:00">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Waktu selesai *</label>
                    <input type="time" class="admin-form-input" value="10:30">
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Catatan internal (opsional)</label>
                <input type="text" class="admin-form-input" placeholder="Contoh: Khusus potret studio">
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-add-slot">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Slot jadwal berhasil ditambahkan.')">
                    Simpan slot
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG EDIT SLOT (FIGMA 141:2773) --}}
    <div class="admin-modal-overlay" id="modal-edit-slot">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Edit slot ketersediaan</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-edit-slot">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">15 Okt 2026 · 09.00–10.30 WIB</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Waktu mulai</label>
                    <input type="time" class="admin-form-input" value="09:00">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Waktu selesai</label>
                    <input type="time" class="admin-form-input" value="10:30">
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Status ketersediaan</label>
                <select class="admin-form-input">
                    <option selected>Tersedia</option>
                    <option>Blokir Studio</option>
                </select>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-edit-slot">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Perubahan slot berhasil disimpan.')">
                    Simpan perubahan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG HAPUS SLOT (FIGMA 141:2928) --}}
    <div class="admin-modal-overlay" id="modal-delete-slot">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Hapus slot ketersediaan?</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-delete-slot">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">15 Okt 2026 · 09.00–10.30 WIB · Tersedia</p>

            <hr class="divider">

            <p class="admin-meta-val">
                Slot waktu ini akan dihapus dari pilihan jadwal yang dapat dipilih pelanggan saat pemesanan baru.
            </p>

            <div class="admin-alert-box alert-warning-box">
                <strong class="admin-alert-title text-gold">Tidak mempengaruhi sesi terpesan</strong>
                <p class="admin-alert-desc text-gold">
                    Slot ini belum terikat dengan pesanan mana pun.
                </p>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-delete-slot">Batal</button>
                <button type="button" class="admin-btn-primary" style="background:#D32F2F; border-color:#D32F2F;" onclick="alert('Slot berhasil dihapus.')">
                    Hapus slot
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:2557) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bindModal = (btnId, modalId) => {
                const btn = document.getElementById(btnId);
                const modal = document.getElementById(modalId);
                if (btn && modal) {
                    btn.addEventListener('click', () => modal.classList.add('is-open'));
                }
            };

            bindModal('btn-open-add-slot', 'modal-add-slot');
            bindModal('btn-open-edit-slot', 'modal-edit-slot');
            bindModal('btn-open-delete-slot', 'modal-delete-slot');

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

