@extends('layouts.admin')

@section('title', 'Kalender Studio — naro capture')
@section('breadcrumb', 'Jadwal / Kalender studio')

@section('content')
    {{-- PENGANTAR HALAMAN --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Kalender studio</h1>
            <p class="admin-page-subtitle">Pisahkan ketersediaan, permintaan tentatif, dan sesi yang disetujui. Zona waktu WIB.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-add-slot">
            + Tambah slot
        </button>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom:16px;">
            <strong class="admin-alert-title text-green">Berhasil</strong>
            <p class="admin-alert-desc text-green">{{ session('success') }}</p>
        </div>
    @endif

    {{-- FILTER & SEARCH BAR --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.jadwal') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; width:100%;">
            <div class="admin-select-dropdown" style="padding:0;">
                <input type="month" name="bulan" class="admin-form-input" style="border:none; padding:8px 12px; background:transparent; font-size:13px;" value="{{ $selectedMonth }}" onchange="this.form.submit()">
            </div>

            <div style="margin-left:auto; display:flex; gap:12px; align-items:center;">
                <span class="status-indicator-item text-green">● {{ $bookedSlots }} Disetujui/Terpesan</span>
                <span class="status-indicator-item text-blue">● {{ $availableSlots }} Tersedia</span>
                <span class="status-indicator-item text-muted">● {{ $blockedSlots }} Blokir</span>
            </div>
        </form>
    </div>

    {{-- GRID KALENDER: KIRI (KALENDER & DAFTAR SLOT) / KANAN (DETAIL TANGGAL & AKSI) --}}
    <div class="admin-calendar-layout-grid">
        {{-- KOLOM KIRI: KALENDER INTERAKTIF --}}
        <div class="admin-panel admin-calendar-main-panel">
            {{-- NAVIGASI BULAN --}}
            <div class="admin-calendar-nav-bar">
                <h2 class="admin-calendar-month-heading">{{ $monthDate->translatedFormat('F Y') }}</h2>
                <div class="admin-calendar-nav-actions">
                    <a href="{{ route('admin.jadwal', ['bulan' => $monthDate->copy()->subMonth()->format('Y-m')]) }}" class="admin-btn-outline btn-sm">‹ Bulan Lalu</a>
                    <a href="{{ route('admin.jadwal', ['bulan' => now()->format('Y-m')]) }}" class="admin-btn-outline btn-sm">Hari ini</a>
                    <a href="{{ route('admin.jadwal', ['bulan' => $monthDate->copy()->addMonth()->format('Y-m')]) }}" class="admin-btn-outline btn-sm">Bulan Depan ›</a>
                </div>
            </div>

            {{-- KEPALA HARI --}}
            <div class="admin-calendar-weekdays">
                <span>Sen</span>
                <span>Sel</span>
                <span>Rab</span>
                <span>Kam</span>
                <span>Jum</span>
                <span>Sab</span>
                <span>Min</span>
            </div>

            {{-- GRID BULAN --}}
            @php
                $startOfMonth = $monthDate->copy()->startOfMonth();
                $endOfMonth = $monthDate->copy()->endOfMonth();
                $dayOfWeek = ($startOfMonth->dayOfWeekIso - 1); // 0 (Mon) to 6 (Sun)
                $daysInMonth = $monthDate->daysInMonth;
            @endphp

            <div class="admin-calendar-month-grid">
                {{-- Padding hari sebelum tanggal 1 --}}
                @for ($p = 0; $p < $dayOfWeek; $p++)
                    <div class="calendar-day-cell is-muted">
                        <span class="day-num">{{ $startOfMonth->copy()->subDays($dayOfWeek - $p)->day }}</span>
                    </div>
                @endfor

                {{-- Hari-hari bulan ini --}}
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $currentDateStr = $monthDate->copy()->day($day)->format('Y-m-d');
                        $daySlots = $slots->filter(fn($sl) => $sl->date && $sl->date->format('Y-m-d') === $currentDateStr);
                        $dayOrders = $monthOrders->filter(fn($ord) => $ord->session_date && $ord->session_date->format('Y-m-d') === $currentDateStr);
                        $hasEvent = $daySlots->isNotEmpty() || $dayOrders->isNotEmpty();
                    @endphp
                    <div class="calendar-day-cell cal-day-interactive {{ $hasEvent ? 'is-highlight' : '' }}"
                        data-date="{{ $currentDateStr }}"
                        data-label="{{ $monthDate->copy()->day($day)->translatedFormat('l, d F Y') }}"
                        style="cursor: pointer;"
                        title="Klik untuk memilih tanggal {{ $day }} {{ $monthDate->translatedFormat('F Y') }}">
                        <span class="day-num">{{ $day }}</span>
                        
                        @foreach($dayOrders as $ord)
                            <div class="cal-event-pill {{ $ord->session_status === 'Dikonfirmasi' ? 'event-approved' : 'event-tentative' }}" title="{{ $ord->customer_name }} ({{ $ord->package_name }})">
                                {{ explode(' ', $ord->session_time_slot)[0] ?? '' }} {{ \Illuminate\Support\Str::limit($ord->customer_name, 6) }}<br>
                                {{ $ord->session_status }}
                            </div>
                        @endforeach

                        @foreach($daySlots as $sl)
                            @if($sl->type === 'available')
                                <div class="cal-event-pill event-available" title="Slot Tersedia">
                                    {{ substr($sl->start_time, 0, 5) }}–{{ substr($sl->end_time, 0, 5) }}<br>Tersedia
                                </div>
                            @elseif($sl->type === 'blocked')
                                <div class="cal-event-pill event-blocked" title="{{ $sl->note ?? 'Blok Studio' }}">
                                    Blokir Studio<br>{{ \Illuminate\Support\Str::limit($sl->note, 10) }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endfor
            </div>

            {{-- LEGENDA INDIKATOR --}}
            <div class="admin-calendar-legend-bar">
                <span class="legend-item text-green">● Sesi disetujui</span>
                <span class="legend-item text-gold">● Permintaan tentatif</span>
                <span class="legend-item text-blue">● Tersedia</span>
                <span class="legend-item text-muted">● Blok studio</span>
            </div>
        </div>

        {{-- KOLOM KANAN: DAFTAR SLOT & AKSI --}}
        <div class="admin-calendar-sidebar-detail">
            <div class="admin-panel">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                    <div>
                        <h3 class="admin-panel-title" id="sidebar-slot-title" style="margin-bottom:2px;">Daftar Slot {{ $monthDate->translatedFormat('F Y') }}</h3>
                        <span class="status-indicator-item text-blue" id="sidebar-slot-count">● {{ $slots->count() }} Slot Tercatat</span>
                    </div>
                    <button type="button" id="btn-reset-calendar-filter" class="admin-btn-outline btn-sm" style="display:none; font-size:11px; padding:3px 10px;" title="Reset pilihan tanggal dan tampilkan semua">
                        Semua Slot ↺
                    </button>
                </div>

                {{-- Alert Informasi Tanggal Terpilih --}}
                <div id="selected-date-banner" style="display:none; margin-top:12px; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:6px; padding:8px 12px; font-size:12px; color:#1E40AF;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📅 Tanggal: <strong id="selected-date-text">-</strong></span>
                        <button type="button" id="btn-add-slot-for-date" class="admin-btn-outline btn-sm" style="background:#FFFFFF; font-size:11px; padding:2px 8px; color:#2563EB;">
                            + Tambah Slot
                        </button>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px; margin-top:14px; max-height:480px; overflow-y:auto; padding-right:4px;" id="sidebar-slots-container">
                    @forelse($slots as $slot)
                        @php
                            $slotDateFormatted = Carbon\Carbon::parse($slot->date)->format('Y-m-d');
                        @endphp
                        <div class="slot-item-card" data-date="{{ $slotDateFormatted }}" style="border:1px solid #E3E5DF; border-radius:6px; padding:12px; background:#FAFAFA;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <strong style="font-size:13px; color:#253247;">{{ Carbon\Carbon::parse($slot->date)->translatedFormat('d M Y') }}</strong>
                                @if($slot->type === 'available')
                                    <span class="status-indicator-item text-blue" style="font-size:11px;">● Tersedia</span>
                                @elseif($slot->type === 'blocked')
                                    <span class="status-indicator-item text-muted" style="font-size:11px;">● Blokir</span>
                                @else
                                    <span class="status-indicator-item text-green" style="font-size:11px;">● Terpesan</span>
                                @endif
                            </div>

                            <p style="font-size:12px; margin:4px 0 8px 0; color:#576478;">
                                Waktu: <strong>{{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }} WIB</strong>
                                @if($slot->note)
                                    <br><span style="font-style:italic;">Catatan: {{ $slot->note }}</span>
                                @endif
                            </p>

                            <div style="display:flex; gap:8px;">
                                <button type="button" class="admin-btn-outline btn-sm btn-edit-slot" 
                                    data-id="{{ $slot->id }}"
                                    data-date="{{ $slot->date }}"
                                    data-start="{{ substr($slot->start_time, 0, 5) }}"
                                    data-end="{{ substr($slot->end_time, 0, 5) }}"
                                    data-type="{{ $slot->type }}"
                                    data-note="{{ $slot->note }}"
                                    style="padding:2px 8px; font-size:11px;">
                                    Edit
                                </button>
                                <form action="{{ route('admin.jadwal.slot.destroy', ['id' => $slot->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus slot ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-outline btn-sm btn-outline-danger" style="padding:2px 8px; font-size:11px;">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted font-12" id="slot-empty-default">Belum ada slot khusus yang dibuat untuk bulan ini. Klik "+ Tambah slot" di atas untuk menambahkan slot waktu atau jadwal libur/maintenance studio.</p>
                    @endforelse

                    <div id="slot-empty-filter-msg" style="display:none; padding:16px; text-align:center; background:#F8FAFD; border:1px dashed #CBD5E1; border-radius:6px;">
                        <p style="font-size:12px; color:#64748B; margin:0 0 10px 0;">Tidak ada slot khusus pada tanggal yang dipilih.</p>
                        <button type="button" id="btn-quick-add-slot" class="admin-btn-outline btn-sm" style="color:#2563EB; border-color:#BFDBFE; background:#EFF6FF; font-size:11px;">
                            + Buat Slot di Tanggal Ini
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DIALOG TAMBAH SLOT --}}
    <div class="admin-modal-overlay" id="modal-add-slot">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.jadwal.slot.store') }}" method="POST">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Tambah slot waktu</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-add-slot">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">Buka ketersediaan sesi baru atau blokir studio</p>
                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Tanggal *</label>
                        <input type="date" id="create-slot-date" name="date" class="admin-form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Jenis slot *</label>
                        <select name="type" class="admin-form-input" required>
                            <option value="available" selected>Slot Tersedia untuk Pelanggan</option>
                            <option value="blocked">Blokir Studio (Libur / Maintenance)</option>
                        </select>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu mulai *</label>
                        <input type="time" name="start_time" class="admin-form-input" value="09:00" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu selesai *</label>
                        <input type="time" name="end_time" class="admin-form-input" value="10:30" required>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan internal (opsional)</label>
                    <input type="text" name="note" class="admin-form-input" placeholder="Contoh: Sesi pagi atau kalibrasi lampu">
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-add-slot">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Simpan slot
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DIALOG EDIT SLOT --}}
    <div class="admin-modal-overlay" id="modal-edit-slot">
        <div class="admin-modal-dialog">
            <form id="form-edit-slot" method="POST">
                @csrf
                @method('PUT')
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Edit slot studio</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-edit-slot">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Tanggal *</label>
                        <input type="date" id="edit-slot-date" name="date" class="admin-form-input" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Jenis slot *</label>
                        <select id="edit-slot-type" name="type" class="admin-form-input" required>
                            <option value="available">Tersedia</option>
                            <option value="blocked">Blokir Studio</option>
                            <option value="booked">Terpesan</option>
                        </select>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu mulai *</label>
                        <input type="time" id="edit-slot-start" name="start_time" class="admin-form-input" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Waktu selesai *</label>
                        <input type="time" id="edit-slot-end" name="end_time" class="admin-form-input" required>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan</label>
                    <input type="text" id="edit-slot-note" name="note" class="admin-form-input">
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-edit-slot">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Simpan perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Terhubung ke Database SQLite · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bindModal = (btnId, modalId) => {
                const btn = document.getElementById(btnId);
                const modal = document.getElementById(modalId);
                if (btn && modal) {
                    btn.addEventListener('click', () => modal.classList.add('is-open'));
                }
            };

            bindModal('btn-open-add-slot', 'modal-add-slot');
            bindModal('btn-add-slot-for-date', 'modal-add-slot');
            bindModal('btn-quick-add-slot', 'modal-add-slot');

            // Handle edit buttons
            document.querySelectorAll('.btn-edit-slot').forEach(b => {
                b.addEventListener('click', () => {
                    const id = b.getAttribute('data-id');
                    const form = document.getElementById('form-edit-slot');
                    form.action = `/admin/jadwal/slot/${id}`;

                    document.getElementById('edit-slot-date').value = b.getAttribute('data-date');
                    document.getElementById('edit-slot-start').value = b.getAttribute('data-start');
                    document.getElementById('edit-slot-end').value = b.getAttribute('data-end');
                    document.getElementById('edit-slot-type').value = b.getAttribute('data-type');
                    document.getElementById('edit-slot-note').value = b.getAttribute('data-note') || '';

                    document.getElementById('modal-edit-slot').classList.add('is-open');
                });
            });

            // Interaktivitas Memilih Tanggal pada Kalender
            const dayCells = document.querySelectorAll('.cal-day-interactive');
            const sidebarTitle = document.getElementById('sidebar-slot-title');
            const sidebarCount = document.getElementById('sidebar-slot-count');
            const resetBtn = document.getElementById('btn-reset-calendar-filter');
            const dateBanner = document.getElementById('selected-date-banner');
            const dateText = document.getElementById('selected-date-text');
            const slotCards = document.querySelectorAll('.slot-item-card');
            const emptyFilterMsg = document.getElementById('slot-empty-filter-msg');
            const defaultEmpty = document.getElementById('slot-empty-default');
            const createSlotDate = document.getElementById('create-slot-date');

            const totalCountText = "● {{ $slots->count() }} Slot Tercatat";
            const defaultTitleText = "Daftar Slot {{ $monthDate->translatedFormat('F Y') }}";

            const resetFilter = () => {
                dayCells.forEach(c => c.classList.remove('is-selected'));
                slotCards.forEach(c => c.style.display = 'block');
                if (emptyFilterMsg) emptyFilterMsg.style.display = 'none';
                if (defaultEmpty) defaultEmpty.style.display = 'block';
                if (resetBtn) resetBtn.style.display = 'none';
                if (dateBanner) dateBanner.style.display = 'none';
                if (sidebarTitle) sidebarTitle.textContent = defaultTitleText;
                if (sidebarCount) sidebarCount.textContent = totalCountText;
                if (createSlotDate) createSlotDate.value = "{{ date('Y-m-d') }}";
            };

            if (resetBtn) {
                resetBtn.addEventListener('click', resetFilter);
            }

            dayCells.forEach(cell => {
                cell.addEventListener('click', () => {
                    const isAlreadySelected = cell.classList.contains('is-selected');

                    if (isAlreadySelected) {
                        resetFilter();
                        return;
                    }

                    dayCells.forEach(c => c.classList.remove('is-selected'));
                    cell.classList.add('is-selected');

                    const selectedDate = cell.getAttribute('data-date');
                    const selectedLabel = cell.getAttribute('data-label');

                    if (createSlotDate) createSlotDate.value = selectedDate;
                    if (dateText) dateText.textContent = selectedLabel;
                    if (dateBanner) dateBanner.style.display = 'block';
                    if (resetBtn) resetBtn.style.display = 'inline-block';
                    if (sidebarTitle) sidebarTitle.textContent = 'Jadwal: ' + selectedLabel;

                    let matchCount = 0;
                    slotCards.forEach(card => {
                        if (card.getAttribute('data-date') === selectedDate) {
                            card.style.display = 'block';
                            matchCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (defaultEmpty) defaultEmpty.style.display = 'none';

                    if (matchCount === 0) {
                        if (emptyFilterMsg) emptyFilterMsg.style.display = 'block';
                        if (sidebarCount) sidebarCount.textContent = '● 0 Slot Tercatat';
                    } else {
                        if (emptyFilterMsg) emptyFilterMsg.style.display = 'none';
                        if (sidebarCount) sidebarCount.textContent = `● ${matchCount} Slot pada Tanggal Ini`;
                    }
                });
            });

            document.querySelectorAll('[data-close]').forEach(b => {
                b.addEventListener('click', () => {
                    const id = b.getAttribute('data-close');
                    const m = document.getElementById(id);
                    if (m) m.classList.remove('is-open');
                });
            });
        });
    </script>
@endsection
