@extends('layouts.admin')

@section('title', 'Pengaturan Studio — naro capture')
@section('breadcrumb', 'Pengaturan / Pengaturan studio')

@section('content')
    {{-- PENGANTAR HALAMAN --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pengaturan studio</h1>
            <p class="admin-page-subtitle">Profil, kebijakan, dan preferensi komunikasi untuk operasional sehari-hari.</p>
        </div>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom:16px;">
            <strong class="admin-alert-title text-green">Pengaturan berhasil disimpan</strong>
            <p class="admin-alert-desc text-green">
                {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- GRID PENGATURAN --}}
    <form action="{{ route('admin.pengaturan.save') }}" method="POST">
        @csrf
        <div class="admin-order-detail-grid">
            {{-- KOLOM KIRI: FORMULIR UTAMA --}}
            <div class="admin-order-detail-left">
                {{-- 1. STUDIO & PROFIL ADMIN --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Studio & profil admin</h2>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Nama studio *</label>
                            <input type="text" name="studio_name" class="admin-form-input" value="{{ $settings['studio_name'] ?? 'naro capture' }}" required>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Nama admin *</label>
                            <input type="text" name="admin_name" class="admin-form-input" value="{{ $settings['admin_name'] ?? 'naro capture Admin' }}" required>
                        </div>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Email studio *</label>
                            <input type="email" name="studio_email" class="admin-form-input" value="{{ $settings['studio_email'] ?? 'hello@narocapture.com' }}" required>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">WhatsApp studio</label>
                            <input type="text" name="studio_whatsapp" class="admin-form-input" value="{{ $settings['studio_whatsapp'] ?? '+62 812 3456 7890' }}">
                        </div>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Lokasi studio</label>
                            <input type="text" name="studio_location" class="admin-form-input" value="{{ $settings['studio_location'] ?? 'Jakarta, Indonesia' }}">
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Zona waktu *</label>
                            <select name="timezone" class="admin-form-input">
                                <option value="WIB" {{ ($settings['timezone'] ?? 'WIB') === 'WIB' ? 'selected' : '' }}>WIB · UTC+7</option>
                                <option value="WITA" {{ ($settings['timezone'] ?? '') === 'WITA' ? 'selected' : '' }}>WITA · UTC+8</option>
                                <option value="WIT" {{ ($settings['timezone'] ?? '') === 'WIT' ? 'selected' : '' }}>WIT · UTC+9</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. PEMESANAN & PEMBAYARAN --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Pemesanan & pembayaran</h2>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Penerimaan pemesanan</label>
                            <select name="booking_mode" class="admin-form-input">
                                <option value="Permintaan jadwal · Tinjauan manual" {{ ($settings['booking_mode'] ?? '') === 'Permintaan jadwal · Tinjauan manual' ? 'selected' : '' }}>Permintaan jadwal · Tinjauan manual</option>
                                <option value="Instan / Otomatis" {{ ($settings['booking_mode'] ?? '') === 'Instan / Otomatis' ? 'selected' : '' }}>Instan / Otomatis</option>
                            </select>
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Metode pembayaran</label>
                            <select name="payment_method" class="admin-form-input">
                                <option value="Transfer Bank (BCA & Mandiri)" {{ ($settings['payment_method'] ?? '') === 'Transfer Bank (BCA & Mandiri)' ? 'selected' : '' }}>Transfer Bank (BCA & Mandiri)</option>
                                <option value="Tunai di Kasir Studio" {{ ($settings['payment_method'] ?? '') === 'Tunai di Kasir Studio' ? 'selected' : '' }}>Tunai di Kasir Studio</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-meta-row-2">
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Prefiks invoice</label>
                            <input type="text" name="invoice_prefix" class="admin-form-input font-mono" value="{{ $settings['invoice_prefix'] ?? 'INV-CH-2026' }}">
                        </div>
                        <div class="admin-meta-field">
                            <label class="admin-meta-label">Bank / nama rekening resmi</label>
                            <input type="text" name="bank_account_name" class="admin-form-input" value="{{ $settings['bank_account_name'] ?? 'BCA & Mandiri a.n. naro capture Studio' }}">
                        </div>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Nomor rekening resmi studio</label>
                        <input type="text" name="bank_account_number" class="admin-form-input font-mono" value="{{ $settings['bank_account_number'] ?? 'BCA: 8735 0912 344 · Mandiri: 137 00 2841 9920' }}">
                    </div>
                </div>

                {{-- 3. PRIVASI & KEBIJAKAN --}}
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Privasi & kebijakan</h2>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Teks privasi pelanggan *</label>
                        <textarea name="privacy_policy" class="admin-form-input" rows="3">{{ $settings['privacy_policy'] ?? 'Kontak dan bukti digunakan untuk koordinasi sesi dan verifikasi oleh studio. Galeri bersifat pribadi; publikasi portofolio memerlukan izin terpisah.' }}</textarea>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Kebijakan pembatalan *</label>
                        <textarea name="cancellation_policy" class="admin-form-input" rows="3">{{ $settings['cancellation_policy'] ?? 'Setiap pembatalan ditinjau sesuai ketentuan pemesanan yang disepakati. Catat alasan dan komunikasikan keputusan kepada pelanggan.' }}</textarea>
                    </div>

                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Kebijakan refund *</label>
                        <textarea name="refund_policy" class="admin-form-input" rows="4">{{ $settings['refund_policy'] ?? 'Kelayakan, nominal, dan proses pengembalian dana harus ditinjau serta disetujui. Tidak ada refund otomatis. Catat ketentuan yang disepakati pada setiap kasus.' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: NOTIFIKASI & INFORMASI --}}
            <div class="admin-order-detail-right">
                <div class="admin-panel">
                    <h2 class="admin-panel-title">Status Konfigurasi</h2>
                    <span class="status-indicator-item text-green">● Penyimpanan Database Aktif</span>

                    <p class="admin-meta-val" style="font-size: 12px; line-height: 1.6; margin-top:12px;">
                        Setiap perubahan pengaturan pada formulir ini disimpan secara persisten di tabel <code>studio_settings</code> database SQLite lokal aplikasi.
                        <br><br>
                        Data ini digunakan oleh sistem invoice dan informasi pembayaran di halaman publik.
                    </p>

                    <hr class="divider">

                    <span class="admin-meta-hint">
                        Terakhir dimuat dari database: {{ now()->isoFormat('D MMMM Y, H:mm') }} WIB
                    </span>
                </div>
            </div>
        </div>

        {{-- TINDAKAN FORMULIR --}}
        <div class="admin-settings-actions-bar">
            <span class="admin-meta-hint">* Wajib diisi · Perubahan langsung tersimpan ke database</span>
            <div class="admin-btn-group-2">
                <button type="reset" class="admin-btn-outline">Reset</button>
                <button type="submit" class="admin-btn-primary">Simpan perubahan</button>
            </div>
        </div>
    </form>

    {{-- FOOTER COPYRIGHT --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Terhubung ke Database SQLite · Akses terbatas admin
    </div>
@endsection
