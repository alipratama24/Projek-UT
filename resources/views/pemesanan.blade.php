@extends('layouts.app')

@section('title', 'Pemesanan — naro capture')

@php
    $selectedPkg = collect($packages)->firstWhere('id', $selectedPackageId) ?? ($packages[0] ?? null);
@endphp

@section('content')
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

    <form action="{{ route('pemesanan.store') }}" method="POST" id="bookingForm">
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
                        <div class="time-slots">
                            <label class="time-slot-btn">
                                <input type="radio" name="session_time" value="09.00" class="sr-only time-radio">
                                <span class="slot-time">09.00</span>
                                <span class="slot-status">Tersedia</span>
                            </label>
                            <div class="time-slot-btn is-full">
                                <span class="slot-time">13.00</span>
                                <span class="slot-status">Penuh</span>
                            </div>
                            <label class="time-slot-btn is-selected">
                                <input type="radio" name="session_time" value="15.30" checked class="sr-only time-radio">
                                <span class="slot-time">15.30</span>
                                <span class="slot-status">Dipilih</span>
                            </label>
                        </div>
                        <p class="time-duration-note" id="timeDurationNote">Durasi {{ $selectedPkg['name'] ?? 'Family Session' }}: {{ $selectedPkg['duration'] ?? 90 }} menit · 15.30–{{ ($selectedPkg['duration'] ?? 90) === 90 ? '17.00' : '16.30' }} WIB</p>
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
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" placeholder="Contoh: Ayu Pramesti" value="{{ old('nama_lengkap') }}" required>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="email" class="form-label">Alamat email *</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp" class="form-label">Nomor WhatsApp *</label>
                                <input type="tel" id="whatsapp" name="whatsapp" class="form-control" placeholder="+62 812 3456 7890" value="{{ old('whatsapp') }}" inputmode="numeric" autocomplete="tel" required>
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
                            <input type="text" id="lokasi" name="lokasi" class="form-control" placeholder="Contoh: Rumah / Cafe / Studio di Jakarta Selatan" value="{{ old('lokasi') }}" required>
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
                            <input type="checkbox" id="persetujuanCheckbox" name="persetujuan" value="1" required class="custom-checkbox">
                            <span class="consent-text">Saya menyetujui Kebijakan Privasi dan Syarat Pemesanan, serta bersedia dihubungi untuk membahas rencana sesi ini.</span>
                        </label>

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
    const packageCards = document.querySelectorAll('.package-card-select');
    const calDates = document.querySelectorAll('.cal-date.is-available');
    const timeSlots = document.querySelectorAll('.time-slot-btn:not(.is-full)');
    const selectedDateInput = document.getElementById('selectedDateInput');
    
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
    let currentTimeSlot = '15.30';

    function updateSummary() {
        if (sumPkg) sumPkg.textContent = currentPkg.name;
        if (sumPrice) sumPrice.textContent = currentPkg.price;
        if (formSelectedPkgLabel) formSelectedPkgLabel.textContent = `${currentPkg.name} · ${currentPkg.price} / sesi`;
        if (lokasiHelper) lokasiHelper.textContent = currentPkg.location;
        if (sumDate) sumDate.textContent = currentDateLabel;
        if (timeDateDisplay) timeDateDisplay.textContent = currentDateLabel;
        
        let endTime = '';
        if (currentTimeSlot === '09.00') {
            endTime = currentPkg.duration === 90 ? '10.30' : (currentPkg.duration === 45 ? '09.45' : '13.00');
        } else if (currentTimeSlot === '15.30') {
            endTime = currentPkg.duration === 90 ? '17.00' : (currentPkg.duration === 45 ? '16.15' : '19.30');
        } else {
            endTime = '14.30';
        }

        const timeStr = `${currentTimeSlot}–${endTime} WIB · ${currentPkg.duration} menit`;
        if (sumTime) sumTime.textContent = timeStr;
        if (timeDurationNote) timeDurationNote.textContent = `Durasi ${currentPkg.name}: ${currentPkg.duration} menit · ${currentTimeSlot}–${endTime} WIB`;
    }

    updateSummary();

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
            currentPkg.duration = parseInt(card.dataset.duration || 90);
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

            if (isPast) {
                html += `<div class="cal-date is-disabled" title="Sudah lewat">${day}</div>`;
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
                updateSummary();
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

    // Inisialisasi kalender interaktif
    renderCalendar(viewYear, viewMonth);

    // Pemilihan Slot Waktu
    timeSlots.forEach(slot => {
        slot.addEventListener('click', () => {
            timeSlots.forEach(s => {
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

    // Kontrol tombol submit berdasarkan checklist persetujuan
    const persetujuanCheckbox = document.getElementById('persetujuanCheckbox');
    const submitBookingBtn = document.getElementById('submitBookingBtn');

    if (persetujuanCheckbox && submitBookingBtn) {
        const updateSubmitState = () => {
            if (!submitBookingBtn.hasAttribute('data-submitting')) {
                submitBookingBtn.disabled = !persetujuanCheckbox.checked;
            }
        };

        persetujuanCheckbox.addEventListener('change', updateSubmitState);
        updateSubmitState(); // Jalankan saat pertama kali halaman dimuat
    }

    // Cegah double submission saat klik cepat berkali-kali
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm && submitBookingBtn) {
        bookingForm.addEventListener('submit', function(e) {
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

