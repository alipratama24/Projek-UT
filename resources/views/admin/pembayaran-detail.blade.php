@extends('layouts.admin')

@section('title', 'Detail Bukti Pembayaran ' . $payment['id'] . ' — naro capture')
@section('breadcrumb', 'Pembayaran / Detail bukti pembayaran')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:1801) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Detail bukti pembayaran</h1>
            <p class="admin-page-subtitle">Tinjau sumber, nominal, dan invoice yang cocok sebelum mengambil keputusan.</p>
        </div>
        <a href="{{ route('admin.pembayaran') }}" class="admin-btn-outline">
            ← Kembali ke daftar
        </a>
    </div>

    {{-- STATUS BADGES (FIGMA 141:1805) --}}
    <div class="admin-status-summary-bar">
        <span class="status-indicator-item text-blue">● {{ $payment['id'] }}</span>
        <span class="status-indicator-item text-gold">● {{ $payment['status_badge'] }}</span>
    </div>

    {{-- GRID 2 KOLOM (FIGMA 141:1808) --}}
    <div class="admin-order-detail-grid">
        {{-- KOLOM KIRI: BUKTI & DATA TRANSFER --}}
        <div class="admin-order-detail-left">
            {{-- PANEL BUKTI YANG DIAJUKAN (FIGMA 141:1810) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Bukti yang diajukan</h2>

                @if(!empty($payment['has_file']) && !empty($payment['file_path']))
                    <div class="admin-doc-toolbar">
                        <strong class="doc-filename-text">{{ $payment['file_name'] }}</strong>
                        <span class="doc-meta-text">{{ $payment['file_meta'] }}</span>
                    </div>

                    @if(!empty($payment['is_image']))
                        <div class="admin-doc-preview-box" style="padding: 16px; background: #fafbfc; border: 1px solid #e1e7f0; display: flex; align-items: center; justify-content: center; min-height: 240px;">
                            <img src="{{ $payment['file_path'] }}" alt="{{ $payment['file_name'] }}" style="max-height: 290px; max-width: 100%; object-fit: contain; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                        </div>
                    @else
                        <div class="admin-doc-preview-box">
                            <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#3B5B9A" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <strong class="doc-preview-title">{{ $payment['file_name'] }}</strong>
                            <p class="doc-preview-desc">
                                Berkas bukti transfer resmi yang diunggah oleh pelanggan.
                            </p>
                            <span class="status-indicator-item text-blue">● BERKAS UNGGAHAN PELANGGAN</span>
                        </div>
                    @endif

                    <a href="{{ $payment['file_path'] }}" target="_blank" rel="noopener noreferrer" class="admin-btn-outline" style="text-decoration: none; text-align: center; display: block;">
                        Buka berkas di tab baru ↗
                    </a>
                @else
                    {{-- TAMPILAN NO DATA / TIDAK ADA BERKAS TANPA TOMBOL BUKA BERKAS --}}
                    <div class="admin-doc-preview-box" style="background: #FAFBFC; border: 1.5px dashed #CBD5E1; min-height: 260px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                        <strong class="doc-preview-title" style="color: #475569; font-size: 18px;">No Data / Tidak Ada Berkas</strong>
                        <p class="doc-preview-desc" style="color: #64748B;">
                            Pelanggan belum mengunggah berkas bukti pembayaran, atau pembayaran dilakukan secara tunai/manual di studio naro capture.
                        </p>
                        <span class="status-indicator-item text-muted" style="background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1;">● TIDAK ADA BERKAS</span>
                    </div>
                @endif
            </div>

            {{-- PANEL DATA TRANSFER YANG DIAJUKAN (FIGMA 141:1824) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Data transfer yang diajukan</h2>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Nama pengirim</label>
                        <p class="admin-meta-val">{{ $payment['sender'] }}</p>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Tanggal transfer</label>
                        <p class="admin-meta-val">{{ $payment['transfer_date'] }}</p>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Sumber pembayaran</label>
                        <p class="admin-meta-val">{{ $payment['bank_source'] }}</p>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Nominal diajukan</label>
                        <p class="admin-meta-val font-mono">{{ $payment['nominal'] }}</p>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan pengirim</label>
                    <p class="admin-meta-val">{{ $payment['sender_note'] }}</p>
                </div>

                <span class="admin-meta-hint">
                    {{ $payment['has_file'] ? 'Dikirim pelanggan · '.$payment['submitted_at'].' · Berkas tersimpan di server' : 'Data transaksi tercatat · '.$payment['submitted_at'] }}
                </span>
            </div>
        </div>

        {{-- KOLOM KANAN: INVOICE TERKAIT & RIWAYAT TINJAUAN --}}
        <div class="admin-order-detail-right">
            {{-- PANEL INVOICE TERKAIT (FIGMA 141:1835) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Invoice terkait</h2>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Invoice</label>
                    <p class="admin-meta-val font-mono">{{ $payment['invoice'] }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Pesanan</label>
                    <p class="admin-meta-val">{{ $payment['order_code'] }} · {{ $payment['sender'] }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Paket / total invoice</label>
                    <p class="admin-meta-val">{{ $payment['package_name'] }} · {{ $payment['nominal'] }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Jadwal diminta</label>
                    <p class="admin-meta-val">{{ $payment['session_schedule'] }}</p>
                </div>

                {{-- NOTICE KECOCOKAN NOMINAL (FIGMA 141:1841) --}}
                <div class="admin-alert-box">
                    <strong class="admin-alert-title">Nominal cocok dengan invoice</strong>
                    <p class="admin-alert-desc">Tetap periksa bukti dan kecocokan tujuan menggunakan data resmi studio.</p>
                </div>

                {{-- TOMBOL PEMICU MODAL VERIFIKASI (FIGMA 141:1844) --}}
                <button type="button" class="admin-btn-primary w-full" id="btn-open-verify-modal">
                    Verifikasi pembayaran
                </button>

                <button type="button" class="admin-btn-outline w-full" id="btn-open-request-fix-modal">
                    Minta perbaikan bukti
                </button>

                <button type="button" class="admin-btn-outline w-full" id="btn-open-edit-note-modal">
                    Edit catatan
                </button>
            </div>

            {{-- PANEL RIWAYAT TINJAUAN (FIGMA 141:1859) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Riwayat tinjauan</h2>

                <div class="admin-meta-field">
                    <span class="status-indicator-item text-muted">● {{ $payment['status_badge'] }}</span>
                </div>

                <div class="admin-review-history-steps">
                    @if(!empty($payment['internal_review_notes']))
                        <p class="review-step-text"><strong>Catatan internal:</strong> {{ $payment['internal_review_notes'] }}</p>
                    @endif
                    <p class="review-step-text">{{ $payment['submitted_at'] }} · Transaksi dicatat untuk {{ $payment['sender'] }}.</p>
                </div>

                <hr class="divider">

                <p class="admin-meta-disclaimer">
                    Keputusan verifikasi, permintaan perbaikan, dan catatan admin akan ditambahkan ke audit, bukan menimpa bukti asli.
                </p>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG VERIFIKASI PEMBAYARAN (FIGMA 141:1867) --}}
    <div class="admin-modal-overlay {{ request('modal') === 'verifikasi' ? 'is-open' : '' }}" id="modal-verify">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Verifikasi pembayaran?</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-verify">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <p class="admin-modal-subtitle">{{ $payment['sender'] }} · {{ $payment['id'] }}</p>

            <hr class="divider">

            <form action="{{ route('admin.pembayaran.verifikasi-action', ['id' => $payment['id']]) }}" method="POST">
                @csrf
                <div class="admin-meta-field">
                    <label class="admin-meta-label">Invoice / pesanan</label>
                    <p class="admin-meta-val font-mono">{{ $payment['invoice'] }} / {{ $payment['order_code'] }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nominal bukti / invoice</label>
                    <p class="admin-meta-val">{{ $payment['nominal'] }} / {{ $payment['nominal'] }} · Cocok</p>
                </div>

                <label class="admin-checkbox-label">
                    <input type="checkbox" id="verifyAgreementCheckbox" checked required>
                    <span>Saya telah meninjau nominal, pengirim, bukti, dan kecocokan referensi invoice menggunakan data resmi studio.</span>
                </label>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan internal (opsional)</label>
                    <input type="text" name="catatan" class="admin-form-input" value="{{ $payment['has_file'] ? 'Bukti transfer cocok dengan nominal invoice.' : 'Pembayaran dikonfirmasi sesuai transaksi studio naro capture.' }}">
                </div>

                <div class="admin-alert-box alert-warning-box">
                    <strong class="admin-alert-title text-gold">Pembayaran terverifikasi ≠ jadwal dikonfirmasi</strong>
                    <p class="admin-alert-desc text-gold">
                        Setelah disimpan, pembayaran menjadi terverifikasi; jadwal masih memerlukan konfirmasi terpisah di Pesanan. Riwayat keuangan tidak dapat dihapus.
                    </p>
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-verify">Batal</button>
                    <button type="submit" id="btnSubmitVerify" class="admin-btn-primary">Ya, verifikasi pembayaran</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DIALOG MINTA PERBAIKAN BUKTI --}}
    <div class="admin-modal-overlay" id="modal-request-fix">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.pembayaran.minta-perbaikan', ['id' => $payment['id']]) }}" method="POST">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Minta perbaikan bukti?</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-request-fix">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">{{ $payment['sender'] }} · {{ $payment['id'] }}</p>

                <hr class="divider">

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Alasan perbaikan *</label>
                    <select name="alasan" class="admin-form-input">
                        <option value="Nominal atau nomor rekening tujuan tidak terbaca jelas" selected>Nominal atau nomor rekening tujuan tidak terbaca jelas</option>
                        <option value="Nominal transfer tidak sesuai dengan total invoice">Nominal transfer tidak sesuai dengan total invoice</option>
                        <option value="Struk tidak memuat waktu atau referensi bank yang valid">Struk tidak memuat waktu atau referensi bank yang valid</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Pesan instruksi untuk pelanggan *</label>
                    <textarea name="pesan_instruksi" class="admin-form-input" rows="3">Mohon unggah kembali bukti transfer yang menampilkan tanggal, nama penerima, dan nominal secara lengkap.</textarea>
                </div>

                <p class="admin-meta-disclaimer">
                    Status pembayaran pelanggan akan berubah menjadi “Perlu perbaikan bukti”. Pelanggan dapat mengunggah struk baru di halaman Cek Pesanan.
                </p>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-request-fix">Batal</button>
                    <button type="submit" class="admin-btn-primary" style="background:#94671E; border-color:#94671E;">
                        Kirim permintaan perbaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DIALOG EDIT CATATAN --}}
    <div class="admin-modal-overlay" id="modal-edit-note">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.pembayaran.catatan', ['id' => $payment['id']]) }}" method="POST">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Edit catatan pembayaran</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-edit-note">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">{{ $payment['sender'] }} · {{ $payment['id'] }}</p>

                <hr class="divider">

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan internal admin</label>
                    <textarea name="catatan" class="admin-form-input" rows="4" placeholder="Tulis catatan internal admin di sini...">{{ $payment['internal_review_notes'] ?? '' }}</textarea>
                </div>

                <span class="admin-meta-hint">
                    Catatan ini hanya dapat dilihat oleh tim admin dan disimpan ke audit transaksi.
                </span>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-edit-note">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Simpan catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:1865) --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Sistem Manajemen Pembayaran · Akses terbatas admin
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

            bindModal('btn-open-verify-modal', 'modal-verify');
            bindModal('btn-open-request-fix-modal', 'modal-request-fix');
            bindModal('btn-open-edit-note-modal', 'modal-edit-note');

            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-close');
                    const targetModal = document.getElementById(targetId);
                    if (targetModal) targetModal.classList.remove('is-open');
                });
            });

            const verifyCheckbox = document.getElementById('verifyAgreementCheckbox');
            const verifyBtn = document.getElementById('btnSubmitVerify');
            if (verifyCheckbox && verifyBtn) {
                const updateVerifyState = () => {
                    verifyBtn.disabled = !verifyCheckbox.checked;
                };
                verifyCheckbox.addEventListener('change', updateVerifyState);
                updateVerifyState();
            }
        });
    </script>
@endsection

