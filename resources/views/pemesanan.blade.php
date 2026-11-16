@extends('layouts.app')

@section('title', 'Pemesanan — naro capture')

@php
    $selectedPkg = collect($packages)->firstWhere('id', $selectedPackageId) ?? ($packages[0] ?? null);
@endphp

@section('content')
<style>
.form-control.is-invalid, .custom-checkbox.is-invalid {
    border-color: #DC2626 !important;
    background-color: #FEF2F2 !important;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
}
.invalid-feedback {
    display: block;
    color: #DC2626;
    font-size: 12px;
    margin-top: 4px;
    font-weight: 500;
}
.booking-alert-error {
    margin: 0 0 24px 0;
    padding: 16px 20px;
    background: #FEF2F2;
    border: 1px solid #FCA5A5;
    border-radius: 8px;
    color: #991B1B;
}
.booking-alert-error ul {
    margin: 6px 0 0 0;
    padding-left: 20px;
    font-size: 13px;
    line-height: 1.5;
}
</style>

<main class="booking-page">
    {{-- Breadcrumb & Header --}}
    <section class="booking-hero">
        <div class="booking-breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="sep">/</span>
            <span>Pemesanan</span>
        </div>
        <div class="booking-header">
            <div class="booking-header__left">
                <span class="eyebrow">MARI BUAT KENANGAN YANG BERARTI</span>
                <h1 class="booking-header__title">Rencanakan sesi Anda.</h1>
            </div>
            <p class="body-text booking-header__desc">
                Pilih paket, tentukan waktu, lalu ceritakan sedikit tentang Anda. Saya akan meninjau ketersediaan sebelum mengonfirmasi sesi.
            </p>
        </div>
    </section>

    @if ($errors->any())
        <div class="booking-alert-error" id="serverValidationAlert">
            <div style="font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                Pemesanan belum dapat diproses. Mohon lengkapi atau perbaiki data berikut:
            </div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pemesanan.store') }}" method="POST" id="bookingForm" novalidate>
        @csrf

        {{-- LANGKAH 1: PILIH PAKET LAYANAN --}}
        <section class="booking-step">
            <div class="step-title">
                <div class="step-badge">
                    <span class="step-num">01</span>
                    <h2 class="step-heading">Pilih paket layanan</h2>
                </div>
                <p class="step-desc">Temukan sesi yang paling sesuai dengan cerita Anda.</p>
            </div>

            <div class="package-selection-grid">
                @foreach ($packages as $pkg)
                    <label class="package-card-select {{ $selectedPackageId === $pkg['id'] ? 'is-selected' : '' }}" data-id="{{ $pkg['id'] }}" data-name="{{ $pkg['name'] }}" data-price="{{ $pkg['price'] }}" data-duration="{{ $pkg['duration'] }}" data-location="{{ $pkg['location_note'] }}">
                        <input type="radio" name="package_id" value="{{ $pkg['id'] }}" {{ $selectedPackageId === $pkg['id'] ? 'checked' : '' }} class="sr-only package-radio">
                        <div class="pkg-card__img">
                            <img src="{{ asset('images/' . $pkg['image']) }}" alt="{{ $pkg['name'] }}">
                        </div>
                        <div class="pkg-card__body">
                            <div class="pkg-card__top">
                                <div class="pkg-radio-circle">
                                    <div class="pkg-radio-inner"></div>
                                </div>
                                <span class="pkg-card__name">{{ $pkg['name'] }}</span>
                            </div>
                            <div class="pkg-card__price">
                                <span class="price-val">{{ $pkg['price'] }}</span>
                                <span class="price-unit">/ sesi</span>
                            </div>
                            <hr class="divider">
                            <ul class="pkg-card__features">
                                @foreach ($pkg['items'] as $item)
                                    <li>
                                        <img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14">
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="pkg-card__footer">
                                @if ($selectedPackageId === $pkg['id'])
                                    <span class="pkg-tag">PAKET TERPILIH</span>
                                @else
                                    <span class="pkg-tag-muted">KLIK UNTUK MEMILIH</span>
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- LANGKAH 2 & 3: GRID KOLOM (JADWAL & FORMULIR) --}}
        <div class="booking-split-grid">
            {{-- KOLOM KIRI: KALENDER & RINGKASAN --}}
            <div class="booking-split-left">
                {{-- KALENDER & WAKTU --}}
                <div class="booking-box">
                    <div class="step-title">
                        <div class="step-badge">
                            <span class="step-num">02</span>
                            <h2 class="step-heading">Pilih tanggal &amp; waktu</h2>
                        </div>
                        <p class="step-desc">Pilih jadwal yang tersedia untuk sesi Anda.</p>
                    </div>

                    {{-- Header Bulan & Navigasi Bulan --}}
                    <div class="calendar-month-header">
                        <h3 class="month-title" id="currentMonthYear">{{ $bookingMonth->translatedFormat('F Y') }}</h3>
                        <div class="month-nav">
                            <button type="button" class="btn-icon" id="btnPrevMonth" aria-label="Bulan sebelumnya" title="Bulan sebelumnya">
                                <img src="{{ asset('images/chevron-left.svg') }}" alt="‹" width="16" height="16">
                            </button>
                            <button type="button" class="btn-icon" id="btnNextMonth" aria-label="Bulan berikutnya" title="Bulan berikutnya">
                                <img src="{{ asset('images/chevron-right.svg') }}" alt="›" width="16" height="16">
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Kalender --}}
                    <div class="calendar-grid">
                        <div class="cal-days-header">
                            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                        </div>
                        <div class="cal-dates">
                            {{-- Padding hari sebelum tanggal 1 --}}
                            @for ($p = 0; $p < $dayOfWeekOffset; $p++)
                                <div class="cal-date is-disabled">{{ $startOfMonth->copy()->subDays($dayOfWeekOffset - $p)->day }}</div>
                            @endfor

                            {{-- Hari-hari bulan ini --}}
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $dateObj = $bookingMonth->copy()->day($day);
                                    $dateStr = $dateObj->format('Y-m-d');
                                    $dateLabel = $dateObj->translatedFormat('l, d F Y');
                                    $isPast = $dateObj->isBefore($today);
                                    $isSelected = $dateStr === $defaultSelectedDate->format('Y-m-d');
                                @endphp
                                @if($isPast)
                                    <div class="cal-date is-disabled" title="Sudah lewat">{{ $day }}</div>
                                @else
                                    <div class="cal-date is-available {{ $isSelected ? 'is-selected' : '' }}" data-date="{{ $dateStr }}" data-label="{{ $dateLabel }}">{{ $day }}</div>
                                @endif
                            @endfor

                            {{-- Padding hari sesudah tanggal terakhir --}}
                            @php
                                $totalRendered = $dayOfWeekOffset + $daysInMonth;
                                $remainingPadding = (7 - ($totalRendered % 7)) % 7;
                            @endphp
                            @for ($p = 1; $p <= $remainingPadding; $p++)
                                <div class="cal-date is-disabled">{{ $p }}</div>
                            @endfor
                        </div>
                    </div>

                    {{-- Legenda --}}
                    <div class="cal-legend">
                        <div class="cal-legend__item">
                            <span class="legend-dot dot-available"></span>
                            <span>Tersedia</span>
                        </div>
                        <div class="cal-legend__item">
                            <span class="legend-dot dot-selected"></span>
                            <span>Dipilih</span>
                        </div>
                        <div class="cal-legend__item">
                            <span class="legend-dot dot-disabled"></span>
                            <span>Tidak tersedia</span>
                        </div>
                    </div>

                    <input type="hidden" name="session_date" id="selectedDateInput" value="{{ $defaultSelectedDate->format('Y-m-d') }}">

                    <hr class="divider">

                    {{-- Pilihan Jam --}}
                    <div class="time-selection-wrap">
                        <div class="time-header">
                            <span class="time-date-label" id="timeDateDisplay">{{ $defaultSelectedDate->translatedFormat('l, d F Y') }}</span>
                            <span class="small-caps">WIB</span>
                        </div>
                        @error('session_time')
                            <div class="invalid-feedback" style="margin-bottom: 8px;">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback client-error" id="err_session_time" style="display:none; margin-bottom: 8px;"></div>
                        <div class="time-slots" id="timeSlotsContainer">
                            @forelse($initialSlots as $slot)
                                @if($slot['is_available'])
                                    <label class="time-slot-btn {{ ($initialSelectedSlot === $slot['time']) ? 'is-selected' : '' }}">
                                        <input type="radio" name="session_time" value="{{ $slot['time'] }}" {{ ($initialSelectedSlot === $slot['time']) ? 'checked' : '' }} class="sr-only time-radio">
                                        <span class="slot-time">{{ $slot['time'] }}</span>
                                        <span class="slot-status">{{ ($initialSelectedSlot === $slot['time']) ? 'Dipilih' : 'Tersedia' }}</span>
                                    </label>
                                @else
                                    <div class="time-slot-btn is-full" title="{{ $slot['note'] ?? $slot['status'] }}">
                                        <span class="slot-time">{{ $slot['time'] }}</span>
                                        <span class="slot-status">{{ $slot['status'] }}</span>
                                    </div>
                                @endif
                            @empty
                                <div style="grid-column: 1 / -1; padding: 14px; background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5; border-radius: 6px; font-size: 13px; text-align: center;">
                                    Tidak ada slot sesi yang tersedia pada tanggal ini. Silakan pilih tanggal lain yang bertanda hijau.
                                </div>
                            @endforelse
                        </div>
                        <p class="time-duration-note" id="timeDurationNote">Durasi {{ $selectedPkg['name'] ?? 'Family Session' }}: {{ $selectedPkg['duration'] ?? 90 }} menit</p>
                    </div>
                </div>

                {{-- RINGKASAN PEMESANAN --}}
                <div class="booking-box summary-box">
                    <h3 class="summary-title">Sesi yang Anda rencanakan</h3>
                    
                    <div class="summary-item">
                        <img src="{{ asset('images/icon-camera.svg') }}" alt="" class="summary-icon">
                        <div class="summary-item__text">
                            <span class="summary-label">Paket pilihan</span>
                            <strong class="summary-val" id="sumPackageName">{{ $selectedPkg['name'] ?? 'Family Session' }}</strong>
                        </div>
                    </div>

                    <div class="summary-item">
                        <img src="{{ asset('images/icon-calendar.svg') }}" alt="" class="summary-icon">
                        <div class="summary-item__text">
                            <span class="summary-label">Tanggal sesi</span>
                            <strong class="summary-val" id="sumDate">{{ $defaultSelectedDate->translatedFormat('l, d F Y') }}</strong>
                        </div>
                    </div>

                    <div class="summary-item">
                        <img src="{{ asset('images/icon-clock.svg') }}" alt="" class="summary-icon">
                        <div class="summary-item__text">
                            <span class="summary-label">Waktu sesi</span>
                            <strong class="summary-val" id="sumTime">15.30–{{ ($selectedPkg['duration'] ?? 90) === 90 ? '17.00' : '16.30' }} WIB · {{ $selectedPkg['duration'] ?? 90 }} menit</strong>
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="summary-price-row">
                        <span class="summary-price-label">Harga paket / sesi</span>
                        <span class="summary-price-val" id="sumPrice">{{ $selectedPkg['price'] ?? 'Rp2.450.000' }}</span>
                    </div>

                    <div class="summary-notice">
                        <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon">
                        <p>Ini adalah permintaan jadwal, bukan konfirmasi. Sesi dikonfirmasi setelah ketersediaan ditinjau. Belum ada pembayaran pada tahap ini.</p>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORMULIR DATA DIRI --}}
            <div class="booking-split-right">
                <div class="booking-box form-box">
                    <div class="step-title">
                        <div class="step-badge">
                            <span class="step-num">03</span>
                            <h2 class="step-heading">Ceritakan tentang Anda</h2>
                        </div>
                        <p class="step-desc">Lengkapi data berikut agar saya bisa membantu menyiapkan sesi yang personal.</p>
                    </div>

                    <div class="form-section">
                        <div class="form-section__header">
                            <h4 class="form-section__title">Data diri</h4>
                            <span class="form-required-note">* Wajib diisi</span>
                        </div>

                        <div class="form-group">
                            <label for="nama_lengkap" class="form-label">Nama lengkap *</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Contoh: Ayu Pramesti" value="{{ old('nama_lengkap') }}" required>
                            @error('nama_lengkap')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <span class="invalid-feedback client-error" id="err_nama_lengkap" style="display:none;"></span>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="email" class="form-label">Alamat email *</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="nama@email.com" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback client-error" id="err_email" style="display:none;"></span>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp" class="form-label">Nomor WhatsApp *</label>
                                <input type="tel" id="whatsapp" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" placeholder="+62 812 3456 7890" value="{{ old('whatsapp') }}" inputmode="numeric" autocomplete="tel" required>
                                @error('whatsapp')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback client-error" id="err_whatsapp" style="display:none;"></span>
                            </div>
                        </div>

                        <p class="form-helper-text">Pastikan email dan nomor WhatsApp aktif untuk menerima kabar dari studio.</p>
                    </div>

                    <hr class="divider">

                    <div class="form-section">
                        <h4 class="form-section__title">Detail sesi</h4>

                        <div class="form-group">
                            <label for="paket_display" class="form-label">Paket pilihan *</label>
                            <div class="custom-select-mimic">
                                <span id="formSelectedPkgLabel">{{ $selectedPkg['name'] ?? 'Family Session' }} · {{ $selectedPkg['price'] ?? 'Rp2.450.000' }} / sesi</span>
                                <img src="{{ asset('images/chevron-down.svg') }}" alt="" class="select-chevron">
                            </div>
                            <p class="form-helper-text">Sesuai dengan paket yang Anda pilih di atas.</p>
                        </div>

                        <div class="form-group">
                            <label for="lokasi" class="form-label">Lokasi sesi *</label>
                            <input type="text" id="lokasi" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" placeholder="Contoh: Rumah / Cafe / Studio di Jakarta Selatan" value="{{ old('lokasi') }}" required>
                            @error('lokasi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <span class="invalid-feedback client-error" id="err_lokasi" style="display:none;"></span>
                            <p class="form-helper-text" id="lokasiHelper">{{ $selectedPkg['location_note'] ?? 'Satu lokasi pilihan Anda.' }}</p>
                        </div>

                        <div class="form-group">
                            <label for="catatan" class="form-label">Catatan untuk Studio / Fotografer (opsional)</label>
                            <textarea id="catatan" name="catatan" rows="4" class="form-control form-textarea" placeholder="Ceritakan konsep atau preferensi suasana yang Anda inginkan...">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    <hr class="divider">

                    {{-- Persetujuan & Tombol Kirim --}}
                    <div class="form-section">
                        <label class="consent-checkbox-label">
                            <input type="checkbox" id="persetujuanCheckbox" name="persetujuan" value="1" required class="custom-checkbox @error('persetujuan') is-invalid @enderror">
                            <span class="consent-text">Saya menyetujui Kebijakan Privasi dan Syarat Pemesanan, serta bersedia dihubungi untuk membahas rencana sesi ini.</span>
                        </label>
                        @error('persetujuan')
                            <span class="invalid-feedback" style="margin-top:6px;">{{ $message }}</span>
                        @enderror
                        <span class="invalid-feedback client-error" id="err_persetujuan" style="display:none; margin-top:6px;"></span>

                        <button type="submit" id="submitBookingBtn" class="btn btn--fill btn--block btn--lg" disabled>
                            Kirim Permintaan Pemesanan @include('partials.arrow')
                        </button>

                        <p class="form-disclaimer-text">
                            Setelah permintaan dikirim, kode pesanan dibuat dan ditampilkan di checkout serta dikirim ke email atau WhatsApp Anda. Simpan kode untuk mengecek status. Tim naro capture akan meninjau ketersediaan sebelum mengonfirmasi sesi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

{{-- SCRIPT INTERAKTIF PEMILIHAN PAKET, KALENDER, & WAKTU --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.__STUDIO_SCHEDULE__ = @json($scheduleMap ?? []);

    const packageCards = document.querySelectorAll('.package-card-select');
    const selectedDateInput = document.getElementById('selectedDateInput');
    const timeSlotsContainer = document.getElementById('timeSlotsContainer');
    
    // Elemen Ringkasan & Form Mirror
    const sumPkg = document.getElementById('sumPackageName');
    const sumPrice = document.getElementById('sumPrice');
    const sumTime = document.getElementById('sumTime');
    const sumDate = document.getElementById('sumDate');
    const formSelectedPkgLabel = document.getElementById('formSelectedPkgLabel');
    const lokasiHelper = document.getElementById('lokasiHelper');
    const timeDurationNote = document.getElementById('timeDurationNote');
    const timeDateDisplay = document.getElementById('timeDateDisplay');

    let currentPkg = {
        name: "{{ $selectedPkg ? $selectedPkg['name'] : 'Family Session' }}",
        price: "{{ $selectedPkg ? $selectedPkg['price'] : 'Rp2.450.000' }}",
        duration: {{ $selectedPkg ? ($selectedPkg['duration'] ?? 90) : 90 }},
        location: "{{ $selectedPkg ? addslashes($selectedPkg['location_note'] ?? '') : '' }}"
    };
    let currentDateLabel = "{{ $defaultSelectedDate->translatedFormat('l, d F Y') }}";
    let currentTimeSlot = "{{ $initialSelectedSlot ?? '15.30' }}";

    function calculateEndTime(startTimeStr, durationMinutes) {
        if (!startTimeStr) return '';
        if (startTimeStr.includes('–') || startTimeStr.includes('-')) {
            const rangeParts = startTimeStr.split(/[–-]/);
            if (rangeParts.length >= 2) return rangeParts[1].trim();
        }
        const parts = startTimeStr.split(/[.:]/);
        if (parts.length < 2) return startTimeStr;
        const startH = parseInt(parts[0], 10);
        const startM = parseInt(parts[1], 10);
        const total = (startH * 60) + startM + parseInt(durationMinutes || 90, 10);
        const endH = Math.floor(total / 60) % 24;
        const endM = total % 60;
        return `${String(endH).padStart(2, '0')}.${String(endM).padStart(2, '0')}`;
    }

    function getSlotsForDate(dateStr) {
        if (window.__STUDIO_SCHEDULE__ && window.__STUDIO_SCHEDULE__[dateStr] && window.__STUDIO_SCHEDULE__[dateStr].slots) {
            return window.__STUDIO_SCHEDULE__[dateStr].slots;
        }
        return [
            { time: '09.00', end_time: '10.30', time_display: '09.00', status: 'Tersedia', is_available: true, type: 'available' },
            { time: '13.00', end_time: '14.30', time_display: '13.00', status: 'Tersedia', is_available: true, type: 'available' },
            { time: '15.30', end_time: '17.00', time_display: '15.30', status: 'Tersedia', is_available: true, type: 'available' }
        ];
    }

    function updateSummary() {
        if (sumPkg) sumPkg.textContent = currentPkg.name;
        if (sumPrice) sumPrice.textContent = currentPkg.price;
        if (formSelectedPkgLabel) formSelectedPkgLabel.textContent = `${currentPkg.name} · ${currentPkg.price} / sesi`;
        if (lokasiHelper) lokasiHelper.textContent = currentPkg.location;
        if (sumDate) sumDate.textContent = currentDateLabel;
        if (timeDateDisplay) timeDateDisplay.textContent = currentDateLabel;
        
        const endTime = calculateEndTime(currentTimeSlot, currentPkg.duration);
        const timeStr = `${currentTimeSlot}–${endTime} WIB · ${currentPkg.duration} menit`;
        if (sumTime) sumTime.textContent = timeStr;
        if (timeDurationNote) timeDurationNote.textContent = `Durasi ${currentPkg.name}: ${currentPkg.duration} menit · ${currentTimeSlot}–${endTime} WIB`;
    }

    function bindSlotEvents() {
        const slotBtns = timeSlotsContainer ? timeSlotsContainer.querySelectorAll('.time-slot-btn:not(.is-full)') : [];
        slotBtns.forEach(slot => {
            slot.addEventListener('click', () => {
                slotBtns.forEach(s => {
                    s.classList.remove('is-selected');
                    const st = s.querySelector('.slot-status');
                    if (st) st.textContent = 'Tersedia';
                });
                slot.classList.add('is-selected');
                const st = slot.querySelector('.slot-status');
                if (st) st.textContent = 'Dipilih';
                const radio = slot.querySelector('.time-radio');
                if (radio) {
                    radio.checked = true;
                    currentTimeSlot = radio.value;
                }
                updateSummary();
            });
        });
    }

    function renderTimeSlots(dateStr) {
        if (!timeSlotsContainer) return;

        const slots = getSlotsForDate(dateStr);
        let html = '';
        let hasAvailable = false;

        const matchCurrent = slots.find(s => s.time === currentTimeSlot && s.is_available);
        if (!matchCurrent) {
            const firstAvail = slots.find(s => s.is_available);
            if (firstAvail) {
                currentTimeSlot = firstAvail.time;
            }
        }

        slots.forEach(slot => {
            if (slot.is_available) {
                hasAvailable = true;
                const isSel = (slot.time === currentTimeSlot);
                html += `
                    <label class="time-slot-btn ${isSel ? 'is-selected' : ''}">
                        <input type="radio" name="session_time" value="${slot.time}" ${isSel ? 'checked' : ''} class="sr-only time-radio">
                        <span class="slot-time">${slot.time}</span>
                        <span class="slot-status">${isSel ? 'Dipilih' : 'Tersedia'}</span>
                    </label>
                `;
            } else {
                html += `
                    <div class="time-slot-btn is-full" title="${slot.note || slot.status}">
                        <span class="slot-time">${slot.time}</span>
                        <span class="slot-status">${slot.status}</span>
                    </div>
                `;
            }
        });

        if (slots.length === 0 || !hasAvailable) {
            html = `
                <div style="grid-column: 1 / -1; padding: 14px; background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5; border-radius: 6px; font-size: 13px; text-align: center;">
                    Semua slot pada tanggal ini sudah terpesan atau diblokir. Silakan pilih tanggal lain yang bertanda hijau.
                </div>
            `;
        }

        timeSlotsContainer.innerHTML = html;
        bindSlotEvents();
        updateSummary();
    }

    // Pemilihan Paket
    packageCards.forEach(card => {
        card.addEventListener('click', () => {
            packageCards.forEach(c => {
                c.classList.remove('is-selected');
                const tag = c.querySelector('.pkg-card__footer span');
                if (tag) {
                    tag.className = 'pkg-tag-muted';
                    tag.textContent = 'KLIK UNTUK MEMILIH';
                }
            });
            card.classList.add('is-selected');
            const radio = card.querySelector('.package-radio');
            if (radio) radio.checked = true;

            const activeTag = card.querySelector('.pkg-card__footer span');
            if (activeTag) {
                activeTag.className = 'pkg-tag';
                activeTag.textContent = 'PAKET TERPILIH';
            }

            currentPkg.name = card.dataset.name;
            currentPkg.price = card.dataset.price;
            currentPkg.duration = parseInt(card.dataset.duration || 90, 10);
            currentPkg.location = card.dataset.location;

            updateSummary();
        });
    });

    // Navigasi & Render Kalender Dinamis (Bulan Berjalan, Bulan Depan, dst.)
    const currentMonthYearEl = document.getElementById('currentMonthYear');
    const calDatesContainer = document.querySelector('.cal-dates');
    const btnPrevMonth = document.getElementById('btnPrevMonth');
    const btnNextMonth = document.getElementById('btnNextMonth');

    const indonesianMonths = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const indonesianDays = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let viewYear = {{ $bookingMonth->year }};
    let viewMonth = {{ $bookingMonth->month - 1 }};

    const minYear = today.getFullYear();
    const minMonth = today.getMonth();

    function renderCalendar(year, month) {
        if (!calDatesContainer) return;

        if (currentMonthYearEl) {
            currentMonthYearEl.textContent = `${indonesianMonths[month]} ${year}`;
        }

        if (btnPrevMonth) {
            btnPrevMonth.disabled = (year < minYear || (year === minYear && month <= minMonth));
        }

        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDayOfWeek = new Date(year, month, 1).getDay();
        const dayOfWeekOffset = (firstDayOfWeek === 0) ? 6 : (firstDayOfWeek - 1);
        const prevMonthDays = new Date(year, month, 0).getDate();

        let html = '';

        // 1. Padding hari sebelum tanggal 1
        for (let p = 0; p < dayOfWeekOffset; p++) {
            const padDay = prevMonthDays - dayOfWeekOffset + p + 1;
            html += `<div class="cal-date is-disabled">${padDay}</div>`;
        }

        // 2. Tanggal-tanggal dalam bulan ini
        const selectedDateVal = selectedDateInput ? selectedDateInput.value : '';

        for (let day = 1; day <= daysInMonth; day++) {
            const dateObj = new Date(year, month, day);
            dateObj.setHours(0, 0, 0, 0);

            const isPast = dateObj < today;
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayName = indonesianDays[dateObj.getDay()];
            const dateLabel = `${dayName}, ${day} ${indonesianMonths[month]} ${year}`;
            const isSelected = (dateStr === selectedDateVal);

            const daySlots = getSlotsForDate(dateStr);
            const isBlockedDate = window.__STUDIO_SCHEDULE__ && window.__STUDIO_SCHEDULE__[dateStr] && window.__STUDIO_SCHEDULE__[dateStr].is_blocked;
            const hasAvailableSlots = !isBlockedDate && daySlots.some(s => s.is_available);

            if (isPast) {
                html += `<div class="cal-date is-disabled" title="Sudah lewat">${day}</div>`;
            } else if (!hasAvailableSlots) {
                html += `<div class="cal-date is-disabled" title="Penuh / Tidak tersedia">${day}</div>`;
            } else {
                html += `<div class="cal-date is-available ${isSelected ? 'is-selected' : ''}" data-date="${dateStr}" data-label="${dateLabel}">${day}</div>`;
            }
        }

        // 3. Padding hari sesudah tanggal terakhir (melengkapi baris kelipatan 7)
        const totalRendered = dayOfWeekOffset + daysInMonth;
        const remainingPadding = (7 - (totalRendered % 7)) % 7;
        for (let p = 1; p <= remainingPadding; p++) {
            html += `<div class="cal-date is-disabled">${p}</div>`;
        }

        calDatesContainer.innerHTML = html;

        // Pasang event listener untuk tanggal yang tersedia
        calDatesContainer.querySelectorAll('.cal-date.is-available').forEach(dateEl => {
            dateEl.addEventListener('click', () => {
                calDatesContainer.querySelectorAll('.cal-date').forEach(d => d.classList.remove('is-selected'));
                dateEl.classList.add('is-selected');

                if (selectedDateInput) selectedDateInput.value = dateEl.dataset.date;
                currentDateLabel = dateEl.dataset.label;
                renderTimeSlots(dateEl.dataset.date);
            });
        });
    }

    if (btnPrevMonth) {
        btnPrevMonth.addEventListener('click', () => {
            if (viewYear > minYear || (viewYear === minYear && viewMonth > minMonth)) {
                viewMonth--;
                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear--;
                }
                renderCalendar(viewYear, viewMonth);
            }
        });
    }

    if (btnNextMonth) {
        btnNextMonth.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            renderCalendar(viewYear, viewMonth);
        });
    }

    // Inisialisasi awal
    bindSlotEvents();
    renderCalendar(viewYear, viewMonth);
    updateSummary();

    // Format Otomatis Nomor WhatsApp (Number only & 08 -> +62 8)
    const waInput = document.getElementById('whatsapp');
    if (waInput) {
        waInput.addEventListener('input', (e) => {
            let val = waInput.value;

            // Pertahankan hanya digit angka dan tanda plus awal jika ada
            let rawDigits = val.replace(/\D/g, '');

            // Jika user mengetik mulai dari 08... -> ganti 0 di depan jadi 62
            if (rawDigits.startsWith('0')) {
                rawDigits = '62' + rawDigits.substring(1);
            } else if (rawDigits.startsWith('8')) {
                // Jika langsung ketik 8... -> tambahkan 62 di depan
                rawDigits = '62' + rawDigits;
            }

            // Jika kosong, kosongkan input
            if (!rawDigits) {
                waInput.value = '';
                return;
            }

            // Format rapi: +62 8xx xxxx xxxx
            let formatted = '+';
            if (rawDigits.startsWith('62')) {
                formatted += '62';
                const rest = rawDigits.substring(2);
                if (rest.length > 0) {
                    formatted += ' ' + rest.substring(0, 3);
                }
                if (rest.length > 3) {
                    formatted += ' ' + rest.substring(3, 7);
                }
                if (rest.length > 7) {
                    formatted += ' ' + rest.substring(7, 12);
                }
            } else {
                formatted += rawDigits;
            }

            waInput.value = formatted;
        });

        // Cegah karakter non-angka saat mengetik
        waInput.addEventListener('keydown', (e) => {
            // Izinkan navigation keys, backspace, delete, tab, enter, ctrl+a/c/v
            const allowed = [
                'Backspace', 'Delete', 'Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight', 'Home', 'End'
            ];
            if (allowed.includes(e.key) || (e.ctrlKey || e.metaKey)) {
                return;
            }
            // Cegah jika bukan angka 0-9
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });
    }

    // ==========================================
    // VALIDASI FORM & KONTROL TOMBOL SUBMIT
    // ==========================================
    const namaInput = document.getElementById('nama_lengkap');
    const emailInput = document.getElementById('email');
    const lokasiInput = document.getElementById('lokasi');
    const persetujuanCheckbox = document.getElementById('persetujuanCheckbox');
    const submitBookingBtn = document.getElementById('submitBookingBtn');
    const bookingForm = document.getElementById('bookingForm');

    function setFieldError(fieldId, errorMsg) {
        const input = document.getElementById(fieldId);
        const errEl = document.getElementById(`err_${fieldId}`);
        if (input) input.classList.add('is-invalid');
        if (errEl) {
            errEl.textContent = errorMsg;
            errEl.style.display = 'block';
        }
    }

    function clearFieldError(fieldId) {
        const input = document.getElementById(fieldId);
        const errEl = document.getElementById(`err_${fieldId}`);
        if (input) input.classList.remove('is-invalid');
        if (errEl) {
            errEl.textContent = '';
            errEl.style.display = 'none';
        }
    }

    function isEmailValid(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validateForm(showErrors = false) {
        let isValid = true;
        let firstInvalid = null;

        // 1. Nama lengkap
        const namaVal = namaInput ? namaInput.value.trim() : '';
        if (namaVal.length < 3) {
            isValid = false;
            if (showErrors) {
                setFieldError('nama_lengkap', 'Nama lengkap pemesan wajib diisi (minimal 3 karakter).');
                if (!firstInvalid) firstInvalid = namaInput;
            }
        } else if (showErrors) {
            clearFieldError('nama_lengkap');
        }

        // 2. Email
        const emailVal = emailInput ? emailInput.value.trim() : '';
        if (!emailVal || !isEmailValid(emailVal)) {
            isValid = false;
            if (showErrors) {
                setFieldError('email', 'Alamat email aktif wajib diisi dengan format valid (contoh: nama@email.com).');
                if (!firstInvalid) firstInvalid = emailInput;
            }
        } else if (showErrors) {
            clearFieldError('email');
        }

        // 3. WhatsApp
        const waClean = waInput ? waInput.value.replace(/[^0-9]/g, '') : '';
        if (waClean.length < 8) {
            isValid = false;
            if (showErrors) {
                setFieldError('whatsapp', 'Nomor WhatsApp aktif wajib diisi (minimal 8 digit angka).');
                if (!firstInvalid) firstInvalid = waInput;
            }
        } else if (showErrors) {
            clearFieldError('whatsapp');
        }

        // 4. Lokasi
        const lokasiVal = lokasiInput ? lokasiInput.value.trim() : '';
        if (lokasiVal.length < 3) {
            isValid = false;
            if (showErrors) {
                setFieldError('lokasi', 'Lokasi sesi foto wajib diisi (minimal 3 karakter).');
                if (!firstInvalid) firstInvalid = lokasiInput;
            }
        } else if (showErrors) {
            clearFieldError('lokasi');
        }

        // 5. Slot waktu sesi
        const checkedTime = document.querySelector('input[name="session_time"]:checked');
        if (!checkedTime || !checkedTime.value) {
            isValid = false;
            if (showErrors) {
                setFieldError('session_time', 'Silakan pilih salah satu slot waktu yang tersedia pada tanggal ini.');
                if (!firstInvalid) firstInvalid = timeSlotsContainer;
            }
        } else if (showErrors) {
            clearFieldError('session_time');
        }

        // 6. Checkbox Persetujuan
        if (!persetujuanCheckbox || !persetujuanCheckbox.checked) {
            isValid = false;
            if (showErrors) {
                setFieldError('persetujuan', 'Anda harus mencentang persetujuan Kebijakan Privasi dan Syarat Pemesanan.');
                if (!firstInvalid) firstInvalid = persetujuanCheckbox;
            }
        } else if (showErrors) {
            clearFieldError('persetujuan');
        }

        return { isValid, firstInvalid };
    }

    // Real-time listener untuk update tombol submit & clear error saat mengetik
    const formFields = [namaInput, emailInput, waInput, lokasiInput];
    formFields.forEach(input => {
        if (!input) return;
        input.addEventListener('input', () => {
            clearFieldError(input.id);
            updateSubmitButtonState();
        });
        input.addEventListener('blur', () => {
            if (input.value.trim() !== '') {
                validateForm(false);
            }
        });
    });

    if (persetujuanCheckbox) {
        persetujuanCheckbox.addEventListener('change', () => {
            clearFieldError('persetujuan');
            updateSubmitButtonState();
        });
    }

    function updateSubmitButtonState() {
        if (!submitBookingBtn || submitBookingBtn.hasAttribute('data-submitting')) return;
        const { isValid } = validateForm(false);
        submitBookingBtn.disabled = !isValid;
    }

    updateSubmitButtonState();

    // Pencegahan Submit jika ada data wajib belum diisi
    if (bookingForm && submitBookingBtn) {
        bookingForm.addEventListener('submit', function(e) {
            const { isValid, firstInvalid } = validateForm(true);

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (typeof firstInvalid.focus === 'function') {
                        firstInvalid.focus();
                    }
                }
                return false;
            }

            if (submitBookingBtn.hasAttribute('data-submitting')) {
                e.preventDefault();
                return false;
            }

            submitBookingBtn.setAttribute('data-submitting', 'true');
            submitBookingBtn.style.opacity = '0.75';
            submitBookingBtn.style.cursor = 'not-allowed';
            submitBookingBtn.innerHTML = 'Memproses Permintaan Pemesanan...';
        });
    }
});
</script>
@endsection

