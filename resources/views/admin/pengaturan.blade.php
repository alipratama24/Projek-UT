@extends('layouts.admin')

@section('title', 'Pengaturan Studio — Clara Hayes Photography')
@section('breadcrumb', 'Pengaturan / Pengaturan studio')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:4713) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pengaturan studio</h1>
            <p class="admin-page-subtitle">Profil, kebijakan, dan preferensi komunikasi untuk operasional sehari-hari.</p>
        </div>
    </div>

    {{-- FEEDBACK NOTICE (FIGMA 141:4717) --}}
    <div class="admin-alert-box alert-success-box">
        <strong class="admin-alert-title text-green">Pengaturan berhasil disimpan</strong>
        <p class="admin-alert-desc text-green">
            Profil dan preferensi notifikasi diperbarui pada 6 Oktober 2026, 09.20 WIB. Data pembayaran resmi belum dikonfigurasi.
        </p>
    </div>

    {{-- GRID PENGATURAN (KIRI: FORMULIR UTAMA / KANAN: NOTIFIKASI & AKSES) --}}
    <form action="{{ route('admin.pengaturan.save') }}" method="POST">
        @csrf
        <div class="admin-order-detail-grid">
            {{-- KOLOM KIRI: FORMULIR UTAMA --}}
            <div class="admin-order-detail-left">
                {{-- 1. STUDIO & PROFIL ADMIN (FIGMA 141:4722) --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Studio & profil admin</h2>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Nama studio *</label>
                            <input type="text" name="studio_name" class="admin-form-input" value="Clara Hayes Photography" required>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Nama admin *</label>
                            <input type="text" name="admin_name" class="admin-form-input" value="Clara Hayes" required>
                        </div>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Email studio *</label>
                            <input type="email" name="studio_email" class="admin-form-input" value="hello@clarahayes.studio" required>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">WhatsApp studio</label>
                            <input type="text" name="studio_whatsapp" class="admin-form-input" value="Belum dikonfigurasi">
                        </div>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Lokasi studio</label>
                            <input type="text" name="studio_location" class="admin-form-input" value="Jakarta, Indonesia">
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Zona waktu *</label>
                            <select name="timezone" class="admin-form-input">
                                <option value="WIB" selected>WIB · UTC+7</option>
                                <option value="WITA">WITA · UTC+8</option>
                                <option value="WIT">WIT · UTC+9</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. PEMESANAN & PEMBAYARAN (FIGMA 141:4740) --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Pemesanan & pembayaran</h2>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Penerimaan pemesanan</label>
                            <select class="admin-form-input">
                                <option selected>Permintaan jadwal · Tinjauan manual</option>
                                <option>Instan / Otomatis</option>
                            </select>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Konfirmasi sesi</label>
                            <div class="input-locked-wrap">
                                <input type="text" class="admin-form-input" value="Keputusan terpisah oleh admin" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Metode pembayaran</label>
                            <select class="admin-form-input">
                                <option selected>Transfer / QRIS · Setelah konfigurasi</option>
                                <option>Tunai di Lokasi</option>
                            </select>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Prefiks invoice</label>
                            <input type="text" class="admin-form-input font-mono" value="INV-CH-2026">
                        </div>
                    </div>

                    {{-- PREFERENSI TOGGLE --}}
                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">Verifikasi pembayaran manual</strong>
                            <span class="toggle-desc">Unggahan bukti tidak otomatis melunasi invoice.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">Tinjau jadwal sebelum konfirmasi</strong>
                            <span class="toggle-desc">Cegah sesi terkonfirmasi pada slot bentrok.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    {{-- ALERT DATA RESMI BELUM TERSEDIA --}}
                    <div class="admin-alert-box alert-warning-box">
                        <strong class="admin-alert-title text-gold">Instruksi resmi belum tersedia</strong>
                        <p class="admin-alert-desc text-gold">
                            Bank, nomor rekening, nama penerima, dan QRIS harus diisi serta ditinjau studio sebelum ditampilkan kepada pelanggan.
                        </p>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Bank / nama penerima resmi</label>
                            <input type="text" class="admin-form-input" value="Belum dikonfigurasi">
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Nomor rekening resmi</label>
                            <input type="text" class="admin-form-input" value="Belum dikonfigurasi">
                        </div>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Berkas QRIS resmi</label>
                        <input type="text" class="admin-form-input" value="Belum diunggah · Pilih berkas setelah tersedia" readonly>
                    </div>
                </div>

                {{-- 3. PRIVASI & KEBIJAKAN (FIGMA 141:4777) --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Privasi & kebijakan</h2>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Teks privasi pelanggan *</label>
                        <textarea class="admin-form-input" rows="3">Kontak dan bukti digunakan untuk koordinasi sesi dan verifikasi oleh studio. Galeri bersifat pribadi; publikasi portofolio memerlukan izin terpisah.</textarea>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Kebijakan pembatalan *</label>
                        <textarea class="admin-form-input" rows="3">Setiap pembatalan ditinjau sesuai ketentuan pemesanan yang disepakati. Catat alasan dan komunikasikan keputusan kepada pelanggan.</textarea>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Kebijakan refund *</label>
                        <textarea class="admin-form-input" rows="4">Kelayakan, nominal, dan proses pengembalian dana harus ditinjau serta disetujui. Tidak ada refund otomatis. Catat ketentuan yang disepakati pada setiap kasus. Selesaikan dan setujui kebijakan resmi sebelum digunakan untuk keputusan keuangan.</textarea>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: NOTIFIKASI & KEAMANAN (FIGMA 141:4785) --}}
            <div class="admin-order-detail-right">
                {{-- PANEL NOTIFIKASI --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Notifikasi</h2>

                    <span class="admin-section-subhead">Kanal admin</span>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">Email admin</strong>
                            <span class="toggle-desc">Permintaan baru dan bukti pembayaran.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">WhatsApp admin</strong>
                            <span class="toggle-desc">Belum dikonfigurasi; pengiriman belum aktif.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <hr class="divider">

                    <span class="admin-section-subhead">Kabar untuk pelanggan</span>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">Keputusan pesanan via email</strong>
                            <span class="toggle-desc">Konfirmasi dan perubahan status ditinjau admin.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">Perbaikan bukti</strong>
                            <span class="toggle-desc">Kirim alasan dan instruksi, bukan tagihan kedua.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">Galeri siap dilihat</strong>
                            <span class="toggle-desc">Hanya setelah checklist publikasi disetujui.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="admin-toggle-row">
                        <div class="admin-toggle-text">
                            <strong class="toggle-title">WhatsApp pelanggan</strong>
                            <span class="toggle-desc">Memerlukan konfigurasi dan persetujuan kontak.</span>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="admin-alert-box">
                        <strong class="admin-alert-title">Tidak ada pengiriman nyata</strong>
                        <p class="admin-alert-desc">
                            Preferensi ini adalah UI statis. Kanal dan kredensial belum dihubungkan.
                        </p>
                    </div>
                </div>

                {{-- PANEL AKSES & AUDIT (FIGMA 141:4830) --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Akses & audit</h2>
                    <span class="status-indicator-item text-blue">● Pemilik studio · Admin</span>

                    <p class="admin-meta-val" style="font-size: 12px; line-height: 1.6;">
                        Transaksi terverifikasi tidak dapat dihapus atau ditimpa. Koreksi dan refund dicatat terpisah.
                        <br><br>
                        Batasi akses foto dan kontak. Jangan menyimpan OTP, PIN, atau kata sandi di catatan.
                    </p>

                    <hr class="divider">

                    <span class="admin-meta-hint">
                        Perubahan terakhir: Clara · 6 Okt 2026 · 09.20 WIB
                    </span>
                </div>
            </div>
        </div>

        {{-- TINDAKAN FORMULIR (FIGMA 141:4836) --}}
        <div class="admin-settings-actions-bar">
            <span class="admin-meta-hint">* Wajib diisi · Perubahan dicatat dalam riwayat</span>
            <div class="admin-btn-group-2">
                <button type="reset" class="admin-btn-outline">Batal</button>
                <button type="submit" class="admin-btn-primary">Simpan perubahan</button>
            </div>
        </div>
    </form>

    {{-- FOOTER COPYRIGHT (FIGMA 141:4850) --}}
    <div class="admin-footer-copyright">
        © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
    </div>
@endsection

