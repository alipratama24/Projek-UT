@extends('layouts.app')

@section('title', 'Galeri Foto — naro capture')

@section('content')
<main class="gallery-page">
    {{-- PENGANTAR HALAMAN GALERI (FIGMA 105:45334) --}}
    <section class="checkout-hero">
        <div class="booking-breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="sep">/</span>
            <a href="{{ route('cek-pesanan') }}">Pesanan</a>
            <span class="sep">/</span>
            <span>{{ $data['order_code'] ?? 'CH-2026-001' }}</span>
        </div>

        <div class="checkout-header">
            <div class="checkout-header__left">
                <span class="eyebrow">TINJAUAN &amp; HASIL SESI</span>
                <h1 class="checkout-header__title">Cerita keluarga, untuk dikenang.</h1>
            </div>
            <p class="body-text checkout-header__desc">
                Galeri pribadi {{ $data['nama_lengkap'] ?? 'Pelanggan' }}. Nikmati kembali momen kebersamaan, unduh foto pilihan Anda, dan simpan cerita ini dengan baik.
            </p>
        </div>

        <div class="tracking-results-header">
            <span class="font-20 font-normal" style="color:var(--blue);">{{ $data['order_code'] ?? 'CH-2026-001' }}</span>
            <span class="badge-tag" style="background:#E8F0FF; color:#3B5B9A; padding:6px 12px; font-size:11px;">
                {{ $gallery ? ($gallery->status . ' · Galeri Digital') : 'Tahap Lanjutan · Galeri Digital' }}
            </span>
        </div>
        <hr class="divider">
    </section>

    {{-- FEEDBACK NOTICE (SUCCESS / ERROR) --}}
    @if(session('success'))
        <div style="max-width: 1200px; margin: 0 auto 20px; padding: 14px 20px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; color: #065F46; font-size: 14px;">
            <strong>Berhasil:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="max-width: 1200px; margin: 0 auto 20px; padding: 14px 20px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; color: #991B1B; font-size: 14px;">
            <strong>Perhatian:</strong> {{ session('error') }}
        </div>
    @endif

    @if(!empty($isLocked))
        {{-- LAYAR VERIFIKASI PIN GALERI PRIBADI --}}
        <section class="gallery-content-section" style="padding-top: 40px; padding-bottom: 80px;">
            <div style="max-width: 480px; margin: 0 auto; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 40px 32px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); text-align: center;">
                <div style="width: 56px; height: 56px; background: #EEF2F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #253247;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>

                <span class="eyebrow" style="color: #6E7A8B; letter-spacing: 0.1em; font-size: 11px;">RUANG PRIBADI KLIEN</span>
                <h2 style="font-size: 22px; font-weight: 600; color: #1E293B; margin: 8px 0 12px;">{{ $gallery->title ?? 'Galeri Privat' }}</h2>
                <p style="font-size: 13px; color: #64748B; line-height: 1.6; margin-bottom: 24px;">
                    Galeri ini diproteksi untuk menjaga privasi sesi foto Anda. Silakan masukkan <strong>Kode PIN</strong> yang diberikan oleh studio untuk membuka akses.
                </p>

                <form action="{{ route('galeri-klien.verify-pin') }}" method="POST">
                    @csrf
                    <input type="hidden" name="gallery_id" value="{{ $gallery->id }}">
                    
                    <div style="margin-bottom: 20px;">
                        <input type="password" name="pin_code" placeholder="Masukkan Kode PIN" required autofocus
                            style="width: 100%; height: 48px; text-align: center; font-size: 18px; letter-spacing: 4px; font-weight: 600; border: 1.5px solid #CBD5E1; border-radius: 6px; outline: none; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='#3B5B9A'" onblur="this.style.borderColor='#CBD5E1'">
                    </div>

                    <button type="submit" class="btn btn--fill btn--block" style="background: #3B5B9A; color: #FFF; height: 46px; font-size: 14px; font-weight: 500;">
                        Buka Galeri Foto @include('partials.arrow')
                    </button>
                </form>

                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #F1F5F9; font-size: 12px; color: #94A3B8;">
                    Lupa PIN Anda? Silakan hubungi admin di <a href="mailto:hello@narocapture.com" style="color: #3B5B9A; text-decoration: underline;">hello@narocapture.com</a>
                </div>
            </div>
        </section>
    @else
    {{-- ISI GALERI (FIGMA 105:45347) --}}
    <section class="gallery-content-section">
        {{-- Banner Panduan Status --}}
        @if(!$gallery)
            <div class="notice-readonly-proof">
                <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="20" height="20">
                <div>
                    <h4 class="notice-readonly-title" style="font-weight:600;">Galeri pribadi studio</h4>
                    <p class="notice-readonly-desc">
                        Halaman ini menampilkan pratinjau galeri digital studio. Foto resolusi tinggi dapat diunduh langsung setelah proses penyuntingan selesai.
                    </p>
                </div>
            </div>
        @endif

        {{-- Sampul dan Rincian Galeri --}}
        <div class="gallery-header-cover-grid">
            <div class="gallery-cover-img-wrap">
                <img src="{{ $coverImage ?? asset('images/work-1.jpg') }}" alt="{{ $data['package_name'] ?? 'Foto Sampul' }}" class="gallery-cover-img">
            </div>

            <div class="gallery-meta-card">
                <span class="badge-tag" style="background:#E8F0FF; color:#3B5B9A; padding:6px 14px; font-size:11px; width:fit-content;">
                    {{ count($photos) }} foto diedit · Telah diserahkan
                </span>
                <h2 class="card-title font-25">{{ $data['package_name'] ?? 'Cerita Keluarga' }}</h2>

                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <span class="detail-label">Pemesan</span>
                        <strong class="detail-value">{{ $data['nama_lengkap'] ?? 'Pelanggan' }}</strong>
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
                        <span class="detail-label">Pesanan / Invoice</span>
                        <strong class="detail-value">{{ $data['order_code'] ?? 'CH-2026-001' }}</strong>
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
                    Koleksi foto sesi Anda yang telah dikurasi dan disunting dengan cermat. Menampilkan {{ count($photos) }} foto.
                </p>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" class="btn btn--outline btn-sm" id="btnOpenSlideshow" style="display:inline-flex; align-items:center; gap:6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                    Lihat Tayangan Foto
                </button>
                <a href="{{ route('galeri-klien.download-all', ['slug' => request('slug')]) }}" class="btn btn--fill btn-sm" id="btnDownloadAll" style="background:#3B5B9A; color:#FFF; display:inline-flex; align-items:center; gap:6px; text-decoration:none;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span id="btnDownloadAllText">Unduh Semua Foto</span>
                </a>
            </div>
        </div>

        {{-- Grid Koleksi Foto --}}
        <div class="gallery-grid-3">
            @foreach($photos as $photo)
                <div class="gallery-photo-card">
                    <div class="gallery-photo-img-wrap js-open-photo" data-index="{{ $loop->index }}" style="cursor: pointer;" title="Klik untuk melihat foto lebih besar">
                        <img src="{{ $photo['img'] }}" alt="{{ $photo['title'] }}" class="gallery-photo-img" loading="lazy">
                    </div>
                    <div class="gallery-photo-info">
                        <strong class="gallery-photo-title">{{ $photo['title'] }}</strong>
                        <div class="gallery-photo-actions-wrap" style="display:flex; align-items:center; gap:8px;">
                            <button type="button" class="gallery-action-link js-open-photo" data-index="{{ $loop->index }}" title="Lihat foto">Lihat</button>
                            <span class="sep-dot">·</span>
                            <a href="{{ $photo['download_url'] }}" class="gallery-action-link js-download-single" data-title="{{ $photo['title'] }}" title="Unduh {{ $photo['title'] }}">Unduh &darr;</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Navigasi Galeri / Pagination --}}
        <div class="gallery-pagination">
            <span class="body-text font-11 muted">Menampilkan {{ count($photos) }} foto</span>
            <div style="display:flex; align-items:center; gap:16px;">
                <button type="button" class="btn btn--outline btn-sm" disabled style="opacity:0.45; cursor:not-allowed;">Sebelumnya</button>
                <div class="gallery-pagination-numbers">
                    <strong style="color:var(--ink);">1</strong>
                </div>
                <button type="button" class="btn btn--outline btn-sm" disabled style="opacity:0.45; cursor:not-allowed;">Berikutnya</button>
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
                    <p class="body-text font-13">Hubungi hello@narocapture.com dan sertakan kode pesanan Anda. Jangan kirim PIN, kata sandi, atau kode OTP.</p>
                </div>
            </div>
            <a href="mailto:hello@narocapture.com" class="btn btn--outline" style="white-space: nowrap;">
                Hubungi Studio
            </a>
        </div>
    </section>

    {{-- LIGHTBOX SLIDESHOW MODAL --}}
    <div id="galleryLightboxModal" class="gallery-lightbox" aria-hidden="true">
        <div class="gallery-lightbox__backdrop" id="lightboxBackdrop"></div>
        <div class="gallery-lightbox__container" role="dialog" aria-modal="true" aria-label="Tayangan Foto">
            {{-- Header Modal --}}
            <div class="gallery-lightbox__header">
                <div class="gallery-lightbox__meta">
                    <span class="gallery-lightbox__title" id="lightboxTitle">Foto Studio</span>
                    <span class="gallery-lightbox__counter" id="lightboxCounter">1 / 6</span>
                </div>
                <div class="gallery-lightbox__controls">
                    <a href="#" id="lightboxDownloadBtn" class="gallery-lightbox__btn js-download-single" data-title="Foto Studio" title="Unduh foto ini">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span>Unduh Foto Ini</span>
                    </a>
                    <button type="button" class="gallery-lightbox__btn gallery-lightbox__btn--close" id="lightboxCloseBtn" title="Tutup (Esc)" aria-label="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            </div>

            {{-- Main Stage Slideshow --}}
            <div class="gallery-lightbox__stage">
                <button type="button" class="gallery-lightbox__nav gallery-lightbox__nav--prev" id="lightboxPrevBtn" aria-label="Foto sebelumnya" title="Sebelumnya (←)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>

                <div class="gallery-lightbox__image-wrap">
                    <img id="lightboxImage" src="" alt="Foto" class="gallery-lightbox__image">
                </div>

                <button type="button" class="gallery-lightbox__nav gallery-lightbox__nav--next" id="lightboxNextBtn" aria-label="Foto berikutnya" title="Berikutnya (→)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>

            {{-- Thumbnail Strip --}}
            <div class="gallery-lightbox__thumbs" id="lightboxThumbStrip">
                @foreach($photos as $photo)
                    <div class="gallery-lightbox__thumb {{ $loop->first ? 'is-active' : '' }}" data-index="{{ $loop->index }}" title="{{ $photo['title'] }}">
                        <img src="{{ $photo['img'] }}" alt="{{ $photo['title'] }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- TOAST NOTIFICATION --}}
    <div id="galleryToast" class="gallery-toast" role="status" aria-live="polite">
        <div class="gallery-toast__icon" id="galleryToastIcon">✓</div>
        <div class="gallery-toast__message" id="galleryToastMsg">Notifikasi</div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const photos = @json($photos);
    const totalPhotos = photos.length;
    let currentIndex = 0;

    const modal = document.getElementById('galleryLightboxModal');
    const backdrop = document.getElementById('lightboxBackdrop');
    const closeBtn = document.getElementById('lightboxCloseBtn');
    const prevBtn = document.getElementById('lightboxPrevBtn');
    const nextBtn = document.getElementById('lightboxNextBtn');
    const lightboxImg = document.getElementById('lightboxImage');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxCounter = document.getElementById('lightboxCounter');
    const lightboxDownloadBtn = document.getElementById('lightboxDownloadBtn');
    const thumbItems = document.querySelectorAll('.gallery-lightbox__thumb');

    const toast = document.getElementById('galleryToast');
    const toastIcon = document.getElementById('galleryToastIcon');
    const toastMsg = document.getElementById('galleryToastMsg');
    let toastTimeout = null;

    function showToast(message, type = 'success') {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        if (type === 'info') {
            toastIcon.textContent = 'ℹ';
            toastIcon.style.background = '#3B5B9A';
        } else {
            toastIcon.textContent = '✓';
            toastIcon.style.background = '#10B981';
        }
        toast.classList.add('is-visible');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 3200);
    }

    function updateLightbox(index) {
        if (!photos || photos.length === 0) return;
        if (index < 0) index = totalPhotos - 1;
        if (index >= totalPhotos) index = 0;
        currentIndex = index;

        const current = photos[currentIndex];
        lightboxImg.classList.add('is-loading');
        lightboxImg.src = current.img;
        lightboxImg.alt = current.title;
        lightboxTitle.textContent = current.title;
        lightboxCounter.textContent = (currentIndex + 1) + ' / ' + totalPhotos;
        lightboxDownloadBtn.href = current.download_url;
        lightboxDownloadBtn.setAttribute('data-title', current.title);

        lightboxImg.onload = function () {
            lightboxImg.classList.remove('is-loading');
        };

        thumbItems.forEach((thumb, idx) => {
            if (idx === currentIndex) {
                thumb.classList.add('is-active');
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('is-active');
            }
        });
    }

    function openLightbox(index = 0) {
        updateLightbox(index);
        modal.classList.add('is-active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        modal.classList.remove('is-active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    // Tombol Lihat Tayangan Foto di Header
    const btnOpenSlideshow = document.getElementById('btnOpenSlideshow');
    if (btnOpenSlideshow) {
        btnOpenSlideshow.addEventListener('click', function () {
            openLightbox(0);
        });
    }

    // Buka foto dari tombol Lihat atau kartu gambar
    document.querySelectorAll('.js-open-photo').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            const index = parseInt(this.getAttribute('data-index') || 0, 10);
            openLightbox(index);
        });
    });

    // Kontrol navigasi slide
    if (prevBtn) {
        prevBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            updateLightbox(currentIndex - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            updateLightbox(currentIndex + 1);
        });
    }

    // Klik thumbnail di bawah modal
    thumbItems.forEach(thumb => {
        thumb.addEventListener('click', function (e) {
            e.stopPropagation();
            const index = parseInt(this.getAttribute('data-index') || 0, 10);
            updateLightbox(index);
        });
    });

    // Tutup modal
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (backdrop) backdrop.addEventListener('click', closeLightbox);

    // Keyboard navigation
    document.addEventListener('keydown', function (e) {
        if (!modal.classList.contains('is-active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') updateLightbox(currentIndex - 1);
        if (e.key === 'ArrowRight') updateLightbox(currentIndex + 1);
    });

    // Feedback Unduh Per Foto
    document.addEventListener('click', function (e) {
        const downloadBtn = e.target.closest('.js-download-single');
        if (downloadBtn) {
            const title = downloadBtn.getAttribute('data-title') || 'Foto';
            showToast('Mengunduh ' + title + '...', 'info');
        }
    });

    // Feedback Tombol Unduh Semua Foto
    const btnDownloadAll = document.getElementById('btnDownloadAll');
    const btnDownloadAllText = document.getElementById('btnDownloadAllText');
    if (btnDownloadAll) {
        btnDownloadAll.addEventListener('click', function () {
            const originalText = btnDownloadAllText ? btnDownloadAllText.textContent : 'Unduh Semua Foto';
            if (btnDownloadAllText) {
                btnDownloadAllText.textContent = 'Menyiapkan ZIP...';
            }
            btnDownloadAll.style.opacity = '0.75';
            btnDownloadAll.style.pointerEvents = 'none';

            showToast('Menyiapkan arsip ZIP seluruh foto...', 'info');

            setTimeout(function () {
                if (btnDownloadAllText) {
                    btnDownloadAllText.textContent = originalText;
                }
                btnDownloadAll.style.opacity = '';
                btnDownloadAll.style.pointerEvents = '';
                showToast('Arsip ZIP berhasil disiapkan dan mulai diunduh!');
            }, 2500);
        });
    }
});
</script>
@endsection
