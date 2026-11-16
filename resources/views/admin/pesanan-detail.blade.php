@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order['code'] . ' — naro capture')
@section('breadcrumb', 'Pesanan / Detail pesanan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:1125) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Detail pesanan</h1>
            <p class="admin-page-subtitle">{{ $order['customer'] }} · Dibuat {{ $order['created_date'] }}</p>
        </div>
        <a href="{{ route('admin.pesanan') }}" class="admin-btn-outline">
            ← Kembali ke daftar
        </a>
    </div>

    {{-- FEEDBACK NOTICE (SUCCESS / ERROR) --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom: 16px; background:#ECFDF5; border:1px solid #A7F3D0;">
            <strong class="admin-alert-title text-green" style="color:#065F46;">Berhasil</strong>
            <p class="admin-alert-desc text-green" style="color:#047857;">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="admin-alert-box alert-danger-box" style="margin-bottom: 16px; background:#FEF2F2; border:1px solid #FECACA;">
            <strong class="admin-alert-title text-red" style="color:#991B1B;">Perhatian</strong>
            <p class="admin-alert-desc text-red" style="color:#B91C1C;">{{ session('error') }}</p>
        </div>
    @endif

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
                    @php
                        $thumbSrc = $order['package_image'] ?? 'paket-family.jpg';
                        if (!\Illuminate\Support\Str::startsWith($thumbSrc, ['http://', 'https://', '/'])) {
                            $thumbSrc = asset('images/' . $thumbSrc);
                        }
                    @endphp
                    <img src="{{ $thumbSrc }}" alt="{{ $order['package_name'] }}" class="package-card-thumb">
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
                        <label class="admin-meta-label">Kontak pemesan</label>
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
                <span class="admin-meta-hint">Hanya dapat dilihat oleh admin studio naro capture</span>
            </div>

            {{-- PANEL GALERI HASIL FOTO KLIEN --}}
            <div class="admin-panel" style="margin-top: 16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <h2 class="admin-panel-title" style="margin-bottom:0;">Galeri hasil foto klien</h2>
                    @if(!empty($order['gallery']))
                        @if($order['gallery']['status'] === 'Diterbitkan')
                            <span class="badge-status-payment status-terverifikasi" style="font-size:11px;">● Diterbitkan (Publik)</span>
                        @elseif($order['gallery']['status'] === 'Siap')
                            <span class="badge-status-payment status-terverifikasi" style="background:#E2F0D9; color:#385723; font-size:11px;">● Siap Terbit</span>
                        @elseif($order['gallery']['status'] === 'Penyuntingan')
                            <span class="badge-status-payment status-bukti-terkirim" style="font-size:11px;">● Penyuntingan</span>
                        @else
                            <span class="badge-status-payment" style="background:#F0F1EE; color:#576478; font-size:11px;">● Draf</span>
                        @endif
                    @endif
                </div>

                @if(!empty($order['gallery']))
                    <div style="background: #F8FAFD; border: 1px solid #E1E7F0; border-radius: 8px; padding: 14px; margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div>
                                <strong style="font-size: 14px; color: #253247; display: block;">{{ $order['gallery']['title'] }}</strong>
                                <span style="font-size: 12px; color: #6E7A8B;">
                                    {{ $order['gallery']['photos_count'] }} / {{ $order['gallery']['target_photos'] }} foto terunggah · Akses: {{ $order['gallery']['access_type'] }}
                                    @if($order['gallery']['pin_code'])
                                        · PIN: <strong style="color: #2563EB;">{{ $order['gallery']['pin_code'] }}</strong>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap;">
                            <a href="{{ route('admin.galeri') }}" class="admin-btn-outline" style="font-size: 12px; padding: 6px 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                ✎ Kelola &amp; Unggah Foto di Galeri
                            </a>
                            <a href="{{ $order['gallery']['url'] }}" target="_blank" class="admin-btn-outline" style="font-size: 12px; padding: 6px 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                Pratinjau Klien ↗
                            </a>
                            @if($order['gallery']['status'] !== 'Diterbitkan')
                                <form action="{{ route('admin.galeri.status', ['id' => $order['gallery']['id']]) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="status" value="Diterbitkan">
                                    <button type="submit" class="admin-btn-primary" style="font-size: 12px; padding: 6px 14px; background: #10B981; border-color: #10B981; font-weight: 600;">
                                        ✓ Terbitkan Galeri Sekarang
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div style="background: #F8FAFD; border: 1px dashed #CBD5E1; border-radius: 8px; padding: 16px; text-align: center;">
                        <p style="font-size: 13px; color: #6E7A8B; margin-bottom: 12px;">
                            Galeri untuk pesanan ini belum dibuat. Setelah pemotretan selesai, buat galeri untuk mengunggah dan membagikan foto hasil jepretan kepada pelanggan.
                        </p>
                        <a href="{{ route('admin.galeri') }}" class="admin-btn-outline" style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF;">
                            + Buat Galeri Pelanggan
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN DAN TINDAK LANJUT (FIGMA 141:1160) --}}
        <div class="admin-order-detail-right">
            {{-- PANEL PEMBAYARAN & INVOICE (TERINTEGRASI DENGAN BUKTI TRANSFER) --}}
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
                    @if($order['payment_status'] === 'Terverifikasi' || $order['payment_status'] === 'Lunas')
                        <span class="status-indicator-item text-green" style="color: #059669; font-weight:600;">● Terverifikasi · Lunas</span>
                    @elseif($order['payment_status'] === 'Perlu perbaikan')
                        <span class="status-indicator-item text-red" style="color: #DC2626; font-weight:600;">● Perlu perbaikan bukti</span>
                    @elseif(!empty($order['has_proof_file']) || !empty($order['has_payment']))
                        <span class="status-indicator-item text-gold">● Bukti terkirim · Menunggu verifikasi</span>
                    @else
                        <span class="status-indicator-item text-gold">● Belum dibayar</span>
                    @endif
                </div>

                {{-- BUKTI PEMBAYARAN LANGSUNG DI HALAMAN DETAIL PESANAN --}}
                @if(!empty($order['has_proof_file']))
                    <div class="admin-meta-field" style="margin-top: 14px;">
                        <label class="admin-meta-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Bukti Transfer ({{ $order['bank_source'] ?? 'Bank' }})</span>
                            <span style="font-size:11px; color:#6E7A8B;">{{ $order['submitted_date'] ?? $order['payment_submitted_date'] }}</span>
                        </label>
                        
                        {{-- Kartu Pratinjau Bukti --}}
                        <div class="admin-proof-preview-card" style="border: 1px solid #E1E7F0; border-radius: 8px; overflow: hidden; background: #F8FAFD; margin-top: 6px;">
                            @if(!empty($order['proof_is_image']))
                                <div style="position: relative; height: 160px; background: #0F172A; display:flex; align-items:center; justify-content:center; cursor: pointer;" onclick="openProofZoomModal('{{ $order['proof_file_url'] }}', '{{ $order['proof_file_name'] }}')">
                                    <img src="{{ $order['proof_file_url'] }}" alt="Bukti Transfer" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                    <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.7); color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 4px; display:flex; align-items:center; gap:4px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                        Perbesar
                                    </div>
                                </div>
                            @elseif(!empty($order['proof_is_pdf']))
                                <div style="position: relative; height: 180px; background: #F8FAFD; display:flex; flex-direction:column; align-items:center; justify-content:center; border-bottom:1px solid #E2E8F0; overflow:hidden;">
                                    <iframe src="{{ $order['proof_file_url'] }}#toolbar=0" style="width: 100%; height: 100%; border: none;" title="{{ $order['proof_file_name'] }}"></iframe>
                                </div>
                            @endif
                            <div style="padding: 10px 12px; display:flex; justify-content:space-between; align-items:center; font-size: 12px;">
                                <div>
                                    <strong style="display:block; color:#253247; font-size:12px;">{{ $order['submitted_nominal'] ?? $order['package_price'] }}</strong>
                                    <span style="color:#6E7A8B; font-size:11px;">Pengirim: {{ $order['sender_name'] ?? $order['customer'] }}</span>
                                </div>
                                <div style="display:flex; gap:6px;">
                                    <button type="button" class="admin-btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="openProofZoomModal('{{ $order['proof_file_url'] }}', '{{ $order['proof_file_name'] }}')">
                                        Lihat bukti pembayaran
                                    </button>
                                    <a href="{{ $order['proof_file_url'] }}" target="_blank" download class="admin-btn-outline" style="padding: 4px 10px; font-size: 11px; text-decoration:none;">
                                        Unduh
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi Verifikasi Pembayaran --}}
                    @if($order['payment_status'] !== 'Terverifikasi' && $order['payment_status'] !== 'Lunas')
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 14px; width: 100%;">
                            @if($order['session_status'] !== 'Dikonfirmasi')
                                <form action="{{ route('admin.pesanan.verifikasi-dan-konfirmasi', ['id' => $order['code']]) }}" method="POST" style="width: 100%; margin: 0;">
                                    @csrf
                                    <button type="submit" class="admin-btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; background:#10B981; border-color:#10B981; font-weight:600; padding: 10px 16px; font-size: 13px;" title="Verifikasi pembayaran dan setujui jadwal sesi dalam 1 klik">
                                        ✓ Verifikasi &amp; Konfirmasi Sesi
                                    </button>
                                </form>
                            @endif

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%;">
                                <form action="{{ route('admin.pesanan.verifikasi-pembayaran', ['id' => $order['code']]) }}" method="POST" style="width: 100%; margin: 0; display: flex;">
                                    @csrf
                                    <button type="submit" class="admin-btn-outline" style="width: 100%; color:#2563EB; border-color:#BFDBFE; background:#EFF6FF; font-size:12px; font-weight:500; padding: 8px 12px; display: flex; align-items: center; justify-content: center; text-align: center;" title="Verifikasi pembayaran saja">
                                        Verifikasi Saja
                                    </button>
                                </form>
                                <button type="button" class="admin-btn-outline" style="width: 100%; color:#EF4444; border-color:#FECACA; background:#FEF2F2; font-size:12px; font-weight:500; padding: 8px 12px; display: flex; align-items: center; justify-content: center; text-align: center;" id="btn-open-revision-modal" title="Minta pelanggan mengirim ulang bukti transfer">
                                    Minta Perbaikan
                                </button>
                            </div>
                        </div>
                    @else
                        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 10px 12px; margin-top: 12px; display:flex; align-items:center; gap:8px;">
                            <span style="color: #059669; font-weight: 700;">✓</span>
                            <span style="color: #065F46; font-size: 12px;">Pembayaran telah diverifikasi lunas{{ $order['payment_verified_at'] ? ' ('.$order['payment_verified_at'].')' : '' }}.</span>
                        </div>
                    @endif

                @elseif(!empty($order['has_payment']) && !empty($order['submitted_nominal']))
                    <div class="admin-meta-field" style="margin-top:10px;">
                        <label class="admin-meta-label">Nominal diajukan</label>
                        <p class="admin-meta-val">{{ $order['submitted_nominal'] }} · {{ $order['submitted_date'] }}</p>
                    </div>

                    <a href="{{ route('admin.pembayaran.detail', ['id' => $order['payment_code'] ?? $order['code']]) }}" class="admin-btn-outline w-full text-center" style="margin-top:8px; display:block;">
                        Lihat bukti pembayaran
                    </a>

                    @if($order['payment_status'] !== 'Terverifikasi' && $order['payment_status'] !== 'Lunas')
                        <form action="{{ route('admin.pesanan.verifikasi-pembayaran', ['id' => $order['code']]) }}" method="POST" style="margin-top:10px;">
                            @csrf
                            <button type="submit" class="admin-btn-primary w-full" style="background:#10B981; border-color:#10B981; font-size:12px;">
                                ✓ Verifikasi Pembayaran
                            </button>
                        </form>
                    @else
                        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 10px 12px; margin-top: 10px; display:flex; align-items:center; gap:8px;">
                            <span style="color: #059669; font-weight: 700;">✓</span>
                            <span style="color: #065F46; font-size: 12px;">Pembayaran telah diverifikasi lunas.</span>
                        </div>
                    @endif

                @else
                    <div class="admin-meta-field" style="margin-top:10px;">
                        <label class="admin-meta-label">Bukti transfer</label>
                        <p class="admin-meta-val" style="color: #6E7A8B; font-size: 13px;">Pelanggan belum mengirimkan bukti pembayaran.</p>
                    </div>

                    @if($order['payment_status'] !== 'Terverifikasi' && $order['payment_status'] !== 'Lunas')
                        <form action="{{ route('admin.pesanan.verifikasi-pembayaran', ['id' => $order['code']]) }}" method="POST" style="margin-top:8px;">
                            @csrf
                            <button type="submit" class="admin-btn-outline w-full" style="font-size:12px;" title="Tandai pembayaran tunai atau manual di studio">
                                Tandai Lunas (Manual/Tunai)
                            </button>
                        </form>
                    @endif
                @endif

                <hr class="admin-divider" style="margin: 16px 0; border:0; border-top:1px solid #E1E7F0;">

                {{-- STATUS & KONFIRMASI JADWAL SESI --}}
                @if($order['session_status'] === 'Dikonfirmasi')
                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                        <span style="color: #2563EB; font-weight: 700;">✓</span>
                        <span style="color: #1E40AF; font-size: 12px; font-weight:500;">Jadwal sesi foto resmi telah dikonfirmasi.</span>
                    </div>

                    {{-- TOMBOL TANDAI SESI FOTO SELESAI --}}
                    <form action="{{ route('admin.pesanan.selesai-sesi', ['id' => $order['code']]) }}" method="POST" style="margin-bottom: 12px;">
                        @csrf
                        <button type="submit" class="admin-btn-primary w-full" style="background: #0284C7; border-color: #0284C7; font-weight: 600; font-size: 13px;" title="Tandai pemotretan telah selesai dilaksanakan di studio/lokasi">
                            ✓ Tandai Sesi Foto Selesai
                        </button>
                    </form>
                @elseif($order['session_status'] === 'Selesai')
                    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                        <span style="color: #059669; font-weight: 700;">✓</span>
                        <span style="color: #065F46; font-size: 12px; font-weight:500;">Sesi pemotretan telah selesai dilaksanakan.</span>
                    </div>
                @else
                    @if($order['payment_status'] !== 'Terverifikasi' && $order['payment_status'] !== 'Lunas')
                        {{-- PEMBAYARAN BELUM LUNAS: KONFIRMASI SESI TERKUNCI / DISABLED --}}
                        <div class="admin-alert-box" style="margin-bottom: 12px; background: #FFFBEB; border: 1px solid #FDE68A;">
                            <strong class="admin-alert-title" style="color: #B45309;">🔒 Konfirmasi Sesi Terkunci</strong>
                            <p class="admin-alert-desc" style="color: #92400E;">
                                Pembayaran belum diterima. Selesaikan verifikasi pembayaran atau gunakan tombol <em>Tandai Lunas (Manual/Tunai)</em> di atas untuk membuka konfirmasi jadwal sesi.
                            </p>
                        </div>

                        <button type="button" class="admin-btn-primary w-full" disabled style="opacity: 0.55; cursor: not-allowed; background: #94A3B8; border-color: #94A3B8;" title="Harap verifikasi pembayaran atau tandai lunas manual terlebih dahulu">
                            Konfirmasi sesi (Menunggu Pembayaran)
                        </button>
                    @else
                        {{-- PEMBAYARAN SUDAH LUNAS: KONFIRMASI SESI AKTIF --}}
                        <div class="admin-alert-box" style="margin-bottom: 12px; background: #ECFDF5; border: 1px solid #A7F3D0;">
                            <strong class="admin-alert-title" style="color: #065F46;">Pembayaran Selesai</strong>
                            <p class="admin-alert-desc" style="color: #047857;">Pembayaran telah lunas. Anda dapat mengonfirmasi jadwal sesi sekarang.</p>
                        </div>

                        <form action="{{ route('admin.pesanan.konfirmasi-sesi', ['id' => $order['code']]) }}" method="POST">
                            @csrf
                            <button type="submit" class="admin-btn-primary w-full">
                                Konfirmasi sesi
                            </button>
                        </form>
                    @endif
                @endif

                <p class="admin-meta-disclaimer" style="margin-top: 10px;">
                    Tinjau ketersediaan dan pembayaran sebelum menyetujui. Konfirmasi mengubah status yang dilihat pelanggan.
                </p>
            </div>

            {{-- PANEL LINIMASA PESANAN (FIGMA 141:1182) --}}
            <div class="admin-panel">
                <h2 class="admin-panel-title">Linimasa pesanan</h2>

                <div class="admin-order-timeline">
                    {{-- 1. Pesanan Dibuat --}}
                    <div class="timeline-step is-complete">
                        <span class="timeline-check">✓</span>
                        <div class="timeline-text">
                            <strong>{{ $order['created_date_short'] }} · Pesanan dibuat</strong>
                        </div>
                    </div>

                    {{-- 2. Bukti Pembayaran --}}
                    @if(!empty($order['has_payment']) || in_array($order['payment_status_badge'], ['Terverifikasi', 'Lunas', 'Bukti terkirim']))
                        <div class="timeline-step is-complete">
                            <span class="timeline-check">✓</span>
                            <div class="timeline-text">
                                <strong>{{ $order['payment_submitted_date'] ?? $order['created_date_short'] }} · Bukti diterima</strong>
                            </div>
                        </div>
                    @elseif($order['payment_status_badge'] === 'Perlu perbaikan')
                        <div class="timeline-step is-pending">
                            <span class="timeline-circle" style="color:#94671E;">⚠</span>
                            <div class="timeline-text">
                                <span style="color:#94671E; font-weight:600;">Perlu perbaikan bukti</span>
                            </div>
                        </div>
                    @else
                        <div class="timeline-step is-pending">
                            <span class="timeline-circle">○</span>
                            <div class="timeline-text">
                                <span>Bukti transfer · Belum dikirim</span>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Verifikasi Pembayaran --}}
                    @if(in_array($order['payment_status_badge'], ['Terverifikasi', 'Lunas']))
                        <div class="timeline-step is-complete">
                            <span class="timeline-check">✓</span>
                            <div class="timeline-text">
                                <strong>Pembayaran terverifikasi{{ !empty($order['payment_verified_at']) ? ' · '.$order['payment_verified_at'] : '' }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="timeline-step is-pending">
                            <span class="timeline-circle">○</span>
                            <div class="timeline-text">
                                <span>Verifikasi pembayaran · Menunggu</span>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Konfirmasi Jadwal --}}
                    @if(in_array($order['session_status'], ['Dikonfirmasi', 'Selesai']))
                        <div class="timeline-step is-complete">
                            <span class="timeline-check">✓</span>
                            <div class="timeline-text">
                                <strong>Jadwal sesi dikonfirmasi</strong>
                            </div>
                        </div>
                    @elseif($order['session_status'] === 'Dibatalkan')
                        <div class="timeline-step is-pending">
                            <span class="timeline-circle" style="color:#DC2626;">✕</span>
                            <div class="timeline-text">
                                <span style="color:#DC2626; font-weight:600;">Sesi dibatalkan</span>
                            </div>
                        </div>
                    @else
                        <div class="timeline-step is-pending">
                            <span class="timeline-circle">○</span>
                            <div class="timeline-text">
                                <span>Konfirmasi jadwal · Menunggu</span>
                            </div>
                        </div>
                    @endif

                    {{-- 5. Sesi Selesai --}}
                    @if($order['session_status'] === 'Selesai')
                        <div class="timeline-step is-complete">
                            <span class="timeline-check">✓</span>
                            <div class="timeline-text">
                                <strong>Sesi selesai</strong>
                            </div>
                        </div>
                    @else
                        <div class="timeline-step is-pending">
                            <span class="timeline-circle">○</span>
                            <div class="timeline-text">
                                <span>Sesi selesai · Belum berlangsung</span>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="divider">

                {{-- Grup Aksi Utama --}}
                <div style="display: flex; flex-direction: column; gap: 8px;">

                    {{-- Edit & Arsipkan --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <button type="button" class="admin-btn-outline" id="btn-open-edit-order" style="display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 13px; font-weight: 500; padding: 9px 12px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit Pesanan
                        </button>
                        <button type="button" class="admin-btn-outline" id="btn-open-archive-order" style="display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 13px; font-weight: 500; padding: 9px 12px; color: #6E7A8B;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
                            Arsipkan
                        </button>
                    </div>

                    {{-- Batalkan (satu baris penuh, kuning/warning) --}}
                    <button type="button" class="admin-btn-outline" id="btn-open-cancel-order" style="display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; font-size: 13px; font-weight: 500; padding: 9px 12px; color: #B45309; border-color: #FDE68A; background: #FFFBEB;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        Batalkan Pesanan
                    </button>

                    {{-- Aksi Draft & Hapus Pesanan --}}
                    @php
                        $canDeleteOrDraft = !in_array($order['session_status'], ['Dikonfirmasi', 'Selesai']);
                    @endphp

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 4px;">
                        @if($canDeleteOrDraft)
                            @if($order['session_status'] !== 'Draft')
                                <form action="{{ route('admin.pesanan.draft', ['id' => $order['code']]) }}" method="POST" onsubmit="return confirm('Ubah status pesanan {{ $order['code'] }} menjadi Draft?')" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="admin-btn-outline" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 13px; font-weight: 500; padding: 9px 12px; color: #475569; background: #F8FAFC; border-color: #CBD5E1;">
                                        Jadikan Draft
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled class="admin-btn-outline" style="width: 100%; font-size: 13px; font-weight: 500; padding: 9px 12px; color: #94A3B8; background: #F1F5F9; border-color: #E2E8F0; cursor: not-allowed;">
                                    Sudah Draft
                                </button>
                            @endif

                            <form action="{{ route('admin.pesanan.destroy', ['id' => $order['code']]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan {{ $order['code'] }} secara permanen dari sistem? Tindakan ini tidak dapat dibatalkan.')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn-outline btn-outline-danger" style="width: 100%; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 9px 12px;" title="Hapus permanen pesanan ini">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                    Hapus Pesanan
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="admin-btn-outline" style="grid-column: 1 / -1; font-size: 12px; padding: 8px 12px; color: #94A3B8; background: #F8FAFC; border-color: #E2E8F0; cursor: not-allowed; text-align: center;" title="Pesanan dengan status Dikonfirmasi atau Selesai tidak dapat dihapus / di-draft-kan">
                                Sesi {{ $order['session_status'] }} terkunci (Tidak dapat dihapus / di-draft)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG BATALKAN PESANAN --}}
    <div class="admin-modal-overlay" id="modal-cancel-order">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.pesanan.batalkan-sesi', ['id' => $order['code']]) }}" method="POST">
                @csrf
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
                    <textarea name="alasan" class="admin-form-input" rows="3" required>Pelanggan meminta membatalkan rencana sesi.</textarea>
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
                    <button type="submit" class="admin-btn-primary" style="background:#D32F2F; border-color:#D32F2F;">
                        Batalkan pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DIALOG ARSIPKAN PESANAN --}}
    <div class="admin-modal-overlay" id="modal-archive-order">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.pesanan.arsipkan-sesi', ['id' => $order['code']]) }}" method="POST">
                @csrf
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
                    <p class="admin-meta-val">Status: {{ $order['session_status'] }} · Pembayaran: {{ $order['payment_summary'] }}</p>
                </div>

                <div class="admin-alert-box alert-success-box">
                    <strong class="admin-alert-title text-green">Riwayat tetap tersimpan</strong>
                    <p class="admin-alert-desc text-green">
                        Pesanan berpindah ke status Arsip. Invoice {{ $order['invoice_number'] }}, bukti pembayaran, dan audit tidak dihapus.
                    </p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan arsip</label>
                    <input type="text" name="catatan_arsip" class="admin-form-input" value="Sesi selesai dan seluruh administrasi ditutup.">
                </div>

                <p class="admin-meta-disclaimer">
                    Arsip memindahkan pesanan dari antrean aktif. Seluruh catatan audit tetap dipertahankan.
                </p>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-archive-order">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Arsipkan pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DIALOG EDIT PESANAN --}}
    <div class="admin-modal-overlay" id="modal-edit-order">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.pesanan.update', ['id' => $order['code']]) }}" method="POST">
                @csrf
                @method('PUT')
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
                        <input type="date" name="session_date" class="admin-form-input" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu sesi</label>
                        <input type="text" name="session_time_slot" class="admin-form-input" value="{{ $order['time_formatted'] }}">
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Lokasi sesi</label>
                    <input type="text" name="location" class="admin-form-input" value="{{ $order['location'] }}">
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan internal admin</label>
                    <textarea name="internal_notes" class="admin-form-input" rows="3">{{ $order['internal_note'] }}</textarea>
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-edit-order">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Simpan perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ZOOM BUKTI PEMBAYARAN --}}
    <div class="admin-modal-overlay" id="modal-proof-zoom" style="background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(6px); z-index: 100000;">
        <div style="position: relative; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <button type="button" class="admin-btn-close-modal" data-close="modal-proof-zoom" style="position: absolute; top: -40px; right: 0; color: #fff; background: rgba(255,255,255,0.2); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer;">
                ✕
            </button>
            <img id="modal-proof-img" src="" alt="Bukti Transfer Penuh" style="max-height: 82vh; max-width: 85vw; object-fit: contain; border-radius: 8px; box-shadow: 0 20px 48px rgba(0,0,0,0.5);">
            <span id="modal-proof-caption" style="color: #E2E8F0; font-size: 12px; margin-top: 10px;">Bukti Transfer Pelanggan</span>
        </div>
    </div>

    {{-- MODAL MINTA PERBAIKAN BUKTI TRANSFER --}}
    <div class="admin-modal-overlay" id="modal-minta-perbaikan">
        <div class="admin-modal-dialog" style="max-width: 440px;">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title" style="color:#DC2626;">Minta Perbaikan Bukti Transfer</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-minta-perbaikan">✕</button>
            </div>
            <p class="admin-modal-subtitle">
                Pelanggan akan diminta mengunggah ulang bukti transfer di halaman pelacakan pesanan mereka.
            </p>
            <form action="{{ route('admin.pesanan.minta-perbaikan-bukti', ['id' => $order['code']]) }}" method="POST">
                @csrf
                <div class="admin-form-group">
                    <label class="admin-form-label">Alasan perbaikan bukti <span class="text-red">*</span></label>
                    <textarea name="alasan" rows="3" class="admin-form-input" style="resize:vertical;" required placeholder="Contoh: Foto bukti transfer buram / nominal transfer tidak sesuai"></textarea>
                </div>

                <div class="admin-modal-actions" style="margin-top: 16px;">
                    <button type="button" class="admin-btn-outline" data-close="modal-minta-perbaikan">Batal</button>
                    <button type="submit" class="admin-btn-primary" style="background:#DC2626; border-color:#DC2626;">
                        Kirim Permintaan Perbaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:1199) --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Sistem Manajemen Pesanan · Akses terbatas admin
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
            bindModal('btn-open-revision-modal', 'modal-minta-perbaikan');

            window.openProofZoomModal = function(url, caption) {
                const modal = document.getElementById('modal-proof-zoom');
                const img = document.getElementById('modal-proof-img');
                const cap = document.getElementById('modal-proof-caption');
                if (img) img.src = url;
                if (cap) cap.textContent = caption || 'Bukti Transfer Pelanggan';
                if (modal) modal.classList.add('is-open');
            };

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

