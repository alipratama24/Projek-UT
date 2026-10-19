@extends('layouts.app')

@section('title', 'Pemesanan — Clara Hayes Photography')

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
                                @if ($pkg['id'] === 'cerita-keluarga')
                                    <span class="pkg-tag">PAKET TERPILIH · PILIHAN TERPOPULER</span>
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

                    {{-- Header Bulan --}}
                    <div class="calendar-month-header">
                        <h3 class="month-title" id="currentMonthYear">Oktober 2026</h3>
                        <div class="month-nav">
                            <button type="button" class="btn-icon" aria-label="Bulan sebelumnya"><img src="{{ asset('images/chevron-left.svg') }}" alt=""></button>
                            <button type="button" class="btn-icon" aria-label="Bulan berikutnya"><img src="{{ asset('images/chevron-right.svg') }}" alt=""></button>
                        </div>
                    </div>

                    {{-- Tabel Kalender --}}
                    <div class="calendar-grid">
                        <div class="cal-days-header">
                            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                        </div>
                        <div class="cal-dates">
                            {{-- Baris 1: Sisa September --}}
                            <div class="cal-date is-disabled">28</div>
                            <div class="cal-date is-disabled">29</div>
                            <div class="cal-date is-disabled">30</div>
                            <div class="cal-date is-disabled">1</div>
                            <div class="cal-date is-disabled">2</div>
                            <div class="cal-date is-disabled">3</div>
                            <div class="cal-date is-disabled">4</div>

                            {{-- Baris 2 --}}
                            <div class="cal-date is-disabled">5</div>
                            <div class="cal-date is-available" data-date="2026-10-06" data-label="Selasa, 6 Oktober 2026">6</div>
                            <div class="cal-date is-available" data-date="2026-10-07" data-label="Rabu, 7 Oktober 2026">7</div>
                            <div class="cal-date is-disabled">8</div>
                            <div class="cal-date is-available" data-date="2026-10-09" data-label="Jumat, 9 Oktober 2026">9</div>
                            <div class="cal-date is-available" data-date="2026-10-10" data-label="Sabtu, 10 Oktober 2026">10</div>
                            <div class="cal-date is-disabled">11</div>

                            {{-- Baris 3 --}}
                            <div class="cal-date is-available" data-date="2026-10-12" data-label="Senin, 12 Oktober 2026">12</div>
                            <div class="cal-date is-available" data-date="2026-10-13" data-label="Selasa, 13 Oktober 2026">13</div>
                            <div class="cal-date is-disabled">14</div>
                            <div class="cal-date is-available" data-date="2026-10-15" data-label="Kamis, 15 Oktober 2026">15</div>
                            <div class="cal-date is-available" data-date="2026-10-16" data-label="Jumat, 16 Oktober 2026">16</div>
                            <div class="cal-date is-available is-selected" data-date="2026-10-17" data-label="Sabtu, 17 Oktober 2026">17</div>
                            <div class="cal-date is-disabled">18</div>

                            {{-- Baris 4 --}}
                            <div class="cal-date is-available" data-date="2026-10-19" data-label="Senin, 19 Oktober 2026">19</div>
                            <div class="cal-date is-available" data-date="2026-10-20" data-label="Selasa, 20 Oktober 2026">20</div>
                            <div class="cal-date is-disabled">21</div>
                            <div class="cal-date is-available" data-date="2026-10-22" data-label="Kamis, 22 Oktober 2026">22</div>
                            <div class="cal-date is-available" data-date="2026-10-23" data-label="Jumat, 23 Oktober 2026">23</div>
                            <div class="cal-date is-available" data-date="2026-10-24" data-label="Sabtu, 24 Oktober 2026">24</div>
                            <div class="cal-date is-disabled">25</div>

                            {{-- Baris 5 --}}
                            <div class="cal-date is-available" data-date="2026-10-26" data-label="Senin, 26 Oktober 2026">26</div>
                            <div class="cal-date is-available" data-date="2026-10-27" data-label="Selasa, 27 Oktober 2026">27</div>
                            <div class="cal-date is-disabled">28</div>
                            <div class="cal-date is-available" data-date="2026-10-29" data-label="Kamis, 29 Oktober 2026">29</div>
                            <div class="cal-date is-available" data-date="2026-10-30" data-label="Jumat, 30 Oktober 2026">30</div>
                            <div class="cal-date is-available" data-date="2026-10-31" data-label="Sabtu, 31 Oktober 2026">31</div>
                            <div class="cal-date is-disabled">1</div>
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

                    <input type="hidden" name="session_date" id="selectedDateInput" value="2026-10-17">

                    <hr class="divider">

                    {{-- Pilihan Jam --}}
                    <div class="time-selection-wrap">
                        <div class="time-header">
                            <span class="time-date-label" id="timeDateDisplay">Sabtu, 17 Oktober 2026</span>
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
                        <p class="time-duration-note" id="timeDurationNote">Durasi Cerita Keluarga: 90 menit · 15.30–17.00 WIB</p>
                    </div>
                </div>

                {{-- RINGKASAN PEMESANAN --}}
                <div class="booking-box summary-box">
                    <h3 class="summary-title">Sesi yang Anda rencanakan</h3>
                    
                    <div class="summary-item">
                        <img src="{{ asset('images/icon-camera.svg') }}" alt="" class="summary-icon">
                        <div class="summary-item__text">
                            <span class="summary-label">Paket pilihan</span>
                            <strong class="summary-val" id="sumPackageName">Cerita Keluarga</strong>
                        </div>
                    </div>

                    <div class="summary-item">
                        <img src="{{ asset('images/icon-calendar.svg') }}" alt="" class="summary-icon">
                        <div class="summary-item__text">
                            <span class="summary-label">Tanggal sesi</span>
                            <strong class="summary-val" id="sumDate">Sabtu, 17 Oktober 2026</strong>
                        </div>
                    </div>

                    <div class="summary-item">
                        <img src="{{ asset('images/icon-clock.svg') }}" alt="" class="summary-icon">
                        <div class="summary-item__text">
                            <span class="summary-label">Waktu sesi</span>
                            <strong class="summary-val" id="sumTime">15.30–17.00 WIB · 90 menit</strong>
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="summary-price-row">
                        <span class="summary-price-label">Harga paket / sesi</span>
                        <span class="summary-price-val" id="sumPrice">Rp9.500.000</span>
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
                                <span id="formSelectedPkgLabel">Cerita Keluarga · Rp9.500.000 / sesi</span>
                                <img src="{{ asset('images/chevron-down.svg') }}" alt="" class="select-chevron">
                            </div>
                            <p class="form-helper-text">Sesuai dengan paket yang Anda pilih di atas.</p>
                        </div>

                        <div class="form-group">
                            <label for="lokasi" class="form-label">Lokasi sesi *</label>
                            <input type="text" id="lokasi" name="lokasi" class="form-control" placeholder="Contoh: Rumah / Cafe / Studio di Jakarta Selatan" value="{{ old('lokasi') }}" required>
                            <p class="form-helper-text" id="lokasiHelper">Cerita Keluarga mencakup dua lokasi. Alamat dan lokasi akhir dibahas saat perencanaan.</p>
                        </div>

                        <div class="form-group">
                            <label for="catatan" class="form-label">Catatan untuk Clara (opsional)</label>
                            <textarea id="catatan" name="catatan" rows="4" class="form-control form-textarea" placeholder="Ceritakan konsep atau preferensi suasana yang Anda inginkan...">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    <hr class="divider">

                    {{-- Persetujuan & Tombol Kirim --}}
                    <div class="form-section">
                        <label class="consent-checkbox-label">
                            <input type="checkbox" name="persetujuan" value="1" required class="custom-checkbox">
                            <span class="consent-text">Saya menyetujui Kebijakan Privasi dan Syarat Pemesanan, serta bersedia dihubungi untuk membahas rencana sesi ini.</span>
                        </label>

                        <button type="submit" class="btn btn--fill btn--block btn--lg">
                            Kirim Permintaan Pemesanan @include('partials.arrow')
                        </button>

                        <p class="form-disclaimer-text">
                            Setelah permintaan dikirim, kode pesanan dibuat dan ditampilkan di checkout serta dikirim ke email atau WhatsApp Anda. Simpan kode untuk mengecek status. Clara akan meninjau ketersediaan sebelum mengonfirmasi sesi.
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
        name: 'Cerita Keluarga',
        price: 'Rp9.500.000',
        duration: 90,
        location: 'Cerita Keluarga mencakup dua lokasi. Alamat dan lokasi akhir dibahas saat perencanaan.'
    };
    let currentDateLabel = 'Sabtu, 17 Oktober 2026';
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

    // Pemilihan Tanggal Kalender
    calDates.forEach(dateEl => {
        dateEl.addEventListener('click', () => {
            calDates.forEach(d => d.classList.remove('is-selected'));
            dateEl.classList.add('is-selected');
            
            selectedDateInput.value = dateEl.dataset.date;
            currentDateLabel = dateEl.dataset.label;
            updateSummary();
        });
    });

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
});
</script>
@endsection

