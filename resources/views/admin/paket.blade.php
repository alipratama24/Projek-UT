@extends('layouts.admin')

@section('title', 'Paket Layanan — naro capture')
@section('breadcrumb', 'Paket Layanan / Paket layanan')

@section('content')
    {{-- PENGANTAR HALAMAN --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Paket layanan</h1>
            <p class="admin-page-subtitle">{{ count($packages) }} paket terdaftar · Harga, cakupan sesi, dan informasi untuk pemesanan baru.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-add-package">
            + Tambah paket
        </button>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom:16px;">
            <strong class="admin-alert-title text-green">Berhasil</strong>
            <p class="admin-alert-desc text-green">{{ session('success') }}</p>
        </div>
    @endif

    {{-- FILTER & SEARCH --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.paket') }}" style="display:flex; gap:12px; width:100%; align-items:center;">
            <div class="admin-search-input-wrap" style="flex:1;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" placeholder="Cari paket layanan" class="admin-search-input" value="{{ request('search') }}">
            </div>
        </form>
    </div>

    {{-- KARTU PAKET LAYANAN --}}
    <div class="admin-packages-cards-grid">
        @php
            $fallbackImages = [
                'birthday' => 'paket-birthday.jpg',
                'family-session' => 'paket-family.jpg',
                'graduation' => 'paket-graduation.jpg',
                'prewedding' => 'paket-prewedding.jpg',
            ];
        @endphp
        @forelse ($packages as $pkg)
            @php
                $imgName = $fallbackImages[$pkg->slug] ?? ($pkg->image ?: 'paket-birthday.jpg');
                $priceFmt = 'Rp' . number_format($pkg->price, 0, ',', '.');
                $features = is_array($pkg->features) ? $pkg->features : (json_decode($pkg->features, true) ?? []);
                $featuresText = implode("\n", $features);
            @endphp
            <div class="admin-panel admin-package-mgmt-card" style="display:flex; flex-direction:column;">
                <img src="{{ asset('images/' . $imgName) }}" alt="{{ $pkg->name }}" class="admin-package-mgmt-img">
                <div class="admin-package-mgmt-body" style="flex:1; display:flex; flex-direction:column;">
                    <div class="admin-package-card-header">
                        <span class="status-indicator-item {{ $pkg->is_active ? 'text-green' : 'text-muted' }}">
                            ● {{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span class="order-code-mono text-muted">{{ $pkg->code }}</span>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <h2 class="admin-package-mgmt-title" style="margin:0;">{{ $pkg->name }}</h2>
                        @if ($pkg->is_featured)
                            <span class="badge" style="font-size:10px; padding:2px 8px;">TERPOPULER</span>
                        @endif
                    </div>
                    <p class="admin-package-mgmt-desc">
                        {{ $pkg->description }}
                    </p>
                    <strong class="admin-package-mgmt-price">{{ $priceFmt }}</strong>
                    <span class="admin-package-mgmt-meta">{{ $pkg->duration_minutes }} menit · {{ $pkg->photo_count }} foto · Per sesi</span>

                    <hr class="divider">

                    <ul class="features-checklist" style="flex:1;">
                        @foreach ($features as $f)
                            <li><span class="check-mark">✓</span> {{ $f }}</li>
                        @endforeach
                    </ul>

                    <hr class="divider">

                    <div style="display:flex; gap:8px; align-items:center;">
                        <button type="button" class="admin-btn-outline flex-1 btn-edit-package"
                            data-id="{{ $pkg->id }}"
                            data-name="{{ $pkg->name }}"
                            data-price="{{ $pkg->price }}"
                            data-duration="{{ $pkg->duration_minutes }}"
                            data-photos="{{ $pkg->photo_count }}"
                            data-desc="{{ $pkg->description }}"
                            data-features="{{ $featuresText }}">
                            Edit
                        </button>
                        
                        <form action="{{ route('admin.paket.toggle', ['id' => $pkg->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="admin-btn-outline" style="font-size:12px; padding:8px 12px;" title="{{ $pkg->is_active ? 'Nonaktifkan Paket' : 'Aktifkan Paket' }}">
                                {{ $pkg->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.paket.destroy', ['id' => $pkg->id]) }}" method="POST" onsubmit="return confirm('Hapus paket {{ $pkg->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn-outline btn-outline-danger" style="font-size:12px; padding:8px 12px;" title="Hapus Paket">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada paket layanan terdaftar di database.</p>
        @endforelse
    </div>

    {{-- MODAL TAMBAH PAKET --}}
    <div class="admin-modal-overlay" id="modal-add-package">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.paket.store') }}" method="POST">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Tambah paket layanan</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-add-package">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">Buat paket layanan baru untuk tampil di katalog beranda & formulir pemesanan</p>
                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Nama paket *</label>
                        <input type="text" name="name" class="admin-form-input" placeholder="Contoh: Maternity Session" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Harga (Rp) *</label>
                        <input type="number" name="price" class="admin-form-input" placeholder="2500000" required>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Durasi (menit) *</label>
                        <input type="number" name="duration_minutes" class="admin-form-input" value="60" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Jumlah foto diedit *</label>
                        <input type="number" name="photo_count" class="admin-form-input" value="15" required>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Deskripsi singkat *</label>
                    <textarea name="description" class="admin-form-input" rows="2" placeholder="Deskripsi paket..." required></textarea>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Fitur / Layanan yang didapat (1 baris per fitur)</label>
                    <textarea name="features" class="admin-form-input" rows="4" placeholder="Properti studio terkurasi&#10;15 foto retouched resolusi tinggi&#10;Akses galeri cloud pribadi"></textarea>
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-add-package">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Simpan paket
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT PAKET --}}
    <div class="admin-modal-overlay" id="modal-edit-package">
        <div class="admin-modal-dialog">
            <form id="form-edit-package" method="POST">
                @csrf
                @method('PUT')
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Edit paket layanan</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-edit-package">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Nama paket *</label>
                        <input type="text" id="edit-pkg-name" name="name" class="admin-form-input" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Harga (Rp) *</label>
                        <input type="number" id="edit-pkg-price" name="price" class="admin-form-input" required>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Durasi (menit) *</label>
                        <input type="number" id="edit-pkg-duration" name="duration_minutes" class="admin-form-input" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Jumlah foto diedit *</label>
                        <input type="number" id="edit-pkg-photos" name="photo_count" class="admin-form-input" required>
                    </div>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Deskripsi singkat *</label>
                    <textarea id="edit-pkg-desc" name="description" class="admin-form-input" rows="2" required></textarea>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Fitur (1 baris per fitur)</label>
                    <textarea id="edit-pkg-features" name="features" class="admin-form-input" rows="4"></textarea>
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-edit-package">Batal</button>
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
            const btn = document.getElementById('btn-open-add-package');
            const modalAdd = document.getElementById('modal-add-package');
            if (btn && modalAdd) {
                btn.addEventListener('click', () => modalAdd.classList.add('is-open'));
            }

            // Edit package handler
            document.querySelectorAll('.btn-edit-package').forEach(b => {
                b.addEventListener('click', () => {
                    const id = b.getAttribute('data-id');
                    const form = document.getElementById('form-edit-package');
                    form.action = `/admin/paket/${id}`;

                    document.getElementById('edit-pkg-name').value = b.getAttribute('data-name');
                    document.getElementById('edit-pkg-price').value = b.getAttribute('data-price');
                    document.getElementById('edit-pkg-duration').value = b.getAttribute('data-duration');
                    document.getElementById('edit-pkg-photos').value = b.getAttribute('data-photos');
                    document.getElementById('edit-pkg-desc').value = b.getAttribute('data-desc');
                    document.getElementById('edit-pkg-features').value = b.getAttribute('data-features');

                    document.getElementById('modal-edit-package').classList.add('is-open');
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
