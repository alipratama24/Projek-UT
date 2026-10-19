@extends('layouts.admin')

@section('title', 'Dashboard — Admin Backoffice Clara Hayes')
@section('breadcrumb', 'Dashboard / Selamat pagi, Clara')

@section('content')
{{-- PENGANTAR HALAMAN (FIGMA 141:885) --}}
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Selamat pagi, Clara</h1>
        <p class="admin-page-subtitle">Selasa, 6 Oktober 2026 · Semua kebutuhan studio, dalam satu ruang.</p>
    </div>
    <a href="{{ url('/admin/pesanan') }}" class="btn btn--fill btn-sm" style="background:#3B5B9A; color:#FFF; height:40px; padding:0 22px;">
        Tambah pesanan
    </a>
</div>

{{-- BANNER PEMBERITAHUAN (FIGMA 141:896) --}}
<div class="admin-alert-box">
    <strong class="admin-alert-title">3 bukti pembayaran perlu ditinjau</strong>
    <span class="admin-alert-desc">
        Periksa bukti, lalu konfirmasi jadwal secara terpisah. 
        <a href="{{ url('/admin/pembayaran') }}" style="color:#3B5B9A; text-decoration:underline; font-weight:500;">Lihat antrean pembayaran →</a>
    </span>
</div>

{{-- METRIK RINGKASAN (FIGMA 141:899) --}}
<div class="admin-metrics-grid">
    {{-- Metrik 1: Pesanan Oktober --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Pesanan Oktober</span>
            <img src="{{ asset('images/icon-clipboard-list.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">12</strong>
        <span class="admin-metric-footnote">4 permintaan · 6 dikonfirmasi · 2 selesai</span>
    </div>

    {{-- Metrik 2: Nilai Pesanan --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Nilai pesanan</span>
            <img src="{{ asset('images/icon-wallet.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">Rp109 jt</strong>
        <span class="admin-metric-footnote">Rp81 jt terverifikasi · Rp28 jt belum</span>
    </div>

    {{-- Metrik 3: Sesi Mendatang --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Sesi mendatang</span>
            <img src="{{ asset('images/icon-calendar.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">6</strong>
        <span class="admin-metric-footnote">Dalam bulan ini · Jadwal disetujui</span>
    </div>

    {{-- Metrik 4: Galeri dalam proses --}}
    <div class="admin-metric-card">
        <div class="admin-metric-header">
            <span class="admin-metric-label">Galeri dalam proses</span>
            <img src="{{ asset('images/icon-images.svg') }}" alt="" width="20" height="20">
        </div>
        <strong class="admin-metric-value">3</strong>
        <span class="admin-metric-footnote">2 penyuntingan · 1 draf</span>
    </div>
</div>

{{-- ROW 2: TREN SESI & PRIORITAS HARI INI (FIGMA 141:924) --}}
<div class="admin-grid-2">
    {{-- Panel Tren Permintaan Sesi --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Tren permintaan sesi</h3>
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span class="admin-metric-label">Juli–Oktober 2026 · Jumlah pesanan ilustratif</span>
            <span class="badge-status-session status-bukti-terkirim">● Bulan berjalan</span>
        </div>

        {{-- Bar Chart Visual --}}
        <div class="admin-bar-chart">
            <div class="admin-bar-item">
                <span class="admin-bar-val">8</span>
                <div class="admin-bar-pillar" style="height: 80px;"></div>
                <span class="admin-bar-label">Jul</span>
            </div>
            <div class="admin-bar-item">
                <span class="admin-bar-val">10</span>
                <div class="admin-bar-pillar" style="height: 100px;"></div>
                <span class="admin-bar-label">Agu</span>
            </div>
            <div class="admin-bar-item">
                <span class="admin-bar-val">9</span>
                <div class="admin-bar-pillar" style="height: 90px;"></div>
                <span class="admin-bar-label">Sep</span>
            </div>
            <div class="admin-bar-item">
                <span class="admin-bar-val" style="font-weight:600;">12</span>
                <div class="admin-bar-pillar is-highlight" style="height: 120px;"></div>
                <span class="admin-bar-label" style="font-weight:600; color:#253247;">Okt</span>
            </div>
        </div>
    </div>

    {{-- Panel Prioritas Hari Ini (Ayu Pramesti) --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Prioritas hari ini</h3>
        
        <div style="display:flex; flex-direction:column; gap:6px;">
            <strong style="font-size:15px; color:#253247;">Permintaan Ayu Pramesti</strong>
            <span class="admin-metric-label">17 Okt · Cerita Keluarga · 15.30 WIB</span>
            <span class="status-pill-gold">● Jadwal belum dikonfirmasi</span>
        </div>

        <p class="body-text font-12" style="color:#253247;">
            Bukti Rp9.500.000 dikirim 5 Oktober. Tinjau terlebih dahulu sebelum menyetujui sesi.
        </p>

        <div>
            <a href="{{ url('/admin/pembayaran') }}" class="btn btn--outline btn-sm" style="height:40px; border-color:#DDDFD8;">
                Tinjau pembayaran
            </a>
        </div>
    </div>
</div>

{{-- ROW 3: SESI TERDEKAT & AKTIVITAS TERBARU (FIGMA 141:957) --}}
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

            <div class="admin-table-row">
                <strong style="font-weight:500;">Nadia Putri · Potret Pribadi</strong>
                <span>10 Okt · 09.00–09.45</span>
                <span class="status-pill-green">● Dikonfirmasi</span>
            </div>

            <div class="admin-table-row">
                <strong style="font-weight:500;">Dimas &amp; Rani · Cerita Keluarga</strong>
                <span>12 Okt · 15.30–17.00</span>
                <span class="status-pill-green">● Dikonfirmasi</span>
            </div>

            <div class="admin-table-row">
                <strong style="font-weight:500;">Ayu Pramesti · Cerita Keluarga</strong>
                <span>17 Okt · 15.30–17.00</span>
                <span class="status-pill-gold">● Permintaan</span>
            </div>
        </div>

        <a href="{{ url('/admin/jadwal') }}" class="link-blue font-12" style="margin-top:auto;">
            Lihat kalender dan permintaan →
        </a>
    </div>

    {{-- Panel Aktivitas Terbaru --}}
    <div class="admin-panel">
        <h3 class="admin-panel-title">Aktivitas terbaru</h3>
        
        <div class="admin-activity-list">
            <div class="admin-activity-item">
                <span class="admin-activity-time">● 09.20</span>
                <div class="admin-activity-content">
                    <strong class="admin-activity-title">Catatan pelanggan diperbarui</strong>
                    <span class="admin-activity-meta">Clara · Nadia Putri</span>
                </div>
            </div>

            <div class="admin-activity-item">
                <span class="admin-activity-time">● 08.45</span>
                <div class="admin-activity-content">
                    <strong class="admin-activity-title">Galeri disimpan sebagai draf</strong>
                    <span class="admin-activity-meta">Clara · Potret Nadia</span>
                </div>
            </div>

            <div class="admin-activity-item">
                <span class="admin-activity-time">● 5 Okt</span>
                <div class="admin-activity-content">
                    <strong class="admin-activity-title">Bukti pembayaran diterima</strong>
                    <span class="admin-activity-meta">Ayu · CH-CONTOH-001</span>
                </div>
            </div>
        </div>

        <a href="{{ url('/admin/pesanan') }}" class="link-blue font-12" style="margin-top:auto;">
            Lihat seluruh aktivitas →
        </a>
    </div>
</div>

<p class="admin-footer-copyright">
    © 2026 Clara Hayes Photography · Data ilustrasi · Akses terbatas admin
</p>
@endsection

