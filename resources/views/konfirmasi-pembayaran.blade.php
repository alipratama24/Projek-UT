@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran — Clara Hayes Photography')

@section('content')
<main class="confirmation-page">
    {{-- Breadcrumb & Header --}}
    <section class="checkout-hero">
        <div class="booking-breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="sep">/</span>
            <a href="{{ route('pemesanan') }}">Pemesanan</a>
            <span class="sep">/</span>
            <a href="{{ route('checkout') }}">Checkout</a>
            <span class="sep">/</span>
            <span>Konfirmasi pembayaran</span>
        </div>

        <div class="checkout-header">
            <div class="checkout-header__left">
                <span class="eyebrow">SATU LANGKAH LAGI UNTUK CERITA ANDA</span>
                <h1 class="checkout-header__title">Konfirmasi pembayaran.</h1>
            </div>
            <p class="body-text checkout-header__desc">
                @if(isset($isSubmitted) && $isSubmitted)
                    Bukti Anda sudah diterima oleh studio. Pantau hasil pemeriksaan melalui status pesanan.
                @else
                    Sudah melakukan transfer? Unggah struk dan lengkapi data pembayaran agar studio dapat meninjaunya.
                @endif
            </p>
        </div>

        {{-- Stepper Alur Pemesanan --}}
        <div class="checkout-stepper">
            <div class="step-indicator is-complete">
                <span class="step-icon"><img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14"></span>
                <span class="step-text">Paket &amp; jadwal</span>
            </div>
            <div class="step-indicator is-complete">
                <span class="step-icon"><img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14"></span>
                <span class="step-text">Data pelanggan</span>
            </div>
            <div class="step-indicator is-complete">
                <span class="step-icon"><img src="{{ asset('images/check.svg') }}" alt="" width="14" height="14"></span>
                <span class="step-text">Ringkasan &amp; pembayaran</span>
            </div>
            <div class="step-indicator is-active">
                <span class="step-icon-num">04</span>
                <span class="step-text-active">{{ (isset($isSubmitted) && $isSubmitted) ? 'Bukti terkirim' : 'Unggah struk' }}</span>
            </div>
            <div class="step-next-note">
                Berikutnya: tinjauan studio
            </div>
        </div>
        <hr class="divider">
    </section>

    {{-- Alert Notifikasi Sukses --}}
    @if(session('success'))
        <div style="margin: 0 72px 24px; padding: 18px 24px; background: #DCFCE7; border: 1px solid #86EFAC; border-radius: 8px; color: #166534; font-size: 14px; display: flex; align-items: center; gap: 12px;">
            <span style="font-weight: 700; font-size: 18px;">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Content Grid: Formulir Upload (Kiri) & Ringkasan Invoice/Kode (Kanan) --}}
    <div class="checkout-split-grid">
        {{-- KOLOM KIRI: FORMULIR KONFIRMASI PEMBAYARAN --}}
        @if(isset($isSubmitted) && $isSubmitted)
            {{-- TAMPILAN BUKTI PEMBAYARAN TERKIRIM (FIGMA NODE 105:8248) --}}
            <div class="checkout-box sent-proof-card">
                {{-- Judul Bukti --}}
                <div class="sent-proof-header">
                    <span class="badge-verification-status">Menunggu verifikasi</span>
                    <h2 class="card-title">Bukti pembayaran terkirim</h2>
                    <p class="body-text">
                        Terima kasih, {{ $submittedPayment['nama_pengirim'] ?? $data['nama_lengkap'] }}. Studio telah menerima bukti Anda pada {{ \Carbon\Carbon::parse($submittedPayment['tanggal_transfer'] ?? now())->translatedFormat('d F Y') }}. Pembayaran dan jadwal sesi belum dikonfirmasi.
                    </p>
                </div>

                {{-- Berkas yang Terkirim --}}
                <div class="proof-file-card">
                    <div class="proof-file-icon-wrap">
                        <img src="{{ asset('images/icon-file-text.svg') }}" alt="" width="24" height="24">
                    </div>
                    <div class="proof-file-details">
                        <strong class="proof-file-name">{{ $submittedPayment['file_name'] ?? 'bukti-transfer-contoh.pdf' }}</strong>
                        <span class="proof-file-meta">{{ $submittedPayment['file_ext'] ?? 'PDF' }} · Bukti diterima studio</span>
                    </div>
                </div>

                <p class="locked-file-disclaimer">
                    Berkas dan data transfer ini hanya ilustrasi, bukan struk atau transaksi asli.
                </p>

                <hr class="divider">

                {{-- Detail Transfer yang Dikirim --}}
                <div class="proof-detail-list">
                    <h4 class="subheader-title">Detail transfer yang dikirim</h4>

                    <div class="proof-detail-item">
                        <span class="proof-detail-label">Nama pengirim</span>
                        <strong class="proof-detail-val">{{ $submittedPayment['nama_pengirim'] ?? 'Ayu Pramesti' }}</strong>
                    </div>

                    <div class="proof-detail-row-2">
                        <div class="proof-detail-item">
                            <span class="proof-detail-label">Tanggal transfer</span>
                            <strong class="proof-detail-val">{{ \Carbon\Carbon::parse($submittedPayment['tanggal_transfer'] ?? now())->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div class="proof-detail-item">
                            <span class="proof-detail-label">Sumber pembayaran</span>
                            <strong class="proof-detail-val">{{ $submittedPayment['bank_sumber'] ?? 'Bank BCA' }}</strong>
                        </div>
                    </div>

                    <div class="proof-detail-item">
                        <span class="proof-detail-label">Nominal transfer</span>
                        <strong class="proof-detail-val">{{ $submittedPayment['nominal'] ?? $data['package_price'] }}</strong>
                    </div>

                    @if(!empty($submittedPayment['catatan_pembayaran']))
                        <div class="proof-detail-item">
                            <span class="proof-detail-label">Catatan pembayaran</span>
                            <span class="proof-detail-val">{{ $submittedPayment['catatan_pembayaran'] }}</span>
                        </div>
                    @endif
                </div>

                <hr class="divider">

                {{-- Panduan Status Readonly --}}
                <div class="notice-readonly-proof">
                    <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="18" height="18">
                    <div>
                        <h4 class="notice-readonly-title">Tidak perlu mengunggah bukti lagi</h4>
                        <p class="notice-readonly-desc">Data yang dikirim bersifat hanya-baca. Unggah kembali hanya jika studio meminta perbaikan, agar tidak ada bukti ganda.</p>
                    </div>
                </div>

                {{-- Tombol Aksi: Lihat Status Pesanan --}}
                <div class="form-actions-wrap">
                    <a href="{{ route('cek-pesanan', ['kode' => $data['order_code'] ?? 'CH-CONTOH-001']) }}" class="btn btn--fill btn--block btn--lg">
                        Lihat Status Pesanan @include('partials.arrow')
                    </a>
                    <p class="proof-footer-note">
                        Clara akan memberi kabar melalui email atau WhatsApp setelah pemeriksaan bukti dan jadwal.
                    </p>
                </div>
            </div>
        @else
            {{-- FORMULIR KONFIRMASI PEMBAYARAN (SEBELUM DIKIRIM) --}}
            <div class="checkout-box form-confirmation-card">
                {{-- BLOK TUJUAN PEMBAYARAN: TERKUNCI SESUAI PILIHAN DI CHECKOUT --}}
                <div class="payment-destination-box">
                    <div class="destination-header">
                        <div>
                            <span class="eyebrow">METODE PEMBAYARAN TERPILIH</span>
                            <h3 class="destination-title">
                                @if($metode === 'qris')
                                    QRIS (Scan QR Code)
                                @else
                                    Transfer Bank
                                @endif
                            </h3>
                        </div>
                        <a href="{{ route('checkout') }}" class="link-change-method">
                            Ubah metode pembayaran
                        </a>
                    </div>

                    @if($metode === 'transfer')
                        {{-- HANYA TRANSFER BANK --}}
                        <div class="pay-method-locked-content">
                            <p class="body-text font-13">Silakan transfer sesuai nominal invoice ke salah satu rekening resmi studio di bawah ini:</p>
                            
                            <div class="bank-cards-grid">
                                {{-- Kartu Bank BCA --}}
                                <div class="bank-card">
                                    <div class="bank-card__top">
                                        <span class="bank-badge bank-bca">BCA</span>
                                        <span class="bank-owner">a.n. Clara Hayes Studio</span>
                                    </div>
                                    <div class="bank-card__number-wrap">
                                        <span class="bank-number" id="bcaNumber">8735 0912 344</span>
                                        <button type="button" class="btn-icon-copy" title="Salin nomor rekening" onclick="copyText('87350912344', this)">
                                            <img src="{{ asset('images/icon-copy.svg') }}" alt="Salin" width="16" height="16">
                                        </button>
                                    </div>
                                </div>

                                {{-- Kartu Bank Mandiri --}}
                                <div class="bank-card">
                                    <div class="bank-card__top">
                                        <span class="bank-badge bank-mandiri">MANDIRI</span>
                                        <span class="bank-owner">a.n. Clara Hayes Studio</span>
                                    </div>
                                    <div class="bank-card__number-wrap">
                                        <span class="bank-number" id="mandiriNumber">137 00 2841 9920</span>
                                        <button type="button" class="btn-icon-copy" title="Salin nomor rekening" onclick="copyText('1370028419920', this)">
                                            <img src="{{ asset('images/icon-copy.svg') }}" alt="Salin" width="16" height="16">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="nominal-highlight-box">
                                <span class="font-12 muted">Nominal yang harus ditransfer:</span>
                                <div class="nominal-row">
                                    <strong class="nominal-val">{{ $data['package_price'] }}</strong>
                                    <button type="button" class="btn-icon-copy" title="Salin nominal" onclick="copyText('{{ preg_replace('/[^0-9]/', '', $data['package_price']) }}', this)">
                                        <img src="{{ asset('images/icon-copy.svg') }}" alt="Salin" width="16" height="16">
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- HANYA QRIS --}}
                        <div class="pay-method-locked-content">
                            <div class="qris-display-card">
                                <div class="qris-qr-frame">
                                    <div class="qris-header-tag">QRIS · National QR Standard</div>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=00020101021226500014ID.LINKAJA.WWW01189360091200000000015204581253033605802ID5919CLARA+HAYES+STUDIO6007JAKARTA61051219062070703A016304C2E4" alt="QRIS Clara Hayes Studio" class="qris-code-img">
                                    <span class="qris-merchant-name">CLARA HAYES STUDIO</span>
                                    <span class="qris-merchant-nns">NMID: ID1020039281749</span>
                                </div>
                                <div class="qris-instructions">
                                    <h4 class="font-14 font-semibold">Cara Pembayaran QRIS:</h4>
                                    <ol class="qris-steps-list">
                                        <li>Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI) atau e-Wallet (GoPay, OVO, Dana, ShopeePay).</li>
                                        <li>Pilih menu <strong>Pindai / Scan QRIS</strong>.</li>
                                        <li>Arahkan kamera ke kode QR di samping.</li>
                                        <li>Pastikan nama merchant: <strong>CLARA HAYES STUDIO</strong> dan nominal transfer: <strong>{{ $data['package_price'] }}</strong>.</li>
                                        <li>Selesaikan pembayaran dan simpan tangkapan layar (screenshot) bukti transfer.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="divider">

                <div class="form-section-title-wrap">
                    <div class="checkout-card-header">
                        <h2 class="card-title">Unggah bukti pembayaran</h2>
                        <span class="form-required-note">* Wajib diisi</span>
                    </div>
                    <p class="body-text">
                        Setelah berhasil melakukan transfer atau scan QRIS di atas, unggah struk atau tangkapan layar pembayaran di bawah ini.
                    </p>
                </div>

                <form action="{{ route('konfirmasi.store') }}" method="POST" enctype="multipart/form-data" id="confirmPaymentForm">
                    @csrf

                    {{-- Area Upload Bukti Pembayaran dengan Live Preview --}}
                    <div class="form-group">
                        <label class="form-label">Struk pembayaran *</label>
                        <div class="upload-dropzone-container">
                            <input type="file" name="struk_pembayaran" id="strukFile" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelected(this)" required>
                            
                            {{-- Tampilan Kosong (Awal) --}}
                            <div class="upload-dropzone" id="dropzoneEmpty" onclick="document.getElementById('strukFile').click()">
                                <div class="upload-icon-circle">
                                    <img src="{{ asset('images/icon-upload.svg') }}" alt="" width="24" height="24">
                                </div>
                                <strong class="dropzone-title">Tarik dan letakkan struk di sini</strong>
                                <span class="dropzone-subtitle">atau pilih berkas dari perangkat Anda</span>
                                <button type="button" class="btn btn--outline btn-sm">Pilih berkas</button>
                                <span class="dropzone-limit">JPG, PNG, atau PDF · Maks. 5 MB · 1 berkas</span>
                            </div>

                            {{-- Tampilan Preview (Saat Gambar Terisi) --}}
                            <div class="upload-preview-box d-none" id="dropzonePreview">
                                <div class="preview-img-wrap">
                                    <img id="previewImage" src="" alt="Pratinjau Struk" class="preview-img d-none">
                                    <div id="previewDocIcon" class="preview-doc-icon d-none">
                                        <span class="doc-badge">PDF</span>
                                    </div>
                                </div>
                                <div class="preview-details">
                                    <div class="preview-status-badge">
                                        <span class="status-check">✓</span> Berkas Berhasil Terpilih
                                    </div>
                                    <strong class="preview-filename" id="previewFileName">struk.jpg</strong>
                                    <span class="preview-filesize" id="previewFileSize">1.2 MB</span>
                                    <div class="preview-actions">
                                        <button type="button" class="btn btn--outline btn-sm" onclick="document.getElementById('strukFile').click()">
                                            Ganti Berkas
                                        </button>
                                        <button type="button" class="btn-remove-file" onclick="removeSelectedFile()">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tampilan Terkunci saat Pengiriman (Figma 105:7832) --}}
                            <div class="locked-file-display d-none" id="dropzoneLocked">
                                <div class="locked-file-icon-wrap">
                                    <img src="{{ asset('images/icon-file-text.svg') }}" alt="" width="24" height="24">
                                </div>
                                <div class="locked-file-info">
                                    <strong class="locked-file-title" id="lockedFileName">bukti-transfer.pdf</strong>
                                    <span class="locked-file-meta" id="lockedFileMeta">PDF · Sedang dikirim…</span>
                                </div>
                            </div>
                            <p class="locked-file-notice d-none" id="lockedFileNotice">
                                Berkas dan data dikunci sementara selama pengiriman.
                            </p>
                            <p class="locked-file-disclaimer d-none" id="lockedFileDisclaimer">
                                Berkas dan data transfer ini hanya ilustrasi, bukan struk atau transaksi asli.
                            </p>
                        </div>
                    </div>

                    <hr class="divider divider--lg">

                    {{-- Detail Transfer Form --}}
                    <div class="form-section">
                        <div>
                            <h4 class="subheader-title">Detail transfer</h4>
                            <p class="form-helper-text">Isi sesuai struk, termasuk jika pengirim berbeda dari pemesan. * Wajib diisi</p>
                        </div>

                        <div class="form-group">
                            <label for="nama_pengirim" class="form-label">Nama pengirim *</label>
                            <div class="input-locked-wrap">
                                <input type="text" id="nama_pengirim" name="nama_pengirim" class="form-control" placeholder="Nama pemilik rekening atau sumber pembayaran" required>
                                <img src="{{ asset('images/icon-lock.svg') }}" alt="" class="input-lock-icon d-none" id="lockIconNama">
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="tanggal_transfer" class="form-label">Tanggal transfer *</label>
                                <div class="input-locked-wrap">
                                    <input type="date" id="tanggal_transfer" name="tanggal_transfer" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    <img src="{{ asset('images/icon-lock.svg') }}" alt="" class="input-lock-icon d-none" id="lockIconTanggal">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bank_sumber" class="form-label">Bank / sumber pembayaran *</label>
                                <div class="input-locked-wrap">
                                    <input type="text" id="bank_sumber" name="bank_sumber" class="form-control" placeholder="Contoh: BCA / Mandiri / GoPay" required>
                                    <img src="{{ asset('images/icon-lock.svg') }}" alt="" class="input-lock-icon d-none" id="lockIconBank">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nominal" class="form-label">Nominal yang dibayarkan (Rp) *</label>
                            <div class="input-locked-wrap">
                                <input type="text" id="nominal" name="nominal" class="form-control" placeholder="Masukkan nominal sesuai struk" value="{{ $data['package_price'] }}" required>
                                <img src="{{ asset('images/icon-lock.svg') }}" alt="" class="input-lock-icon d-none" id="lockIconNominal">
                            </div>
                            <span class="form-helper-text">Total pada invoice: {{ $data['package_price'] }}. Masukkan jumlah yang benar-benar Anda transfer.</span>
                        </div>

                        <div class="form-group">
                            <label for="catatan_pembayaran" class="form-label">Catatan pembayaran (opsional)</label>
                            <div class="input-locked-wrap">
                                <textarea id="catatan_pembayaran" name="catatan_pembayaran" rows="3" class="form-control form-textarea" placeholder="Tambahkan informasi yang membantu studio memeriksa pembayaran Anda."></textarea>
                                <img src="{{ asset('images/icon-lock.svg') }}" alt="" class="input-lock-icon d-none" id="lockIconCatatan" style="top: 14px;">
                            </div>
                        </div>
                    </div>

                    {{-- Box Panduan Verifikasi / Status Pengiriman (Figma 105:7871) --}}
                    <div class="summary-notice notice-verification" id="noticeNormal">
                        <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon">
                        <div>
                            <strong class="notice-title">Pembayaran akan ditinjau oleh studio</strong>
                            <p>Mengunggah struk tidak otomatis mengonfirmasi pembayaran atau pemesanan. Clara akan memeriksa bukti dan ketersediaan jadwal, lalu menghubungi Anda melalui email atau WhatsApp.</p>
                        </div>
                    </div>

                    {{-- Box Status Sedang Mengirim (Figma 105:7871 - State Terkunci) --}}
                    <div class="notice-sending d-none" id="noticeSending">
                        <img src="{{ asset('images/icon-info.svg') }}" alt="" class="notice-icon" width="18" height="18">
                        <div>
                            <h4 class="notice-sending-title">Sedang mengirim bukti Anda…</h4>
                            <p class="notice-sending-desc">Mohon tunggu dan jangan tutup halaman. Tombol kirim dinonaktifkan agar bukti tidak terkirim dua kali. Belum ada konfirmasi penerimaan.</p>
                        </div>
                    </div>

                    {{-- Privasi Pembayaran --}}
                    <div class="privacy-note">
                        <img src="{{ asset('images/icon-shield-check.svg') }}" alt="" width="16" height="16">
                        <span>Data dan struk hanya digunakan untuk verifikasi oleh studio. Jangan sertakan PIN, kata sandi, atau kode OTP.</span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="form-actions-wrap">
                        <button type="submit" class="btn btn--fill btn--block btn--lg" id="btnSubmitPayment">
                            <span id="btnSubmitText">Kirim Konfirmasi Pembayaran</span>
                            <span id="btnSubmitArrow">@include('partials.arrow')</span>
                        </button>
                        <p class="sending-footnote d-none" id="sendingFootnote">
                            Pengiriman sedang berlangsung · Tidak perlu menekan kirim kembali.
                        </p>
                        <div class="card-footer-link" id="cancelLinkWrap">
                            <a href="{{ route('checkout') }}" class="link-blue-arrow">
                                ← Kembali ke checkout
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        {{-- KOLOM KANAN: KODE PESANAN & RINGKASAN INVOICE --}}
        <div class="checkout-split-right">
            {{-- KODE PESANAN CARD --}}
            <div class="checkout-box order-code-summary-box">
                <div class="order-code-header">
                    <div class="code-header-badge-wrap">
                        <h3 class="subheader-title">Kode Pesanan</h3>
                        <span class="badge-tag">CONTOH</span>
                    </div>
                    <span class="small-caps muted">Gunakan untuk melacak pesanan</span>
                </div>

                <div class="big-code-display">
                    <span id="orderCodeValue">{{ $data['order_code'] }}</span>
                    <button type="button" class="btn-icon-copy" id="btnCopyCode" title="Salin kode pesanan" onclick="copyOrderCode(this)">
                        <img src="{{ asset('images/icon-copy.svg') }}" alt="Salin" width="16" height="16">
                    </button>
                </div>

                <p class="body-text font-13">
                    Kode pesanan tersedia sejak permintaan pemesanan dikirim dan disertakan di email/WhatsApp pemesanan. Simpan kode ini agar Anda bisa melacak status pesanan dengan mudah.
                </p>
            </div>

            {{-- RINGKASAN INVOICE CARD --}}
            <div class="checkout-box mini-invoice-card">
                <div class="checkout-card-header">
                    <h3 class="card-title">Ringkasan invoice</h3>
                    <span class="status-badge">Draf · Belum dibayar</span>
                </div>

                <span class="invoice-number">Nomor contoh: {{ $data['invoice_number'] }}</span>

                {{-- Paket Terpilih Mini --}}
                <div class="mini-package-row">
                    <img src="{{ asset('images/' . $data['package_image']) }}" alt="{{ $data['package_name'] }}" class="mini-package-img">
                    <div class="mini-package-info">
                        <span class="eyebrow-small">PAKET TERPILIH</span>
                        <strong class="mini-package-name">{{ $data['package_name'] }}</strong>
                        <span class="mini-package-meta">1 sesi · {{ $data['package_duration'] ?? '90' }} menit</span>
                    </div>
                </div>

                <hr class="divider">

                {{-- Jadwal & Lokasi --}}
                <div class="detail-rows">
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
                    <div class="detail-col">
                        <img src="{{ asset('images/icon-map-pin.svg') }}" alt="" class="detail-icon">
                        <div>
                            <span class="detail-label">Lokasi sesi</span>
                            <strong class="detail-val">{{ $data['lokasi'] }}</strong>
                            <p class="detail-hint">{{ $data['lokasi_hint'] }}</p>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                {{-- Ditujukan kepada --}}
                <div class="invoice-bill-to">
                    <span class="small-caps muted">DITUJUKAN KEPADA</span>
                    <strong class="bill-name">{{ $data['nama_lengkap'] }}</strong>
                    <span class="bill-email">{{ $data['email'] }} · {{ $data['whatsapp'] }}</span>
                </div>

                {{-- Total Pembayaran Box --}}
                <div class="total-pay-box">
                    <span class="total-label">Total pembayaran</span>
                    <div class="total-amount">{{ $data['package_price'] }}</div>
                    <span class="total-note">Dalam rupiah (IDR) · sesuai harga paket</span>
                </div>

                <div class="session-pending-note">
                    <img src="{{ asset('images/icon-shield-check.svg') }}" alt="" width="14" height="14">
                    <span>Pemesanan masih menunggu konfirmasi studio.</span>
                </div>
            </div>

            {{-- Bantuan Pembayaran --}}
            <div class="checkout-box help-box">
                <h4 class="subheader-title">Belum melakukan transfer?</h4>
                <p class="body-text font-13">Gunakan instruksi pembayaran resmi dari studio sebelum mengirim struk. Jika belum menerimanya atau ada kendala, hubungi kami.</p>
                <a href="mailto:hello@clarahayes.studio" class="icon-item link-blue">
                    <img src="{{ asset('images/icon-email.svg') }}" alt="" width="18" height="18">
                    hello@clarahayes.studio
                </a>
            </div>
        </div>
    </div>
</main>

<script>
function handleFileSelected(input) {
    const emptyBox = document.getElementById('dropzoneEmpty');
    const previewBox = document.getElementById('dropzonePreview');
    const previewImg = document.getElementById('previewImage');
    const previewDoc = document.getElementById('previewDocIcon');
    const fileNameEl = document.getElementById('previewFileName');
    const fileSizeEl = document.getElementById('previewFileSize');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);

        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = `${sizeMb} MB · Siap dikonfirmasi`;

        // Jika file adalah gambar, tampilkan previewnya
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
                previewDoc.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            // Jika file dokumen (PDF)
            previewImg.classList.add('d-none');
            previewDoc.classList.remove('d-none');
        }

        // Tampilkan kotak preview, sembunyikan kotak upload kosong
        emptyBox.classList.add('d-none');
        previewBox.classList.remove('d-none');
    }
}

function removeSelectedFile() {
    const fileInput = document.getElementById('strukFile');
    const emptyBox = document.getElementById('dropzoneEmpty');
    const previewBox = document.getElementById('dropzonePreview');
    const previewImg = document.getElementById('previewImage');

    fileInput.value = '';
    previewImg.src = '';
    emptyBox.classList.remove('d-none');
    previewBox.classList.add('d-none');
}

function copyOrderCode(btn) {
    const code = document.getElementById('orderCodeValue').innerText;
    navigator.clipboard.writeText(code).then(() => {
        const btnElement = btn || document.getElementById('btnCopyCode');
        if (btnElement) {
            const originalHtml = btnElement.innerHTML;
            btnElement.innerHTML = '<span style="font-size:11px; font-weight:600; color:#2E7D32;">✓</span>';
            setTimeout(() => {
                btnElement.innerHTML = originalHtml;
            }, 1500);
        }
    });
}

function copyText(val, btnElement) {
    navigator.clipboard.writeText(val).then(() => {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<span style="font-size:11px; font-weight:600; color:#2E7D32;">✓</span>';
        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
        }, 1500);
    });
}

// Drag & drop highlight
const dropzone = document.getElementById('dropzoneEmpty');
if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });
    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length) {
            document.getElementById('strukFile').files = files;
            handleFileSelected(document.getElementById('strukFile'));
        }
    });
}

// Simulasi State Mengirim & Form Terkunci / Disable (Sesuai Figma Node 105:7767)
const confirmForm = document.getElementById('confirmPaymentForm');
if (confirmForm) {
    confirmForm.addEventListener('submit', function(e) {
        // Cek validasi standar HTML5
        if (!this.checkValidity()) {
            return;
        }

        // 1. Tampilkan state berkas terkunci
        const fileInput = document.getElementById('strukFile');
        const emptyBox = document.getElementById('dropzoneEmpty');
        const previewBox = document.getElementById('dropzonePreview');
        const lockedBox = document.getElementById('dropzoneLocked');
        const lockedTitle = document.getElementById('lockedFileName');
        const lockedMeta = document.getElementById('lockedFileMeta');
        const lockedNotice = document.getElementById('lockedFileNotice');
        const lockedDisclaimer = document.getElementById('lockedFileDisclaimer');

        if (fileInput.files && fileInput.files[0]) {
            const fileName = fileInput.files[0].name;
            const ext = fileName.split('.').pop().toUpperCase();
            lockedTitle.textContent = fileName;
            lockedMeta.textContent = `${ext} · Sedang dikirim…`;
        }

        emptyBox.classList.add('d-none');
        previewBox.classList.add('d-none');
        lockedBox.classList.remove('d-none');
        lockedNotice.classList.remove('d-none');
        lockedDisclaimer.classList.remove('d-none');

        // 2. Kunci semua input (Disabled / Readonly) & Tampilkan Ikon Gembok
        const inputIds = ['nama_pengirim', 'tanggal_transfer', 'bank_sumber', 'nominal', 'catatan_pembayaran'];
        inputIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.readOnly = true;
                el.style.backgroundColor = '#F5F8FF';
                el.style.cursor = 'not-allowed';
            }
        });

        const lockIcons = ['lockIconNama', 'lockIconTanggal', 'lockIconBank', 'lockIconNominal', 'lockIconCatatan'];
        lockIcons.forEach(id => {
            const icon = document.getElementById(id);
            if (icon) icon.classList.remove('d-none');
        });

        // 3. Ubah Box Notice ke "Sedang mengirim bukti Anda…" (Figma 105:7871)
        document.getElementById('noticeNormal').classList.add('d-none');
        document.getElementById('noticeSending').classList.remove('d-none');

        // 4. Ubah Tombol ke "Mengirim…" (Disabled abu-abu biru #B5C2DC)
        const btn = document.getElementById('btnSubmitPayment');
        btn.classList.add('btn-sending');
        btn.disabled = true;
        document.getElementById('btnSubmitText').textContent = 'Mengirim…';
        document.getElementById('btnSubmitArrow').classList.add('d-none');
        document.getElementById('sendingFootnote').classList.remove('d-none');
        document.getElementById('cancelLinkWrap').classList.add('d-none');
    });
}
</script>
@endsection

