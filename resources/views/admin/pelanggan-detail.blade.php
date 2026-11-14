@extends('layouts.admin')

@section('title', 'Detail Pelanggan ' . $customer->name . ' — naro capture')
@section('breadcrumb', 'Pelanggan / ' . $customer->name)

@section('content')
    {{-- HEADER HALAMAN DETAIL PELANGGAN --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                <a href="{{ route('admin.pelanggan') }}" class="admin-table-action-link" style="font-size:13px; text-decoration:none;">
                    &larr; Daftar Pelanggan
                </a>
                <span style="color:#A3A79E;">/</span>
                <span class="order-code-mono" style="font-size:13px; color:#6E7A8B;">{{ $customer->customer_code }}</span>
            </div>
            <h1 class="admin-page-title">{{ $customer->name }}</h1>
            <p class="admin-page-subtitle">
                Terdaftar sejak {{ $customer->created_at ? $customer->created_at->translatedFormat('d F Y') : '2026' }} · 
                <span class="badge-status-payment {{ $customer->status === 'aktif' ? 'status-terverifikasi' : 'status-belum-dibayar' }}">
                    {{ ucfirst($customer->status) }}
                </span>
            </p>
        </div>

        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ $whatsappLink }}" target="_blank" class="admin-btn-outline" style="color:#059669; border-color:#A7F3D0; background:#ECFDF5; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                Chat WhatsApp
            </a>

            <button type="button" class="admin-btn-outline" id="btn-open-edit-customer">
                Edit Profil
            </button>

            <form action="{{ route('admin.pelanggan.toggle', ['id' => $customer->customer_code]) }}" method="POST">
                @csrf
                <button type="submit" class="admin-btn-outline" style="{{ $customer->status === 'aktif' ? 'color:#DC2626; border-color:#FECACA; background:#FEF2F2;' : 'color:#059669; border-color:#A7F3D0; background:#ECFDF5;' }}">
                    {{ $customer->status === 'aktif' ? 'Arsipkan' : 'Aktifkan Kembali' }}
                </button>
            </form>
        </div>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom:20px;">
            <strong class="admin-alert-title text-green">Berhasil</strong>
            <p class="admin-alert-desc text-green">
                {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- KARTU STATISTIK PELANGGAN --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="admin-panel" style="margin-bottom:0; padding:20px;">
            <span class="admin-meta-label" style="font-size:12px;">Total Nilai Transaksi (LTV)</span>
            <h3 style="font-size:22px; font-weight:700; color:#253247; margin-top:4px;">Rp{{ number_format($totalSpending, 0, ',', '.') }}</h3>
            <span style="font-size:12px; color:#6E7A8B; margin-top:2px; display:block;">Dari seluruh pemesanan studio</span>
        </div>

        <div class="admin-panel" style="margin-bottom:0; padding:20px;">
            <span class="admin-meta-label" style="font-size:12px;">Total Pesanan Sesi</span>
            <h3 style="font-size:22px; font-weight:700; color:var(--blue, #3B5B9A); margin-top:4px;">{{ $orders->count() }} Pesanan</h3>
            <span style="font-size:12px; color:#6E7A8B; margin-top:2px; display:block;">{{ $completedSessions }} sesi selesai terlaksana</span>
        </div>

        <div class="admin-panel" style="margin-bottom:0; padding:20px;">
            <span class="admin-meta-label" style="font-size:12px;">Sesi Aktif / Mendatang</span>
            <h3 style="font-size:22px; font-weight:700; color:{{ $upcomingSessions > 0 ? '#10B981' : '#6E7A8B' }}; margin-top:4px;">{{ $upcomingSessions }} Sesi</h3>
            <span style="font-size:12px; color:#6E7A8B; margin-top:2px; display:block;">Jadwal yang sedang berjalan</span>
        </div>
    </div>

    {{-- GRID UTAMA: KOLOM KIRI (RIWAYAT PESANAN) & KOLOM KANAN (PROFIL & CATATAN) --}}
    <div class="admin-order-detail-grid">
        {{-- KOLOM KIRI: RIWAYAT PESANAN & CATATAN KHUSUS --}}
        <div class="admin-order-left-col">
            {{-- PANEL TABEL RIWAYAT PESANAN --}}
            <div class="admin-panel">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div>
                        <h2 class="admin-panel-title" style="margin-bottom:2px;">Riwayat Pesanan &amp; Sesi Foto</h2>
                        <p style="font-size:13px; color:#6E7A8B; margin:0;">Daftar seluruh transaksi dan pemesanan yang pernah dilakukan oleh pelanggan ini.</p>
                    </div>
                </div>

                <div class="admin-table-container">
                    <table class="admin-orders-table">
                        <thead>
                            <tr>
                                <th style="width:25%;">Kode / Invoice</th>
                                <th style="width:25%;">Paket Layanan</th>
                                <th style="width:20%;">Jadwal Sesi</th>
                                <th style="width:15%;">Total</th>
                                <th style="width:15%;">Status</th>
                                <th style="width:10%; text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $ord)
                                <tr>
                                    <td>
                                        <div class="table-cell-order">
                                            <a href="{{ route('admin.pesanan.detail', ['id' => $ord->order_code]) }}" style="text-decoration:none;">
                                                <strong class="order-customer-name" style="color:var(--blue, #3B5B9A);">{{ $ord->order_code }}</strong>
                                            </a>
                                            <span class="order-code-mono text-muted">{{ $ord->invoice_number }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="package-name-text">{{ $ord->package_name }}</span>
                                        <span style="font-size:11px; color:#6E7A8B; display:block;">{{ $ord->location }}</span>
                                    </td>
                                    <td>
                                        <span class="schedule-date-text">{{ $ord->session_date ? $ord->session_date->format('d M Y') : '-' }}</span>
                                        <span style="font-size:11px; color:#6E7A8B; display:block;">{{ $ord->session_time_slot }}</span>
                                    </td>
                                    <td>
                                        <strong style="font-size:13px; color:#253247;">Rp{{ number_format($ord->total_price, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <div style="display:flex; flex-direction:column; gap:4px;">
                                            <span class="badge-status-payment {{ in_array($ord->payment_status, ['Terverifikasi', 'Lunas']) ? 'status-terverifikasi' : 'status-belum-dibayar' }}" style="font-size:10px; width:fit-content;">
                                                {{ $ord->payment_status ?: 'Belum dibayar' }}
                                            </span>
                                            <span style="font-size:11px; color:#6E7A8B;">
                                                Sesi: <strong>{{ $ord->session_status }}</strong>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="{{ route('admin.pesanan.detail', ['id' => $ord->order_code]) }}" class="admin-table-action-link" title="Buka Detail Pesanan">
                                            Pesanan &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:36px; color:#6E7A8B;">
                                        Belum ada riwayat pesanan yang terhubung dengan pelanggan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PANEL CATATAN KHUSUS & PREFERENSI STUDIO --}}
            <div class="admin-panel">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h2 class="admin-panel-title">Catatan Preferensi Studio</h2>
                    <button type="button" class="admin-btn-outline" style="font-size:11px; padding:4px 10px;" id="btn-edit-notes">
                        Ubah Catatan
                    </button>
                </div>
                <p style="font-size:13px; color:#6E7A8B; margin-bottom:12px;">
                    Catatan khusus untuk preferensi pencahayaan, konsep foto keluarga, tanggal ulang tahun, atau preferensi pelayanan.
                </p>

                <div style="background:#F8FAFD; border:1px solid #E1E7F0; border-radius:8px; padding:16px;">
                    @if(!empty($customer->notes))
                        <p style="font-size:13px; color:#253247; line-height:1.6; margin:0; white-space:pre-line;">{{ $customer->notes }}</p>
                    @else
                        <span style="font-size:13px; color:#94A3B8; font-style:italic;">Belum ada catatan preferensi untuk pelanggan ini. Klik "Ubah Catatan" untuk menambahkan.</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: DETAIL KONTAK & INFORMASI PROFIL --}}
        <div class="admin-order-right-col">
            <div class="admin-panel">
                <h2 class="admin-panel-title">Informasi Pelanggan</h2>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">ID Pelanggan</label>
                    <p class="admin-meta-val font-mono">{{ $customer->customer_code }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nama Lengkap</label>
                    <p class="admin-meta-val" style="font-size:15px; font-weight:600; color:#253247;">{{ $customer->name }}</p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Nomor WhatsApp</label>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:2px;">
                        <span class="admin-meta-val font-mono">{{ $customer->whatsapp }}</span>
                        <a href="{{ $whatsappLink }}" target="_blank" style="font-size:12px; color:var(--blue, #3B5B9A); text-decoration:none; font-weight:500;">
                            Kirim WA &nearr;
                        </a>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Email</label>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:2px;">
                        <span class="admin-meta-val">{{ $customer->email }}</span>
                        <a href="mailto:{{ $customer->email }}" style="font-size:12px; color:var(--blue, #3B5B9A); text-decoration:none; font-weight:500;">
                            Kirim Email &nearr;
                        </a>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Status Pelanggan</label>
                    <p class="admin-meta-val">
                        <span class="badge-status-payment {{ $customer->status === 'aktif' ? 'status-terverifikasi' : 'status-belum-dibayar' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </p>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Terdaftar Sejak</label>
                    <p class="admin-meta-val">{{ $customer->created_at ? $customer->created_at->translatedFormat('d F Y, H:i \W\I\B') : '-' }}</p>
                </div>

                <hr class="admin-divider" style="margin:16px 0; border:0; border-top:1px solid #E1E7F0;">

                <a href="{{ url('/admin/pesanan?search=' . urlencode($customer->name)) }}" class="admin-btn-outline w-full text-center" style="display:block; text-decoration:none; font-size:12px;">
                    Cari Semua Pesanan Pelanggan Ini &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT PROFIL PELANGGAN --}}
    <div class="admin-modal-overlay" id="modal-edit-customer">
        <div class="admin-modal-dialog" style="max-width:480px;">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Edit Profil Pelanggan</h3>
                <button type="button" class="admin-btn-close-modal" data-close="modal-edit-customer">✕</button>
            </div>
            <p class="admin-modal-subtitle">Perbarui data kontak dan catatan preferensi studio.</p>

            <form action="{{ route('admin.pelanggan.update', ['id' => $customer->customer_code]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="admin-form-group">
                    <label class="admin-form-label">Nama Lengkap <span class="text-red">*</span></label>
                    <input type="text" name="name" class="admin-form-input" value="{{ $customer->name }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Email <span class="text-red">*</span></label>
                    <input type="email" name="email" class="admin-form-input" value="{{ $customer->email }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Nomor WhatsApp <span class="text-red">*</span></label>
                    <input type="text" name="whatsapp" class="admin-form-input" value="{{ $customer->whatsapp }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Status Akun</label>
                    <select name="status" class="admin-form-input">
                        <option value="aktif" {{ $customer->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="diarsipkan" {{ $customer->status === 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Catatan Preferensi Studio</label>
                    <textarea name="notes" rows="3" class="admin-form-input" style="resize:vertical;" placeholder="Preferensi pose, konsep foto, catatan keluarga...">{{ $customer->notes }}</textarea>
                </div>

                <div class="admin-modal-actions" style="margin-top:16px;">
                    <button type="button" class="admin-btn-outline" data-close="modal-edit-customer">Batal</button>
                    <button type="submit" class="admin-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Sistem Manajemen Pelanggan (CRM) · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editModal = document.getElementById('modal-edit-customer');
            const openBtns = ['btn-open-edit-customer', 'btn-edit-notes'];

            openBtns.forEach(id => {
                const btn = document.getElementById(id);
                if (btn && editModal) {
                    btn.addEventListener('click', () => editModal.classList.add('is-open'));
                }
            });

            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-close');
                    const target = document.getElementById(targetId);
                    if (target) target.classList.remove('is-open');
                });
            });
        });
    </script>
@endsection
