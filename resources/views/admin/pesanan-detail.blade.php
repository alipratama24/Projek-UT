@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order['code'] . ' — Clara Hayes Photography')
@section('breadcrumb', 'Pesanan / Detail pesanan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:1125) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Detail pesanan</h1>
            <p class="admin-page-subtitle">{{ $order['customer'] }} · Dibuat {{ $order['created_date'] }} · Record ilustrasi</p>
        </div>
        <a href="{{ route('admin.pesanan') }}" class="admin-btn-outline">
            ← Kembali ke daftar
        </a>
    </div>

    {{-- BARIS KONTEN STATUS BADGES (FIGMA 141:1129) --}}
    <div class="admin-status-summary-bar">
        <span class="status-indicator-item text-blue">● {{ $order['code'] }}</span>
        <span class="status-indicator-item text-gold">● {{ $order['session_status'] }}</span>
        <span class="status-indicator-item text-blue">● {{ $order['payment_summary'] }}</span>
    </div>

    {{-- GRID 2 KOLOM: KIRI (DETAIL SESI & PELANGGAN) / KANAN (PEMBAYARAN & LINIMASA) --}}
    <div class="admin-order-detail-grid">
        {{-- KOLOM KIRI --}}
        <div class="admin-order-detail-left">
            {{-- PANEL DETAIL SESI (FIGMA 141:1135) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Detail sesi</h2>

                <div class="admin-package-card">
                    <img src="{{ asset('images/' . $order['package_image']) }}" alt="{{ $order['package_name'] }}" class="package-card-thumb">
                    <div class="package-card-info">
                        <h3 class="package-card-name">{{ $order['package_name'] }}</h3>
                        <p class="package-card-desc">{{ $order['package_desc'] }}</p>
                        <strong class="package-card-price">{{ $order['package_price'] }} / sesi</strong>
                    </div>
                </div>

                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Tanggal yang diminta</label>
                        <p class="admin-meta-val">{{ $order['date_formatted'] }}</p>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu · durasi</label>
                        <p class="admin-meta-val">{{ $order['time_formatted'] }}</p>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Lokasi sesi</label>
                    <p class="admin-meta-val">{{ $order['location'] }}</p>
                    <span class="admin-meta-hint">Alamat akhir dibahas saat perencanaan.</span>
                </div>

                <div class="admin-package-features-block">
                    <h4 class="features-block-title">Termasuk dalam harga yang dipesan</h4>
                    <ul class="features-checklist">
                        @foreach($order['features'] as $feature)
                            <li>
                                <span class="check-mark">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- PANEL PELANGGAN & CATATAN (FIGMA 141:1151) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Pelanggan & catatan</h2>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Pemesan</label>
                        <p class="admin-meta-val">{{ $order['customer'] }} · {{ $order['customer_code'] }}</p>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Kontak ilustrasi</label>
                        <p class="admin-meta-val">{{ $order['email'] }} · {{ $order['phone'] }}</p>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan pelanggan</label>
                    <p class="admin-meta-val">{{ $order['customer_note'] }}</p>
                </div>

                <div class="admin-internal-note-box">
                    <label class="internal-note-label">Catatan internal</label>
                    <p class="internal-note-text">{{ $order['internal_note'] }}</p>
                </div>
                <span class="admin-meta-hint">Hanya admin · Clara, 6 Okt 2026 09.10 WIB</span>
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN DAN TINDAK LANJUT (FIGMA 141:1160) --}}
        <div class="admin-order-detail-right">
            {{-- PANEL PEMBAYARAN & INVOICE (FIGMA 141:1161) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Pembayaran & invoice</h2>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nomor invoice</label>
                    <p class="admin-meta-val font-mono">{{ $order['invoice_number'] }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Harga dipesan / total</label>
                    <p class="admin-meta-val">{{ $order['package_price'] }} · 1 sesi</p>
                </div>

                <div class="admin-meta-field">
                    <span class="status-indicator-item text-gold">● {{ $order['payment_status_badge'] }}</span>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nominal diajukan</label>
                    <p class="admin-meta-val">{{ $order['submitted_nominal'] }} · {{ $order['submitted_date'] }}</p>
                </div>

                <a href="{{ route('admin.pembayaran.detail', ['id' => $order['code']]) }}" class="admin-btn-outline w-full text-center">
                    Lihat bukti pembayaran
                </a>

                {{-- NOTICE JADWAL BELUM DISETUJUI (FIGMA 141:1171) --}}
                <div class="admin-alert-box">
                    <strong class="admin-alert-title">Jadwal belum disetujui</strong>
                    <p class="admin-alert-desc">Verifikasi pembayaran tidak otomatis mengonfirmasi sesi.</p>
                </div>

                {{-- TOMBOL KONFIRMASI SESI (FIGMA 141:1174) --}}
                <form action="{{ route('admin.pesanan.konfirmasi-sesi', ['id' => $order['code']]) }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-btn-primary w-full">
                        Konfirmasi sesi
                    </button>
                </form>

                <p class="admin-meta-disclaimer">
                    Tinjau ketersediaan dan pembayaran sebelum menyetujui. Konfirmasi mengubah status yang dilihat pelanggan.
                </p>
            </div>

            {{-- PANEL LINIMASA PESANAN (FIGMA 141:1182) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Linimasa pesanan</h2>

                <div class="admin-order-timeline">
                    <div class="timeline-step is-complete">
                        <span class="timeline-check">✓</span>
                        <div class="timeline-text">
                            <strong>5 Okt · Pesanan dibuat</strong>
                        </div>
                    </div>
                    <div class="timeline-step is-complete">
                        <span class="timeline-check">✓</span>
                        <div class="timeline-text">
                            <strong>5 Okt · Bukti diterima</strong>
                        </div>
                    </div>
                    <div class="timeline-step is-pending">
                        <span class="timeline-circle">○</span>
                        <div class="timeline-text">
                            <span>Verifikasi pembayaran · Menunggu</span>
                        </div>
                    </div>
                    <div class="timeline-step is-pending">
                        <span class="timeline-circle">○</span>
                        <div class="timeline-text">
                            <span>Konfirmasi jadwal · Menunggu</span>
                        </div>
                    </div>
                    <div class="timeline-step is-pending">
                        <span class="timeline-circle">○</span>
                        <div class="timeline-text">
                            <span>Sesi selesai · Belum berlangsung</span>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                <div class="admin-btn-group-2">
                    <button type="button" class="admin-btn-outline flex-1" id="btn-open-edit-order">
                        Edit
                    </button>
                    <button type="button" class="admin-btn-outline btn-outline-danger flex-1" id="btn-open-cancel-order">
                        Batalkan
                    </button>
                </div>

                <button type="button" class="admin-btn-outline w-full" id="btn-open-archive-order">
                    Arsipkan setelah selesai
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG BATALKAN PESANAN (FIGMA 141:1411) --}}
    <div class="admin-modal-overlay" id="modal-cancel-order">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Batalkan pesanan?</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-cancel-order">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">{{ $order['code'] }} · {{ $order['customer'] }} · {{ $order['package_name'] }}</p>

            <hr class="divider">

            <div class="admin-meta-field">
                <label class="admin-meta-label">Sesi yang akan dibatalkan</label>
                <p class="admin-meta-val">{{ $order['date_formatted'] }} · {{ $order['time_formatted'] }}</p>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Alasan pembatalan *</label>
                <textarea class="admin-form-input" rows="3" required>Pelanggan meminta membatalkan rencana sesi.</textarea>
            </div>

            <div class="admin-alert-box alert-warning-box">
                <strong class="admin-alert-title text-gold">Pembayaran ditinjau terpisah</strong>
                <p class="admin-alert-desc text-gold">
                    Bukti {{ $order['submitted_nominal'] }} belum terverifikasi. Pembatalan tidak otomatis mengembalikan dana. Tinjau kelayakan dan catat ketentuan refund yang disetujui.
                </p>
            </div>

            <p class="admin-meta-disclaimer">
                Status pelanggan menjadi “Dibatalkan”. Jadwal permintaan dilepas; invoice, bukti dan riwayat audit tetap tersimpan.
            </p>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-cancel-order">Batal</button>
                <button type="button" class="admin-btn-primary" style="background:#D32F2F; border-color:#D32F2F;" onclick="alert('Pesanan {{ $order['code'] }} berhasil dibatalkan.')">
                    Batalkan pesanan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG ARSIPKAN PESANAN (FIGMA 141:1533) --}}
    <div class="admin-modal-overlay" id="modal-archive-order">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Arsipkan pesanan selesai?</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-archive-order">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">{{ $order['code'] }} · {{ $order['customer'] }} · {{ $order['package_name'] }}</p>

            <hr class="divider">

            <div class="admin-meta-field">
                <label class="admin-meta-label">Status saat ini</label>
                <p class="admin-meta-val">Sesi selesai · 17 Oktober 2026 · Pembayaran terverifikasi</p>
            </div>

            <div class="admin-alert-box alert-success-box">
                <strong class="admin-alert-title text-green">Riwayat tetap tersimpan</strong>
                <p class="admin-alert-desc text-green">
                    Pesanan berpindah ke tab Arsip. Invoice {{ $order['invoice_number'] }}, bukti pembayaran, dan audit tidak dihapus.
                </p>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Catatan arsip</label>
                <input type="text" class="admin-form-input" value="Sesi selesai dan seluruh administrasi ditutup.">
            </div>

            <p class="admin-meta-disclaimer">
                Pesanan aktif tidak bisa dihapus atau diarsipkan sebelum penyelesaian ditinjau. Arsip tidak memicu pembayaran atau refund.
            </p>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-archive-order">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Pesanan {{ $order['code'] }} berhasil dipindahkan ke arsip.')">
                    Arsipkan pesanan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG EDIT PESANAN (FIGMA 141:1318) --}}
    <div class="admin-modal-overlay" id="modal-edit-order">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Edit pesanan</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-edit-order">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">{{ $order['code'] }} · {{ $order['customer'] }}</p>

            <hr class="divider">

            <div class="admin-meta-row-2">
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Tanggal sesi</label>
                    <input type="text" class="admin-form-input" value="17 Oktober 2026">
                </div>
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Waktu sesi</label>
                    <input type="text" class="admin-form-input" value="15.30–17.00 WIB">
                </div>
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Lokasi sesi</label>
                <input type="text" class="admin-form-input" value="{{ $order['location'] }}">
            </div>

            <div class="admin-meta-field">
                <label class="admin-meta-label">Catatan internal admin</label>
                <textarea class="admin-form-input" rows="3">{{ $order['internal_note'] }}</textarea>
            </div>

            <hr class="divider">

            <div class="admin-modal-actions">
                <button type="button" class="admin-btn-outline" data-close="modal-edit-order">Batal</button>
                <button type="button" class="admin-btn-primary" onclick="alert('Perubahan pesanan berhasil disimpan.')">
                    Simpan perubahan
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:1199) --}}
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

            bindModal('btn-open-cancel-order', 'modal-cancel-order');
            bindModal('btn-open-archive-order', 'modal-archive-order');
            bindModal('btn-open-edit-order', 'modal-edit-order');

            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-close');
                    const targetModal = document.getElementById(targetId);
                    if (targetModal) targetModal.classList.remove('is-open');
                });
            });
        });
    </script>
@endsection

