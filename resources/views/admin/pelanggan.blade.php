@extends('layouts.admin')

@section('title', 'Pelanggan — naro capture')
@section('breadcrumb', 'Pelanggan / Pelanggan')

@section('content')
    {{-- PENGANTAR HALAMAN (FIGMA 141:3090) --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Pelanggan</h1>
            <p class="admin-page-subtitle">Kontak, riwayat sesi, dan catatan pribadi untuk layanan yang lebih personal.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-add-customer">
            Tambah pelanggan
        </button>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box">
            <strong class="admin-alert-title text-green">Berhasil</strong>
            <p class="admin-alert-desc text-green">
                {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- PANEL UTAMA TABEL PELANGGAN (FIGMA 141:3104) --}}
    <div class="admin-panel admin-orders-panel">
        {{-- TABS (FIGMA 141:3105) --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.pelanggan', ['tab' => 'aktif']) }}" class="admin-tab-item {{ $activeTab === 'aktif' ? 'is-active' : '' }}">
                Aktif (18)
            </a>
            <a href="{{ route('admin.pelanggan', ['tab' => 'diarsipkan']) }}" class="admin-tab-item {{ $activeTab === 'diarsipkan' ? 'is-active' : '' }}">
                Diarsipkan (4)
            </a>
            <a href="{{ route('admin.pelanggan', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua
            </a>
        </div>

        {{-- FILTER & SEARCH (FIGMA 141:3112) --}}
        <div class="admin-filter-bar">
            <div class="admin-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari nama, email, atau WhatsApp" class="admin-search-input" value="{{ request('search') }}">
            </div>

            <div class="admin-select-dropdown">
                <span>Semua riwayat</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>

            <div class="admin-select-dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Semua periode</span>
            </div>
        </div>

        {{-- TABEL RECORD PELANGGAN (FIGMA 141:3122) --}}
        <div class="admin-table-container">
            <table class="admin-orders-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Pelanggan / ID</th>
                        <th style="width: 25%;">Email ilustrasi</th>
                        <th style="width: 20%;">WhatsApp</th>
                        <th style="width: 15%;">Riwayat</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 7%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $c)
                        <tr>
                            <td>
                                <div class="table-cell-order">
                                    <strong class="order-customer-name">{{ $c['name'] }}</strong>
                                    <span class="order-code-mono text-muted">{{ $c['id'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="package-name-text">{{ $c['email'] }}</span>
                            </td>
                            <td>
                                <span class="order-code-mono">{{ $c['whatsapp'] }}</span>
                            </td>
                            <td>
                                <span class="schedule-date-text">{{ $c['history'] }}</span>
                            </td>
                            <td>
                                <span class="badge-status-payment status-terverifikasi">
                                    {{ $c['status'] }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.pesanan.detail', ['id' => 'CH-CONTOH-001']) }}" class="admin-table-action-link">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINASI (FIGMA 141:3139) --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ count($customers) }} dari 18 pelanggan aktif</span>
            <div class="pagination-controls">
                <button type="button" class="btn-page-nav is-disabled" disabled>Sebelumnya</button>
                <div class="pagination-numbers">
                    <span class="page-num is-active">● 1</span>
                    <span class="page-num">2</span>
                    <span class="page-num">3</span>
                </div>
                <button type="button" class="btn-page-nav">Berikutnya</button>
            </div>
        </div>

        <p class="admin-table-footer-hint">
            Semua kontak dalam desain ini bersifat ilustratif. Batasi akses hanya untuk kebutuhan pelayanan studio.
        </p>
    </div>

    {{-- MODAL DIALOG TAMBAH PELANGGAN (FIGMA 141:3288) --}}
    <div class="admin-modal-overlay" id="modal-add-customer">
        <div class="admin-modal-dialog">
            <form id="form-add-customer">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Tambah pelanggan</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-add-customer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">Rekam kontak pemesan untuk persiapan sesi studio via API endpoint</p>

                <hr class="divider">

                <div id="modal-api-alert" style="display:none; margin-bottom:16px;" class="admin-alert-box">
                    <strong class="admin-alert-title" id="modal-api-alert-title"></strong>
                    <p class="admin-alert-desc" id="modal-api-alert-desc"></p>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Nama lengkap *</label>
                        <input type="text" id="cust-name" name="name" class="admin-form-input" placeholder="Nama pemesan" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">ID Pelanggan (otomatis via API)</label>
                        <input type="text" id="cust-code" class="admin-form-input font-mono" value="Auto-generated by API" readonly>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Email *</label>
                        <input type="email" id="cust-email" name="email" class="admin-form-input" placeholder="email@example.com" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">WhatsApp *</label>
                        <input type="tel" id="cust-whatsapp" name="whatsapp" class="admin-form-input" placeholder="+62 8..." required>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Catatan preferensi gaya / keluarga</label>
                    <textarea id="cust-notes" name="notes" class="admin-form-input" rows="3" placeholder="Contoh: Menyukai nuansa outdoor di pagi hari"></textarea>
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-add-customer">Batal</button>
                    <button type="submit" class="admin-btn-primary" id="btn-submit-customer">
                        Simpan pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FOOTER COPYRIGHT (FIGMA 141:3153) --}}
    <div class="admin-footer-copyright">
        © 2026 naro capture · Data ilustrasi · Akses terbatas admin
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-open-add-customer');
            const modal = document.getElementById('modal-add-customer');
            const form = document.getElementById('form-add-customer');
            const submitBtn = document.getElementById('btn-submit-customer');
            const alertBox = document.getElementById('modal-api-alert');
            const alertTitle = document.getElementById('modal-api-alert-title');
            const alertDesc = document.getElementById('modal-api-alert-desc');

            if (btn && modal) {
                btn.addEventListener('click', () => {
                    alertBox.style.display = 'none';
                    form.reset();
                    modal.classList.add('is-open');
                });
            }

            document.querySelectorAll('[data-close]').forEach(b => {
                b.addEventListener('click', () => {
                    const id = b.getAttribute('data-close');
                    const m = document.getElementById(id);
                    if (m) m.classList.remove('is-open');
                });
            });

            // HIT API POST /api/v1/pelanggan via Fetch API
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const payload = {
                        name: document.getElementById('cust-name').value.trim(),
                        email: document.getElementById('cust-email').value.trim(),
                        whatsapp: document.getElementById('cust-whatsapp').value.trim(),
                        notes: document.getElementById('cust-notes').value.trim(),
                    };

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan ke API...';

                    try {
                        const response = await fetch('/api/v1/pelanggan', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            alertBox.className = 'admin-alert-box alert-success-box';
                            alertTitle.className = 'admin-alert-title text-green';
                            alertTitle.textContent = 'Berhasil tersimpan ke API (201 Created)';
                            alertDesc.className = 'admin-alert-desc text-green';
                            alertDesc.textContent = result.message + ' [' + result.data.id + ']';
                            alertBox.style.display = 'block';

                            // Masukkan baris baru secara dinamis ke tabel langsung di browser
                            const tbody = document.querySelector('.admin-table tbody');
                            if (tbody) {
                                const newRow = document.createElement('tr');
                                newRow.innerHTML = `
                                    <td>
                                        <div class="table-cell-order">
                                            <strong class="order-customer-name">${result.data.name}</strong>
                                            <span class="order-code-mono text-muted">${result.data.id}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="package-name-text">${result.data.email}</span>
                                    </td>
                                    <td>
                                        <span class="order-code-mono">${result.data.whatsapp}</span>
                                    </td>
                                    <td>
                                        <span class="schedule-date-text">0 pesanan</span>
                                    </td>
                                    <td>
                                        <span class="badge-status-payment status-terverifikasi">
                                            Aktif
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="text-green font-11 font-mono">Tersimpan via API ✓</span>
                                    </td>
                                `;
                                tbody.insertBefore(newRow, tbody.firstChild);
                            }

                            setTimeout(() => {
                                modal.classList.remove('is-open');
                                form.reset();
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Simpan pelanggan';
                            }, 1000);

                        } else {
                            let errorMsg = result.message || 'Gagal menyimpan ke API.';
                            if (result.errors) {
                                errorMsg = Object.values(result.errors).flat().join(', ');
                            }
                            alertBox.className = 'admin-alert-box alert-danger-box';
                            alertTitle.className = 'admin-alert-title text-red';
                            alertTitle.textContent = 'Gagal Validasi API';
                            alertDesc.className = 'admin-alert-desc text-red';
                            alertDesc.textContent = errorMsg;
                            alertBox.style.display = 'block';

                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Simpan pelanggan';
                        }
                    } catch (err) {
                        alertBox.className = 'admin-alert-box alert-danger-box';
                        alertTitle.className = 'admin-alert-title text-red';
                        alertTitle.textContent = 'Network Error';
                        alertDesc.className = 'admin-alert-desc text-red';
                        alertDesc.textContent = err.message || 'Gagal menghubungi endpoint API.';
                        alertBox.style.display = 'block';

                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Simpan pelanggan';
                    }
                });
            }
        });
    </script>
@endsection

