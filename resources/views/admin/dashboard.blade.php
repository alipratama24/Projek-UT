@extends('layouts.admin')

@section('title', 'Dashboard — Admin Backoffice naro capture')
@section('breadcrumb', 'Dashboard / Selamat ' . (now()->hour < 12 ? 'pagi' : (now()->hour < 17 ? 'siang' : 'malam')) . ', Admin')

@section('content')
{{-- PENGANTAR HALAMAN --}}
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Selamat {{ now()->hour < 12 ? 'pagi' : (now()->hour < 17 ? 'siang' : 'malam') }}, Admin</h1>
        <p class="admin-page-subtitle">{{ $now->isoFormat('dddd, D MMMM Y') }} · Semua kebutuhan studio, dalam satu ruang.</p>
    </div>
    <a href="{{ route('pemesanan') }}" target="_blank" class="btn btn--fill btn-sm" style="background:#3B5B9A; color:#FFF; height:40px; padding:0 22px; display:inline-flex; align-items:center;">
        + Tambah pesanan
    </a>
</div>

{{-- BANNER PEMBERITAHUAN --}}
@if($buktiPendingCount > 0)
<div class="admin-alert-box alert-warning-box">
    <strong class="admin-alert-title text-gold">{{ $buktiPendingCount }} bukti pembayaran perlu ditinjau</strong>
    <span class="admin-alert-desc text-gold">
        Periksa bukti transfer, lalu konfirmasi jadwal secara terpisah. 
        <a href="{{ route('admin.pembayaran', ['tab' => 'bukti-terkirim']) }}" style="color:#94671E; text-decoration:underline; font-weight:600;">Lihat antrean pembayaran →</a>
    </span>
</div>
@else
<div class="admin-alert-box alert-success-box">
    <strong class="admin-alert-title text-green">Semua pembayaran telah ditinjau</strong>
    <span class="admin-alert-desc text-green">
        Tidak ada antrean bukti pembayaran yang menunggu verifikasi saat ini.
    </span>
</div>
@endif

{{-- METRIK RINGKASAN --}}
<div class="admin-metrics-grid">
    {{-- Metrik 1: Total Pesanan Real-Time --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Total pesanan</span>
            <img src="{{ asset('images/icon-clipboard-list.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">{{ $totalOrdersMonth }}</strong>
        <span class="admin-metric-footnote">{{ $permintaanCount }} permintaan · {{ $dikonfirmasiCount }} dikonfirmasi · {{ $selesaiCount }} selesai</span>
    </div>

    {{-- Metrik 2: Nilai Pesanan --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Nilai pesanan</span>
            <img src="{{ asset('images/icon-wallet.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">Rp{{ number_format($totalNominal / 1000000, 1, ',', '.') }} jt</strong>
        <span class="admin-metric-footnote">Rp{{ number_format($terverifikasiNominal / 1000000, 1, ',', '.') }} jt terverifikasi · Rp{{ number_format($pendingNominal / 1000000, 1, ',', '.') }} jt belum</span>
    </div>

    {{-- Metrik 3: Sesi Mendatang --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Sesi mendatang</span>
            <img src="{{ asset('images/icon-calendar.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">{{ $sesiMendatangCount }}</strong>
        <span class="admin-metric-footnote">Jadwal tercatat di sistem studio</span>
    </div>

    {{-- Metrik 4: Galeri dalam proses --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Galeri dalam proses</span>
            <img src="{{ asset('images/icon-images.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">{{ $galeriProsesCount }}</strong>
        <span class="admin-metric-footnote">{{ $galeriPenyuntingan }} penyuntingan · {{ $galeriDraf }} draf</span>
    </div>
</div>

{{-- ROW 2: TREN SESI & PRIORITAS HARI INI --}}
<div class="admin-grid-2">
    {{-- Panel Tren Permintaan Sesi --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Tren permintaan sesi</h3>
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span class="admin-metric-label">4 Bulan Terakhir · Jumlah pesanan tercatat</span>
            <span class="badge-status-session status-bukti-terkirim">● Bulan berjalan</span>
        </div>

        {{-- Bar Chart Visual --}}
        <div class="admin-bar-chart">
            @foreach($monthlyTrend as $trend)
                <div class="admin-bar-item">
                    <span class="admin-bar-val" style="{{ $trend['is_current'] ? 'font-weight:600;' : '' }}">{{ $trend['count'] }}</span>
                    <div class="admin-bar-pillar {{ $trend['is_current'] ? 'is-highlight' : '' }}" style="height: {{ $trend['height'] }}px;"></div>
                    <span class="admin-bar-label" style="{{ $trend['is_current'] ? 'font-weight:600; color:#253247;' : '' }}">{{ $trend['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Panel Prioritas Hari Ini --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Prioritas hari ini</h3>
        
        @if($prioritasOrder)
            <div style="display:flex; flex-direction:column; gap:6px;">
                <strong style="font-size:15px; color:#253247;">Permintaan {{ $prioritasOrder->customer_name }}</strong>
                <span class="admin-metric-label">
                    {{ $prioritasOrder->session_date ? $prioritasOrder->session_date->format('d M') : 'Segera' }} · 
                    {{ $prioritasOrder->package_name }} · 
                    {{ $prioritasOrder->session_time_slot }}
                </span>
                <span class="status-pill-gold">● {{ $prioritasOrder->session_status }} ({{ $prioritasOrder->payment_status }})</span>
            </div>

            <p class="body-text font-12" style="color:#253247; margin-top:8px;">
                Pesanan {{ $prioritasOrder->order_code }} senilai Rp{{ number_format($prioritasOrder->total_price, 0, ',', '.') }}.
                @if($prioritasOrder->payment_status === 'Bukti terkirim')
                    Bukti transfer telah diajukan dan menunggu peninjauan Anda.
                @else
                    Tinjau ketersediaan slot sebelum menyetujui sesi ini.
                @endif
            </p>

            <div style="margin-top:auto; padding-top:12px;">
                <a href="{{ route('admin.pesanan.detail', ['id' => $prioritasOrder->order_code]) }}" class="btn btn--outline btn-sm" style="height:40px; border-color:#DDDFD8; display:inline-flex; align-items:center;">
                    Tinjau pesanan →
                </a>
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:6px;">
                <strong style="font-size:15px; color:#253247;">Semua sesi tertangani</strong>
                <span class="admin-metric-label">Tidak ada pesanan tertunda yang memerlukan tinjauan mendesak.</span>
            </div>
            <div style="margin-top:auto; padding-top:12px;">
                <a href="{{ route('admin.pesanan') }}" class="btn btn--outline btn-sm" style="height:40px; border-color:#DDDFD8; display:inline-flex; align-items:center;">
                    Buka daftar pesanan →
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ROW 3: SESI TERDEKAT & AKTIVITAS TERBARU --}}
<div class="admin-grid-2">
    {{-- Panel Sesi Terdekat --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Sesi terdekat</h3>
        
        <div class="admin-table-simple">
            <div class="admin-table-head">
                <span>Pelanggan / paket</span>
                <span>Jadwal</span>
                <span>Status</span>
            </div>

            @forelse($sesiTerdekat as $sesi)
                <div class="admin-table-row">
                    <strong style="font-weight:500;">
                        <a href="{{ route('admin.pesanan.detail', ['id' => $sesi->order_code]) }}" style="color:#253247; text-decoration:none;">
                            {{ $sesi->customer_name }} · {{ $sesi->package_name }}
                        </a>
                    </strong>
                    <span>{{ $sesi->session_date ? $sesi->session_date->format('d M') : '-' }} · {{ explode(' ', $sesi->session_time_slot)[0] ?? '-' }}</span>
                    @if($sesi->session_status === 'Dikonfirmasi')
                        <span class="status-pill-green">● Dikonfirmasi</span>
                    @elseif($sesi->session_status === 'Selesai')
                        <span class="status-pill-green" style="background:#E2F0D9; color:#385723;">● Selesai</span>
                    @else
                        <span class="status-pill-gold">● {{ $sesi->session_status }}</span>
                    @endif
                </div>
            @empty
                <div class="admin-table-row">
                    <span class="text-muted" style="grid-column: span 3;">Belum ada jadwal sesi terdaftar.</span>
                </div>
            @endforelse
        </div>

        <a href="{{ route('admin.jadwal') }}" class="link-blue font-12" style="margin-top:auto; padding-top:10px;">
            Lihat kalender dan permintaan →
        </a>
    </div>

    {{-- Panel Aktivitas Terbaru --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Aktivitas terbaru</h3>
        
        <div class="admin-activity-list">
            @forelse($recentOrders as $ro)
                <div class="admin-activity-item">
                    <span class="admin-activity-time">● {{ $ro->created_at ? $ro->created_at->format('d M') : 'Baru' }}</span>
                    <div class="admin-activity-content">
                        <strong class="admin-activity-title">Pesanan Baru: {{ $ro->package_name }}</strong>
                        <span class="admin-activity-meta">{{ $ro->customer_name }} · {{ $ro->order_code }}</span>
                    </div>
                </div>
            @empty
            @endforelse

            @forelse($recentPayments as $rp)
                <div class="admin-activity-item">
                    <span class="admin-activity-time">● {{ $rp->created_at ? $rp->created_at->format('d M') : 'Baru' }}</span>
                    <div class="admin-activity-content">
                        <strong class="admin-activity-title">Pembayaran: {{ $rp->status }}</strong>
                        <span class="admin-activity-meta">{{ $rp->sender_name }} · Rp{{ number_format($rp->nominal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
            @endforelse
        </div>

        <a href="{{ route('admin.pesanan') }}" class="link-blue font-12" style="margin-top:auto; padding-top:10px;">
            Lihat seluruh aktivitas pesanan →
        </a>
    </div>
</div>

<p class="admin-footer-copyright">
    © 2026 naro capture · Terhubung ke Database SQLite · Akses terbatas admin
</p>
@endsection
