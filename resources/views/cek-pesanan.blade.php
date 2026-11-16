@extends('layouts.app')

@section('title', 'Cek Status Pesanan — naro capture')

@section('content')
<main class="tracking-page">
    {{-- BAGIAN PENCARIAN PESANAN (FIGMA 95:1501) --}}
    <section class="tracking-search-section">
        <div class="booking-breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="sep">/</span>
            <span>Cek Pesanan</span>
        </div>

        <div class="checkout-header">
            <div class="checkout-header__left">
                <span class="eyebrow">KABAR TERBARU TENTANG SESI ANDA</span>
                <h1 class="checkout-header__title">Cek status pesanan.</h1>
            </div>
            <p class="body-text checkout-header__desc">
                Pantau perjalanan pesanan Anda dengan kode dan kontak saat pemesanan. Tanpa perlu membuat akun atau masuk.
            </p>
        </div>

        {{-- FORMULIR PENCARIAN (1 FIELD: KODE PESANAN / INVOICE) --}}
        <div class="tracking-search-box">
            <div class="checkout-card-header">
                <h2 class="card-title">Temukan pesanan Anda</h2>
                <span class="form-required-note">* Wajib diisi</span>
            </div>

            <form action="{{ route('cek-pesanan') }}" method="GET" class="tracking-search-form">
                <div class="tracking-search-row">
                    <div class="tracking-search-col">
                        <label for="searchKode" class="form-label">Kode Pesanan atau Nomor Invoice *</label>
                        <input type="text" id="searchKode" name="kode" class="form-control" placeholder="Masukkan kode pesanan (contoh: CH-CONTOH-001) atau nomor invoice" value="{{ $searchCode }}" required autofocus>
                    </div>

                    <button type="submit" class="btn btn--fill btn-search-tracking" style="height: 48px; padding: 0 28px; white-space: nowrap;">
                        Cek status pesanan @include('partials.arrow')
                    </button>
                </div>
            </form>

            <div class="tracking-search-privacy">
                <img src="{{ asset('images/icon-shield-check.svg') }}" alt="" width="16" height="16">
                <span>Kode pesanan dapat ditemukan di halaman checkout, lembar invoice, atau pesan konfirmasi email/WhatsApp Anda.</span>
            </div>
        </div>
    </section>

    @if($hasSearched)
        @if($orderFound && $data)
            {{-- HASIL PENCARIAN PESANAN (FIGMA 95:1533) --}}
            <section class="tracking-results-section">
                <div class="tracking-results-header">
                    <div class="tracking-title-wrap">
                        <h2 class="card-title font-25">Pesanan ditemukan</h2>
                        <span class="badge-tag" style="background:#E8F0FF; color:#3B5B9A; padding:6px 14px; font-size:12px; font-weight:600; border-radius:9999px;">
                            {{ $data['package_name'] }}
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span class="body-text font-13 muted">Kode: <strong>{{ $data['order_code'] }}</strong> · {{ $data['invoice_number'] }}</span>
                        <a href="{{ route('cek-pesanan') }}" class="btn btn--outline btn-sm" style="padding: 6px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            ✕ Cari pesanan lain
                        </a>
                    </div>
                </div>

                <div class="checkout-split-grid">
                    {{-- KOLOM KIRI: STATUS PESANAN & LINIMASA (FIGMA 95:1541) --}}
                    <div class="checkout-box status-tracking-main-box">
                        <div class="status-current-box">
                            @if($statusKey === 'dikonfirmasi')
                                <div class="status-eyebrow-row">
                                    <img src="{{ asset('images/check.svg') }}" alt="" width="18" height="18">
                                    <span class="eyebrow">PESANAN DIKONFIRMASI</span>
                                </div>
                                <h2 class="status-main-heading">Sesi Anda sudah dikonfirmasi</h2>
                                <p class="body-text">
                                    Pembayaran telah terverifikasi dan jadwal sesi disetujui oleh studio. Kami menantikan cerita berkesan Anda.
                                </p>
                            @elseif($statusKey === 'perbaikan-bukti')
                                <div class="status-eyebrow-row">
                                    <img src="{{ asset('images/icon-clock.svg') }}" alt="" width="18" height="18">
                                    <span class="eyebrow" style="color: #94671E;">PERLU PERBAIKAN BUKTI</span>
                                </div>
                                <h2 class="status-main-heading" style="color: #94671E;">Perlu perbaikan bukti pembayaran</h2>
                                <p class="body-text">
                                    {{ $data['internal_notes'] ?? 'Bukti pembayaran yang Anda kirimkan memerlukan perbaikan. Silakan unggah kembali bukti transfer yang lebih jelas.' }}
                                </p>
                                <div style="margin-top: 14px;">
                                    <a href="{{ route('konfirmasi', ['order' => $data['order_code']]) }}" class="btn btn--fill btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                                        Unggah Ulang Bukti Pembayaran @include('partials.arrow')
                                    </a>
                                </div>
                            @elseif($statusKey === 'menunggu-pembayaran')
                                <div class="status-eyebrow-row">
                                    <img src="{{ asset('images/icon-clock.svg') }}" alt="" width="18" height="18">
                                    <span class="eyebrow">MENUNGGU PEMBAYARAN</span>
                                </div>
                                <h2 class="status-main-heading">Menunggu transfer pembayaran</h2>
                                <p class="body-text">
                                    Silakan lakukan pembayaran sesuai instruksi invoice dan unggah bukti transfer agar pesanan Anda dapat diproses studio.
                                </p>
                                <div style="margin-top: 14px;">
                                    <a href="{{ route('konfirmasi', ['order' => $data['order_code']]) }}" class="btn btn--fill btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                                        Konfirmasi Pembayaran Sekarang @include('partials.arrow')
                                    </a>
                                </div>
                            @else
                                <div class="status-eyebrow-row">
                                    <img src="{{ asset('images/icon-clock.svg') }}" alt="" width="18" height="18">
                                    <span class="eyebrow">MENUNGGU TINJAUAN STUDIO</span>
                                </div>
                                <h2 class="status-main-heading">Bukti pembayaran sedang diperiksa</h2>
                                <p class="body-text">
                                    Bukti Anda sudah diterima. Tim kami sedang meninjau kelengkapan pembayaran dan konfirmasi ketersediaan sesi.
                                </p>
                            @endif
                        </div>

                        {{-- Panduan Status Banner --}}
                        @if($statusKey === 'dikonfirmasi')
                            <div class="notice-readonly-proof">
                                <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="18" height="18">
                                <div>
                                    <h4 class="notice-readonly-title">Sampai jumpa pada {{ $data['session_date'] }}</h4>
                                    <p class="notice-readonly-desc">Sesi berlangsung pukul {{ $data['session_time'] }}. Tim kami siap mengabadikan momen istimewa Anda.</p>
                                </div>
                            </div>
                        @elseif($statusKey === 'perbaikan-bukti')
                            <div class="notice-readonly-proof" style="background:#FEF3C7; border: 1px solid #FDE68A;">
                                <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="18" height="18">
                                <div>
                                    <h4 class="notice-readonly-title" style="color: #94671E;">Perhatian: Perbaikan Bukti Diperlukan</h4>
                                    <p class="notice-readonly-desc" style="color: #78350F;">Harap segera mengunggah bukti transfer yang valid agar jadwal sesi Anda tidak terlewat.</p>
                                </div>
                            </div>
                        @else
                            <div class="notice-readonly-proof">
                                <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="18" height="18">
                                <div>
                                    <h4 class="notice-readonly-title">Pemeriksaan bukti oleh studio naro capture</h4>
                                    <p class="notice-readonly-desc">Mohon tunggu hasil verifikasi. Anda akan diberi tahu setelah pembayaran disetujui.</p>
                                </div>
                            </div>
                        @endif

                        <hr class="divider">

                        {{-- LINIMASA PERJALANAN PESANAN (FIGMA 95:1555 & 105:8769) --}}
                        <div class="timeline-container">
                            <h3 class="subheader-title font-20" style="margin-bottom: 20px;">Perjalanan pesanan Anda</h3>

                            <div class="timeline-list">
                                {{-- Step 1: Pesanan Dibuat --}}
                                <div class="timeline-item">
                                    <div class="timeline-track">
                                        <div class="timeline-node is-done">
                                            <img src="{{ asset('images/check.svg') }}" alt="✓" width="12" height="12">
                                        </div>
                                        <div class="timeline-line is-done"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-content-header">
                                            <strong class="timeline-step-title">Pesanan dibuat</strong>
                                            <span class="timeline-badge badge-done">DILALUI</span>
                                        </div>
                                        <p class="timeline-step-desc">Paket, rencana jadwal, dan data pemesan telah diterima studio.</p>
                                    </div>
                                </div>

                                {{-- Step 2: Menunggu Pembayaran / Bukti Diterima --}}
                                <div class="timeline-item">
                                    <div class="timeline-track">
                                        <div class="timeline-node {{ in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa', 'perbaikan-bukti']) ? 'is-done' : 'is-current' }}">
                                            @if(in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa', 'perbaikan-bukti']))
                                                <img src="{{ asset('images/check.svg') }}" alt="✓" width="12" height="12">
                                            @else
                                                <span class="timeline-dot"></span>
                                            @endif
                                        </div>
                                        <div class="timeline-line {{ in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa']) ? 'is-done' : 'is-upcoming' }}"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-content-header">
                                            <strong class="timeline-step-title">{{ in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa']) ? 'Bukti pembayaran diterima' : 'Pembayaran & bukti transfer' }}</strong>
                                            <span class="timeline-badge {{ in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa']) ? 'badge-done' : 'badge-current' }}">
                                                {{ in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa']) ? 'DILALUI' : 'SAAT INI' }}
                                            </span>
                                        </div>
                                        <p class="timeline-step-desc">
                                            {{ in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa']) ? 'Bukti transfer telah diterima studio dan diproses.' : 'Lakukan pembayaran dan kirimkan bukti transfer.' }}
                                        </p>
                                    </div>
                                </div>

                                @if($statusKey === 'dikonfirmasi')
                                    {{-- Step 3: Verifikasi Pembayaran (DONE) --}}
                                    <div class="timeline-item">
                                        <div class="timeline-track">
                                            <div class="timeline-node is-done">
                                                <img src="{{ asset('images/check.svg') }}" alt="✓" width="12" height="12">
                                            </div>
                                            <div class="timeline-line is-done"></div>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-content-header">
                                                <strong class="timeline-step-title">Verifikasi pembayaran</strong>
                                                <span class="timeline-badge badge-done">DILALUI</span>
                                            </div>
                                            <p class="timeline-step-desc">Pembayaran telah diperiksa dan dinyatakan terverifikasi.</p>
                                        </div>
                                    </div>

                                    {{-- Step 4: Pesanan Dikonfirmasi (CURRENT) --}}
                                    <div class="timeline-item">
                                        <div class="timeline-track">
                                            <div class="timeline-node is-current">
                                                <img src="{{ asset('images/check.svg') }}" alt="✓" width="12" height="12" style="filter: brightness(0) invert(1);">
                                            </div>
                                            <div class="timeline-line is-upcoming"></div>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-content-header">
                                                <strong class="timeline-step-title is-current">Pesanan dikonfirmasi</strong>
                                                <span class="timeline-badge badge-current">SAAT INI</span>
                                            </div>
                                            <p class="timeline-step-desc">Jadwal {{ $data['session_date'] }} telah disetujui studio.</p>
                                        </div>
                                    </div>
                                @else
                                    {{-- Step 3: Bukti Diterima · Sedang Diperiksa (CURRENT) --}}
                                    <div class="timeline-item">
                                        <div class="timeline-track">
                                            <div class="timeline-node is-current">
                                                <span class="timeline-dot"></span>
                                            </div>
                                            <div class="timeline-line is-upcoming"></div>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-content-header">
                                                <strong class="timeline-step-title is-current">
                                                    {{ $statusKey === 'perbaikan-bukti' ? 'Perlu perbaikan bukti' : 'Bukti sedang ditinjau' }}
                                                </strong>
                                                <span class="timeline-badge badge-current">SAAT INI</span>
                                            </div>
                                            <p class="timeline-step-desc">
                                                {{ $statusKey === 'perbaikan-bukti' ? 'Studio meminta unggahan bukti baru yang lebih jelas.' : 'Studio meninjau bukti pembayaran dan ketersediaan jadwal.' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Step 4: Pesanan Dikonfirmasi (UPCOMING) --}}
                                    <div class="timeline-item">
                                        <div class="timeline-track">
                                            <div class="timeline-node is-upcoming">
                                                <span class="timeline-dot"></span>
                                            </div>
                                            <div class="timeline-line is-upcoming"></div>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-content-header">
                                                <strong class="timeline-step-title muted">Pesanan dikonfirmasi</strong>
                                                <span class="timeline-badge badge-upcoming">BERIKUTNYA</span>
                                            </div>
                                            <p class="timeline-step-desc">Konfirmasi jadwal resmi dari tim studio naro capture.</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 5: Sesi Selesai --}}
                                <div class="timeline-item">
                                    <div class="timeline-track">
                                        <div class="timeline-node is-upcoming">
                                            <span class="timeline-dot"></span>
                                        </div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-content-header">
                                            <strong class="timeline-step-title muted">Sesi selesai</strong>
                                            <span class="timeline-badge badge-upcoming">BERIKUTNYA</span>
                                        </div>
                                        <p class="timeline-step-desc">Setelah sesi foto berlangsung dan galeri foto diserahkan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($statusKey === 'dikonfirmasi')
                            <hr class="divider">

                            {{-- PERSIAPAN SESI (FIGMA 105:8820) --}}
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <h3 class="subheader-title font-20">Siapkan sesi cerita Anda</h3>

                                <div class="detail-row">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="detail-label">Panggilan perencanaan</span>
                                        <p class="body-text font-13" style="color:var(--ink);">Tim fotografer akan menghubungi Anda untuk menyepakati lokasi dan panduan gaya.</p>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="detail-label">Kostum &amp; kenyamanan</span>
                                        <p class="body-text font-13" style="color:var(--ink);">Siapkan pilihan kostum yang nyaman dan selaras untuk sesi foto Anda.</p>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="detail-label">Sebelum sesi</span>
                                        <p class="body-text font-13" style="color:var(--ink);">Disarankan tiba 15 menit sebelum sesi dimulai agar persiapan terasa santai.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <hr class="divider">

                        {{-- DOKUMEN PESANAN --}}
                        <div class="tracking-doc-section">
                            <h4 class="subheader-title font-13 font-medium">Dokumen pesanan</h4>
                            <div class="tracking-doc-actions">
                                <a href="{{ route('checkout', ['order' => $data['order_code']]) }}" class="btn btn--outline btn-sm">
                                    {{ $statusKey === 'dikonfirmasi' ? 'Lihat invoice lunas' : 'Lihat invoice' }} @include('partials.arrow')
                                </a>
                                @if(in_array($statusKey, ['dikonfirmasi', 'sedang-diperiksa', 'perbaikan-bukti']))
                                    <a href="{{ route('konfirmasi', ['order' => $data['order_code'], 'status' => 'success']) }}" class="btn btn--outline btn-sm">
                                        Lihat bukti terkirim @include('partials.arrow')
                                    </a>
                                @endif
                            </div>
                            <span class="form-helper-text" style="font-size: 11px; margin-top: 8px; display: block;">
                                Dokumen invoice dan salinan bukti transfer sesi studio naro capture.
                            </span>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: RINCIAN SESI DAN INVOICE (FIGMA 95:1620) --}}
                    <div class="checkout-split-right">
                        <div class="checkout-box">
                            <h3 class="subheader-title font-25" style="margin-bottom: 20px;">Sesi yang Anda rencanakan</h3>

                            {{-- Paket Terpilih --}}
                            <div class="mini-package-row">
                                <img src="{{ asset('images/' . ($data['package_image'] ?? 'paket-family.jpg')) }}" alt="{{ $data['package_name'] }}" class="mini-package-img">
                                <div class="mini-package-info">
                                    <span class="eyebrow">PAKET TERPILIH</span>
                                    <h4 class="mini-package-name">{{ $data['package_name'] }}</h4>
                                    <span class="mini-package-meta">1 sesi · {{ $data['package_duration'] ?? 90 }} menit</span>
                                </div>
                            </div>

                            {{-- Layanan Termasuk --}}
                            <div style="display: flex; flex-direction: column; gap: 8px; margin: 16px 0;">
                                @foreach($data['package_items'] ?? [] as $item)
                                    <div class="mini-feature-item">
                                        <img src="{{ asset('images/check.svg') }}" alt="✓" width="14" height="14">
                                        <span>{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <hr class="divider">

                            {{-- Detail Sesi --}}
                            <div class="checkout-session-details">
                                <div>
                                    <span class="detail-label">Tanggal</span>
                                    <strong class="detail-value">{{ $data['session_date'] }}</strong>
                                </div>
                                <div>
                                    <span class="detail-label">Waktu</span>
                                    <strong class="detail-value">{{ $data['session_time'] }}</strong>
                                </div>
                                <div>
                                    <span class="detail-label">Lokasi</span>
                                    <strong class="detail-value">{{ $data['lokasi'] }}</strong>
                                </div>
                            </div>

                            <hr class="divider">

                            {{-- Data Pemesan --}}
                            <div class="checkout-session-details">
                                <div>
                                    <span class="detail-label">Nama pemesan</span>
                                    <strong class="detail-value">{{ $data['nama_lengkap'] }}</strong>
                                </div>
                                <div>
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value">{{ $maskedEmail }}</span>
                                </div>
                                <div>
                                    <span class="detail-label">WhatsApp</span>
                                    <span class="detail-value">{{ $maskedPhone }}</span>
                                </div>
                            </div>
                            <span class="form-helper-text" style="font-size: 11px;">Kontak disamarkan untuk menjaga privasi Anda.</span>

                            <hr class="divider">

                            {{-- Nomor Invoice & Total --}}
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div>
                                    <span class="detail-label">Nomor invoice</span>
                                    <strong class="detail-value font-mono">{{ $data['invoice_number'] }}</strong>
                                </div>

                                <div class="total-invoice-card" style="background:#F5F8FF; padding:20px; border-radius:6px; display:flex; flex-direction:column; gap:8px;">
                                    <span class="font-13" style="color:#3B5B9A;">Total pada invoice</span>
                                    <span class="font-36 font-semibold" style="color:var(--ink);">{{ $data['package_price'] }}</span>
                                    <span class="font-11 muted">Dalam rupiah (IDR) · sesuai harga paket</span>
                                    @if($statusKey === 'dikonfirmasi')
                                        <span class="font-12 font-medium" style="color:#25745D; margin-top:4px;">● Pembayaran terverifikasi · Jadwal sesi telah disetujui studio</span>
                                    @elseif($statusKey === 'perbaikan-bukti')
                                        <span class="font-12 font-medium" style="color:#94671E; margin-top:4px;">● Perlu perbaikan bukti transfer</span>
                                    @else
                                        <span class="font-12 font-medium" style="color:#3B5B9A; margin-top:4px;">● Pembayaran belum terverifikasi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            {{-- HASIL TIDAK DITEMUKAN --}}
            <section class="tracking-results-section">
                <div class="checkout-box" style="text-align: center; padding: 48px 24px; background: #fff; border: 1px solid #E1E7F0; border-radius: 12px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                    <h3 class="card-title font-20" style="margin-bottom: 8px;">Pesanan tidak ditemukan</h3>
                    <p class="body-text font-14 muted" style="max-width: 480px; margin: 0 auto 16px;">
                        Tidak dapat menemukan pesanan dengan kode “{{ $searchCode }}”. Pastikan kode pesanan atau kontak yang Anda masukkan sesuai saat melakukan pemesanan.
                    </p>
                    <a href="{{ route('cek-pesanan') }}" class="btn btn--outline btn-sm">
                        Reset pencarian
                    </a>
                </div>
            </section>
        @endif
    @endif

    {{-- BANTUAN PESANAN & PANDUAN PENELUSURAN (FIGMA 95:1684) --}}
    <section class="tracking-faq-section">
        {{-- Banner Hubungi Studio --}}
        <div class="tracking-contact-banner">
            <div style="display: flex; align-items: center; gap: 20px;">
                <img src="{{ asset('images/icon-email.svg') }}" alt="" width="24" height="24" style="flex-shrink:0;">
                <div>
                    <h3 class="subheader-title font-20">Ada yang ingin Anda tanyakan?</h3>
                    <p class="body-text" style="font-size: 13px;">Hubungi hello@narocapture.com dan sertakan kode pesanan Anda. Jangan kirim PIN, kata sandi, atau kode OTP.</p>
                </div>
            </div>
            <a href="mailto:hello@narocapture.com" class="btn btn--outline" style="white-space: nowrap;">
                Hubungi studio @include('partials.arrow')
            </a>
        </div>

        {{-- 3 Kolom FAQ --}}
        <div class="tracking-faq-grid">
            <div class="tracking-faq-card">
                <h4>Lupa kode pesanan?</h4>
                <p>Kode tersedia di checkout, invoice, dan konfirmasi pembayaran. Periksa juga email (termasuk spam) atau WhatsApp dari naro capture. Kode pesanan berbeda dari nomor invoice.</p>
                <a href="mailto:hello@narocapture.com" class="link-blue font-12">Belum menemukannya? Hubungi studio dari kontak yang digunakan saat pemesanan.</a>
            </div>

            <div class="tracking-faq-card">
                <h4>Jika status Anda “Menunggu pembayaran”</h4>
                <p>Ikuti instruksi pembayaran resmi pada invoice atau dari studio. Setelah transfer, kirimkan satu bukti melalui halaman konfirmasi pembayaran.</p>
            </div>

            <div class="tracking-faq-card">
                <h4>Jika status Anda “Perlu perbaikan bukti”</h4>
                <p>Baca catatan dari studio, lalu kirim bukti yang lebih jelas atau data yang diperbaiki melalui halaman konfirmasi pembayaran.</p>
            </div>
        </div>
    </section>
</main>
@endsection
