@extends('layouts.app')

@section('title', 'Galeri Foto — Clara Hayes Photography')

@section('content')
<main class="gallery-page">
    {{-- PENGANTAR HALAMAN GALERI (FIGMA 105:45334) --}}
    <section class="checkout-hero">
        <div class="booking-breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="sep">/</span>
            <a href="{{ route('cek-pesanan') }}">Pesanan</a>
            <span class="sep">/</span>
            <span>{{ $data['order_code'] ?? 'CH-CONTOH-001' }}</span>
        </div>

        <div class="checkout-header">
            <div class="checkout-header__left">
                <span class="eyebrow">TINJAUAN &amp; HASIL SESI</span>
                <h1 class="checkout-header__title">Cerita keluarga, untuk dikenang.</h1>
            </div>
            <p class="body-text checkout-header__desc">
                Galeri pribadi {{ $data['nama_lengkap'] ?? 'Ayu Pramesti' }}. Nikmati kembali momen kebersamaan, unduh foto pilihan Anda, dan simpan cerita ini dengan baik.
            </p>
        </div>

        <div class="tracking-results-header">
            <span class="font-20 font-normal" style="color:var(--blue);">{{ $data['order_code'] ?? 'CH-CONTOH-001' }}</span>
            <span class="badge-tag" style="background:#E8F0FF; color:#3B5B9A; padding:6px 12px; font-size:11px;">
                Tahap lanjutan · Contoh galeri siap
            </span>
        </div>
        <hr class="divider">
    </section>

    {{-- ISI GALERI (FIGMA 105:45347) --}}
    <section class="gallery-content-section">
        {{-- Banner Panduan Status --}}
        <div class="notice-readonly-proof">
            <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="20" height="20">
            <div>
                <h4 class="notice-readonly-title" style="font-weight:600;">Galeri pribadi · contoh tampilan</h4>
                <p class="notice-readonly-desc">
                    Foto pada halaman ini adalah ilustrasi dari foto paket keluarga, digunakan ulang untuk memperlihatkan tampilan galeri. Tidak ada galeri pelanggan asli, tautan berbagi, atau pengiriman foto nyata.
                </p>
            </div>
        </div>

        {{-- Sampul dan Rincian Galeri --}}
        <div class="gallery-header-cover-grid">
            <div class="gallery-cover-img-wrap">
                <img src="{{ asset('images/work-1.png') }}" alt="Foto Sampul Keluarga" class="gallery-cover-img">
            </div>

            <div class="gallery-meta-card">
                <span class="badge-tag" style="background:#E8F0FF; color:#3B5B9A; padding:6px 14px; font-size:11px; width:fit-content;">
                    50 foto diedit · Telah diserahkan
                </span>
                <h2 class="card-title font-25">{{ $data['package_name'] ?? 'Cerita Keluarga' }}</h2>

                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <span class="detail-label">Pemesan</span>
                        <strong class="detail-value">{{ $data['nama_lengkap'] ?? 'Ayu Pramesti' }}</strong>
                    </div>

                    <div>
                        <span class="detail-label">Sesi selesai</span>
                        <strong class="detail-value">{{ $data['session_date'] ?? 'Sabtu, 17 Oktober 2026' }}</strong>
                        <span class="body-text font-12" style="display:block; margin-top:2px;">{{ $data['session_time'] ?? '15.30–17.00 WIB · 90 menit' }}</span>
                    </div>

                    <div>
                        <span class="detail-label">Lokasi sesi</span>
                        <strong class="detail-value">{{ $data['lokasi'] ?? 'Rumah keluarga di Jakarta Selatan & taman terdekat' }}</strong>
                    </div>

                    <hr class="divider">

                    <div>
                        <span class="detail-label">Pesanan / invoice contoh</span>
                        <strong class="detail-value">{{ $data['order_code'] ?? 'CH-CONTOH-001' }}</strong>
                        <span class="body-text font-12" style="display:block;">{{ $data['invoice_number'] ?? 'INV-CH-2026-001' }}</span>
                    </div>

                    <span class="body-text font-11 muted">
                        Pembayaran terverifikasi · Galeri siap dilihat<br>
                        {{ $maskedEmail ?? 'a•••@gmail.com' }} · {{ $maskedPhone ?? '+62 8•• •••• 7890' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Pengantar Koleksi Foto --}}
        <div class="tracking-results-header" style="align-items:flex-end;">
            <div>
                <h3 class="card-title font-25">Galeri foto Anda</h3>
                <p class="body-text font-13" style="margin-top:4px;">
                    50 foto diedit telah diserahkan dalam contoh keadaan ini. Menampilkan 6 dari 50 foto · Halaman 1 dari 9.
                </p>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" class="btn btn--outline btn-sm">Lihat Tayangan Foto</button>
                <button type="button" class="btn btn--fill btn-sm" style="background:#3B5B9A; color:#FFF;">Unduh Semua Foto</button>
            </div>
        </div>

        {{-- Grid Koleksi Foto (6 Foto) --}}
        <div class="gallery-grid-3">
            @php
                $photos = [
                    ['img' => 'work-1.png', 'title' => 'Keluarga — 01'],
                    ['img' => 'work-2.png', 'title' => 'Keluarga — 02'],
                    ['img' => 'work-3.png', 'title' => 'Keluarga — 03'],
                    ['img' => 'work-4.png', 'title' => 'Keluarga — 04'],
                    ['img' => 'work-5.png', 'title' => 'Keluarga — 05'],
                    ['img' => 'work-6.png', 'title' => 'Keluarga — 06'],
                ];
            @endphp

            @foreach($photos as $photo)
                <div class="gallery-photo-card">
                    <div class="gallery-photo-img-wrap">
                        <img src="{{ asset('images/' . $photo['img']) }}" alt="{{ $photo['title'] }}" class="gallery-photo-img">
                    </div>
                    <div class="gallery-photo-info">
                        <strong class="gallery-photo-title">{{ $photo['title'] }}</strong>
                        <span class="gallery-photo-actions">Lihat &nbsp;·&nbsp; Unduh ↓</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Navigasi Galeri / Pagination --}}
        <div class="gallery-pagination">
            <span class="body-text font-11 muted">Menampilkan 6 dari 50 foto · Foto ilustrasi untuk contoh antarmuka</span>
            <div style="display:flex; align-items:center; gap:16px;">
                <button type="button" class="btn btn--outline btn-sm" disabled style="opacity:0.45; cursor:not-allowed;">Sebelumnya</button>
                <div class="gallery-pagination-numbers">
                    <strong style="color:var(--ink);">1</strong>
                    <span>2</span>
                    <span>3</span>
                    <span>…</span>
                    <span>9</span>
                </div>
                <button type="button" class="btn btn--outline btn-sm">Berikutnya</button>
            </div>
        </div>

        {{-- Panduan Menyimpan Foto --}}
        <div class="gallery-guide-card">
            <div class="gallery-guide-col">
                <h4 class="font-20 font-normal">Simpan untuk hari-hari mendatang.</h4>
                <p class="body-text font-13">Gunakan “Unduh Semua Foto” untuk koleksi lengkap atau unduh foto satu per satu. Paket mencakup galeri pribadi dan izin cetak pribadi.</p>
            </div>
            <div class="gallery-guide-col">
                <h4 class="font-20 font-normal">Ruang pribadi untuk keluarga Anda.</h4>
                <p class="body-text font-13">Jaga akses galeri dan berkas unduhan hanya untuk orang yang Anda percayai. Hubungi studio bila Anda membutuhkan bantuan melihat, mengunduh, atau mencetak foto.</p>
            </div>
        </div>

        {{-- Banner Bantuan Pesanan --}}
        <div class="tracking-contact-banner">
            <div style="display: flex; align-items: center; gap: 20px;">
                <img src="{{ asset('images/icon-email.svg') }}" alt="" width="24" height="24" style="flex-shrink:0;">
                <div>
                    <h3 class="subheader-title font-20">Ada yang ingin Anda tanyakan?</h3>
                    <p class="body-text font-13">Hubungi hello@clarahayes.studio dan sertakan kode pesanan Anda. Jangan kirim PIN, kata sandi, atau kode OTP.</p>
                </div>
            </div>
            <a href="mailto:hello@clarahayes.studio" class="btn btn--outline" style="white-space: nowrap;">
                Hubungi Studio
            </a>
        </div>
    </section>
</main>
@endsection

