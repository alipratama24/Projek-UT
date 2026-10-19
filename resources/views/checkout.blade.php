@extends('layouts.app')

@section('title', 'Ringkasan & Invoice — Clara Hayes Photography')

@section('content')
<main class="checkout-page">
    {{-- Breadcrumb & Header --}}
    <section class="checkout-hero">
        <div class="booking-breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="sep">/</span>
            <a href="{{ route('pemesanan') }}">Pemesanan</a>
            <span class="sep">/</span>
            <span>Checkout</span>
        </div>

        <div class="checkout-header">
            <div class="checkout-header__left">
                <span class="eyebrow">SEBELUM KITA MENGABADIKAN CERITA ANDA</span>
                <h1 class="checkout-header__title">Ringkasan &amp; invoice.</h1>
            </div>
            <p class="body-text checkout-header__desc">
                Periksa kembali detail sesi dan invoice Anda, lalu pilih metode pembayaran. Sesi Anda belum dikonfirmasi.
            </p>
        </div>

        {{-- Alur Pemesanan (Step Indicator) --}}
        <div class="checkout-stepper">
            <div class="step-indicator is-complete">
                <span class="step-icon"><img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14"></span>
                <span class="step-text">Paket &amp; jadwal</span>
            </div>
            <div class="step-indicator is-complete">
                <span class="step-icon"><img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14"></span>
                <span class="step-text">Data pelanggan</span>
            </div>
            <div class="step-indicator is-active">
                <span class="step-icon-num">03</span>
                <span class="step-text-active">Ringkasan &amp; pembayaran</span>
            </div>
            <div class="step-next-note">
                Berikutnya: konfirmasi dari studio
            </div>
        </div>
        <hr class="divider">
    </section>

    {{-- Content Grid: Ringkasan Sesi (Kiri) & Invoice/Pembayaran (Kanan) --}}
    <div class="checkout-split-grid">
        {{-- KOLOM KIRI: RINGKASAN SESI --}}
        <div class="checkout-box summary-card">
            <div class="checkout-card-header">
                <h2 class="card-title">Sesi yang Anda rencanakan</h2>
                <span class="status-badge">Menunggu konfirmasi</span>
            </div>

            {{-- Kartu Paket Terpilih --}}
            <div class="chosen-package-card">
                <div class="chosen-package__img">
                    <img src="{{ asset('images/' . $data['package_image']) }}" alt="{{ $data['package_name'] }}">
                </div>
                <div class="chosen-package__info">
                    <span class="eyebrow-small">PAKET TERPILIH</span>
                    <h3 class="package-name-lg">{{ $data['package_name'] }}</h3>
                    <p class="body-text">{{ $data['package_desc'] }}</p>
                    <div class="package-price-highlight">{{ $data['package_price'] }} / sesi</div>
                </div>
            </div>

            {{-- Termasuk dalam sesi --}}
            <div class="session-inclusions">
                <h4 class="inclusions-title">Termasuk dalam sesi Anda</h4>
                <ul class="inclusions-list">
                    @foreach ($data['package_items'] as $item)
                        <li>
                            <img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14">
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <hr class="divider">

            {{-- Jadwal & Lokasi --}}
            <div class="detail-rows">
                <div class="detail-row-flex">
                    <div class="detail-col">
                        <img src="{{ asset('images/icon-calendar.svg') }}" alt="" class="detail-icon">
                        <div>
                            <span class="detail-label">Tanggal sesi</span>
                            <strong class="detail-val">{{ $data['session_date'] }}</strong>
                        </div>
                    </div>
                    <div class="detail-col">
                        <img src="{{ asset('images/icon-clock.svg') }}" alt="" class="detail-icon">
                        <div>
                            <span class="detail-label">Waktu sesi</span>
                            <strong class="detail-val">{{ $data['session_time'] }}</strong>
                        </div>
                    </div>
                </div>

                <div class="detail-row-single">
                    <img src="{{ asset('images/icon-map-pin.svg') }}" alt="" class="detail-icon">
                    <div>
                        <span class="detail-label">Lokasi sesi</span>
                        <strong class="detail-val">{{ $data['lokasi'] }}</strong>
                        <p class="detail-hint">{{ $data['lokasi_hint'] }}</p>
                    </div>
                </div>
            </div>

            <hr class="divider">

            {{-- Data Pelanggan --}}
            <div class="customer-info-section">
                <div class="section-subheader">
                    <h4 class="subheader-title">Data pelanggan</h4>
                    <span class="small-hint">Sesuai data pada pemesanan</span>
                </div>

                <div class="info-group">
                    <span class="detail-label">Nama lengkap</span>
                    <strong class="detail-val">{{ $data['nama_lengkap'] }}</strong>
                </div>

                <div class="info-grid-2">
                    <div class="info-group">
                        <span class="detail-label">Alamat email</span>
                        <strong class="detail-val">{{ $data['email'] }}</strong>
                    </div>
                    <div class="info-group">
                        <span class="detail-label">Nomor WhatsApp</span>
                        <strong class="detail-val">{{ $data['whatsapp'] }}</strong>
                    </div>
                </div>

                @if(!empty($data['catatan']))
                    <div class="info-group">
                        <span class="detail-label">Catatan untuk Clara</span>
                        <p class="body-text">{{ $data['catatan'] }}</p>
                    </div>
                @endif
            </div>

            {{-- Info Notice Box --}}
            <div class="summary-notice">
                <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon">
                <p>Sesi ini masih berupa permintaan jadwal. Clara akan meninjau ketersediaan dan menghubungi Anda melalui email atau WhatsApp sebelum mengonfirmasi sesi.</p>
            </div>

            <div class="card-footer-link">
                <a href="{{ route('pemesanan') }}" class="link-blue-arrow">
                    ← Kembali &amp; ubah pemesanan
                </a>
            </div>
        </div>

        {{-- KOLOM KANAN: INVOICE & PEMBAYARAN --}}
        <div class="checkout-box invoice-card">
            <div class="checkout-card-header">
                <h2 class="card-title">Invoice</h2>
                <span class="status-badge">Draf · Belum dibayar</span>
            </div>

            <div class="invoice-meta">
                <span class="invoice-number">Nomor contoh: {{ $data['invoice_number'] }}</span>
                <strong class="studio-name">Clara Hayes Photography</strong>
                <span class="studio-email">hello@clarahayes.studio</span>
            </div>

            <div class="invoice-order-ref">
                <span class="small-caps muted">KODE PESANAN INVOICE</span>
                <span class="invoice-ref-code">{{ $data['order_code'] }}</span>
                <p class="invoice-ref-desc">Gunakan kode ini untuk cek status pesanan setelah pemesanan dikirim.</p>
            </div>

            <hr class="divider">

            <div class="invoice-bill-to">
                <span class="small-caps muted">DITUJUKAN KEPADA</span>
                <strong class="bill-name">{{ $data['nama_lengkap'] }}</strong>
                <span class="bill-email">{{ $data['email'] }}</span>
            </div>

            {{-- Rincian Tagihan --}}
            <div class="invoice-breakdown">
                <div class="table-header">
                    <span>LAYANAN</span>
                    <span>JUMLAH</span>
                </div>
                <hr class="divider">

                <div class="table-row">
                    <div class="table-col-desc">
                        <strong class="item-title">{{ $data['package_name'] }}</strong>
                        <span class="item-sub">1 sesi × {{ $data['package_price'] }}</span>
                        <span class="item-sub-muted">{{ $data['session_date'] }} · {{ $data['session_time'] }}</span>
                    </div>
                    <span class="item-price">{{ $data['package_price'] }}</span>
                </div>
                <hr class="divider">

                <div class="table-row-subtotal">
                    <span>Subtotal</span>
                    <span>{{ $data['package_price'] }}</span>
                </div>
            </div>

            {{-- Total Pembayaran --}}
            <div class="total-pay-box">
                <span class="total-label">Total pembayaran</span>
                <div class="total-amount">{{ $data['package_price'] }}</div>
                <span class="total-note">Dalam rupiah (IDR) · sesuai harga paket</span>
            </div>

            <button type="button" class="btn btn--outline btn--block" onclick="alert('Draf invoice telah diunduh!')">
                Unduh draf invoice @include('partials.arrow')
            </button>

            <hr class="divider">

            <form action="{{ route('konfirmasi') }}" method="GET" id="paymentMethodForm">
                {{-- Pilihan Metode Pembayaran --}}
                <div class="payment-method-section">
                    <h4 class="payment-title">Pilih metode pembayaran</h4>

                    <label class="payment-method-card is-selected" data-method="transfer">
                        <input type="radio" name="metode" value="transfer" checked class="sr-only payment-radio">
                        <div class="method-radio-circle"><div class="method-radio-inner"></div></div>
                        <div class="method-info">
                            <strong class="method-name">Transfer bank</strong>
                            <span class="method-desc">Nomor rekening BCA & Mandiri diberikan pada langkah berikutnya</span>
                        </div>
                        <img src="{{ asset('images/icon-landmark.svg') }}" alt="" class="method-icon">
                    </label>

                    <label class="payment-method-card" data-method="qris">
                        <input type="radio" name="metode" value="qris" class="sr-only payment-radio">
                        <div class="method-radio-circle"><div class="method-radio-inner"></div></div>
                        <div class="method-info">
                            <strong class="method-name">QRIS</strong>
                            <span class="method-desc">Kode QRIS resmi studio ditampilkan pada langkah berikutnya</span>
                        </div>
                        <img src="{{ asset('images/icon-qr-code.svg') }}" alt="" class="method-icon">
                    </label>
                </div>

                <button type="submit" class="btn btn--fill btn--block btn--lg" style="margin-top: 20px;">
                    Lanjutkan pembayaran @include('partials.arrow')
                </button>
            </form>

            <p class="payment-disclaimer">
                Belum ada pembayaran yang diterima. Pembayaran tidak otomatis mengonfirmasi jadwal; konfirmasi sesi tetap diberikan oleh studio.
            </p>
        </div>
    </div>
</main>

<script>
function copyOrderCode() {
    const code = document.getElementById('orderCodeText').innerText;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.getElementById('btnCopyCode');
        const originalText = btn.innerText;
        btn.innerText = 'Berhasil Disalin!';
        btn.style.color = '#2E7D32';
        setTimeout(() => {
            btn.innerText = originalText;
            btn.style.color = '';
        }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const paymentCards = document.querySelectorAll('.payment-method-card');
    paymentCards.forEach(card => {
        card.addEventListener('click', () => {
            paymentCards.forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            const radio = card.querySelector('.payment-radio');
            if (radio) radio.checked = true;
        });
    });
});
</script>
@endsection

