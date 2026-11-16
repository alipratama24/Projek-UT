@extends('layouts.admin')

@section('title', 'Galeri Pelanggan — naro capture')
@section('breadcrumb', 'Galeri / Galeri pelanggan')

@section('content')
    {{-- PENGANTAR HALAMAN --}}
    <div class="admin-page-header">
        <div class="admin-header-text">
            <h1 class="admin-page-title">Galeri pelanggan</h1>
            <p class="admin-page-subtitle">Kelola hasil sesi dan akses pribadi. Terhubung langsung ke database galeri foto.</p>
        </div>
        <button type="button" class="admin-btn-primary" id="btn-open-create-gallery">
            + Buat galeri
        </button>
    </div>

    {{-- FEEDBACK NOTICE --}}
    @if(session('success'))
        <div class="admin-alert-box alert-success-box" style="margin-bottom:16px;">
            <strong class="admin-alert-title text-green">Berhasil</strong>
            <p class="admin-alert-desc text-green">{{ session('success') }}</p>
        </div>
    @endif

    {{-- BARIS KONTEN STATUS --}}
    <div class="admin-status-summary-bar">
        <span class="status-indicator-item text-blue">● {{ $penyuntinganCount }} dalam penyuntingan</span>
        <span class="status-indicator-item text-muted">● {{ $drafCount }} draf kosong</span>
        <span class="status-indicator-item text-green">● {{ $siapCount }} siap diserahkan</span>
        <span class="status-indicator-item text-green">● {{ $diterbitkanCount }} diterbitkan</span>
    </div>

    {{-- PANEL UTAMA TABEL GALERI --}}
    <div class="admin-panel admin-orders-panel">
        {{-- TABS --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.galeri', ['tab' => 'semua']) }}" class="admin-tab-item {{ $activeTab === 'semua' ? 'is-active' : '' }}">
                Semua galeri ({{ $galleries->count() }})
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'draf']) }}" class="admin-tab-item {{ $activeTab === 'draf' ? 'is-active' : '' }}">
                Draf ({{ $drafCount }})
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'penyuntingan']) }}" class="admin-tab-item {{ $activeTab === 'penyuntingan' ? 'is-active' : '' }}">
                Penyuntingan ({{ $penyuntinganCount }})
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'siap']) }}" class="admin-tab-item {{ $activeTab === 'siap' ? 'is-active' : '' }}">
                Siap ({{ $siapCount }})
            </a>
            <a href="{{ route('admin.galeri', ['tab' => 'diterbitkan']) }}" class="admin-tab-item {{ $activeTab === 'diterbitkan' ? 'is-active' : '' }}">
                Diterbitkan ({{ $diterbitkanCount }})
            </a>
        </div>

        {{-- FILTER & SEARCH --}}
        <div class="admin-filter-bar">
            <form method="GET" action="{{ route('admin.galeri') }}" style="display:flex; gap:12px; align-items:center; width:100%;">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="admin-search-input-wrap" style="flex:1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E7A8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" placeholder="Cari judul, pelanggan, atau pesanan" class="admin-search-input" value="{{ request('search') }}">
                </div>
            </form>
        </div>

        {{-- TABEL RECORD GALERI --}}
        <div class="admin-table-container">
            <table class="admin-orders-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Galeri / Judul</th>
                        <th style="width: 20%; white-space: nowrap;">Pesanan / Klien</th>
                        <th style="width: 15%; white-space: nowrap;">Foto / Target</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 11%;">Akses</th>
                        <th style="width: 20%; min-width: 190px; text-align: right; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($galleries as $g)
                        <tr>
                            <td>
                                <strong class="order-customer-name">{{ $g->title }}</strong>
                                <span class="order-code-mono text-muted" style="display:block; font-size:11px;">/galeri-klien?slug={{ $g->slug }}</span>
                            </td>
                            <td>
                                <div class="table-cell-order">
                                    <span class="order-code-mono">{{ $g->order ? $g->order->order_code : 'Umum' }}</span>
                                    <span class="schedule-time-text">{{ $g->client_name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="schedule-date-text">
                                    <strong>{{ $g->photos->count() }}</strong> / {{ $g->target_photos }} foto
                                </span>
                            </td>
                            <td>
                                @if($g->status === 'Diterbitkan')
                                    <span class="badge-status-payment status-terverifikasi">● Diterbitkan</span>
                                @elseif($g->status === 'Siap')
                                    <span class="badge-status-payment status-terverifikasi" style="background:#E2F0D9; color:#385723;">● Siap</span>
                                @elseif($g->status === 'Penyuntingan')
                                    <span class="badge-status-payment status-bukti-terkirim">● Penyuntingan</span>
                                @else
                                    <span class="badge-status-payment" style="background:#F0F1EE; color:#576478;">● Draf</span>
                                @endif
                            </td>
                            <td>
                                <span class="schedule-time-text">
                                    {{ $g->access_type }} {{ $g->pin_code ? '(PIN: ' . $g->pin_code . ')' : '' }}
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                @php
                                    $photoList = $g->photos->map(function ($p, $pIdx) {
                                        $src = $p->photo_path;
                                        if (!\Illuminate\Support\Str::startsWith($src, ['http://', 'https://'])) {
                                            if (file_exists(public_path('images/' . $src))) {
                                                $src = asset('images/' . $src);
                                            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($src) || file_exists(public_path('storage/' . $src))) {
                                                $src = asset('storage/' . $src);
                                            } else {
                                                $src = asset('images/work-' . (($pIdx % 6) + 1) . '.jpg');
                                            }
                                        }
                                        return [
                                            'id' => $p->id,
                                            'url' => $src,
                                            'caption' => $p->caption,
                                        ];
                                    });
                                @endphp
                                <div style="display:inline-flex; gap:8px; align-items:center; justify-content: flex-end; white-space: nowrap;">
                                    <button type="button" class="admin-btn-outline btn-sm btn-edit-gallery"
                                        data-id="{{ $g->id }}"
                                        data-title="{{ $g->title }}"
                                        data-order-id="{{ $g->order_id }}"
                                        data-status="{{ $g->status }}"
                                        data-target="{{ $g->target_photos }}"
                                        data-access="{{ $g->access_type }}"
                                        data-pin="{{ $g->pin_code }}"
                                        data-photos='@json($photoList)'
                                        style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 11px; font-weight: 500; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; border-radius: 4px; white-space: nowrap; line-height: 1.2;"
                                        title="Ubah data galeri, ganti PIN, atau unggah foto tambahan">
                                        Kelola &amp; Foto ✎
                                    </button>
                                    @if($g->status !== 'Diterbitkan')
                                        <form action="{{ route('admin.galeri.status', ['id' => $g->id]) }}" method="POST" style="display:inline; margin:0;">
                                            @csrf
                                            <input type="hidden" name="status" value="Diterbitkan">
                                            <button type="submit" class="admin-btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px; font-weight: 600; color: #059669; border-color: #A7F3D0; background: #ECFDF5; border-radius: 4px; white-space: nowrap; line-height: 1.2;" title="Terbitkan galeri sekarang agar klien bisa melihat &amp; mengunduh foto">
                                                Terbitkan ✓
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('galeri-klien', ['slug' => $g->slug]) }}" target="_blank" class="admin-table-action-link" style="white-space: nowrap; font-size: 12px;" title="Pratinjau Galeri Publik">
                                        Lihat ↗
                                    </a>
                                    <form action="{{ route('admin.galeri.destroy', ['id' => $g->id]) }}" method="POST" onsubmit="return confirm('Hapus galeri ini?')" style="display:inline; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:none; border:none; color:#DC2626; cursor:pointer; font-size:13px; padding:2px 4px; line-height:1; vertical-align:middle;" title="Hapus galeri">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 24px;" class="text-muted">
                                Tidak ada galeri foto yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINASI --}}
        <div class="admin-pagination-bar">
            <span class="pagination-info">Menampilkan {{ $galleries->count() }} galeri dari database</span>
        </div>
    </div>

    {{-- MODAL DIALOG BUAT GALERI --}}
    <div class="admin-modal-overlay" id="modal-create-gallery">
        <div class="admin-modal-dialog">
            <form action="{{ route('admin.galeri.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Buat galeri pelanggan</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-create-gallery">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">Inisiasi ruang foto online pribadi untuk pesanan yang telah dikonfirmasi</p>
                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Judul galeri *</label>
                        <input type="text" name="title" class="admin-form-input" placeholder="Contoh: Potret Keluarga Ayu" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Pesanan terkait</label>
                        <select name="order_id" class="admin-form-input">
                            <option value="">-- Tanpa Pesanan (Koleksi Umum) --</option>
                            @foreach($orders as $ord)
                                <option value="{{ $ord->id }}">
                                    {{ $ord->order_code }} · {{ $ord->customer_name }} ({{ $ord->package_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Target jumlah foto</label>
                        <input type="number" name="target_photos" class="admin-form-input" value="20" min="1" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Tipe akses *</label>
                        <select name="access_type" id="create-access-type" class="admin-form-input" required>
                            <option value="Privat" selected>Privat (PIN / Tautan Terproteksi)</option>
                            <option value="Terbuka">Terbuka (Portofolio Publik)</option>
                        </select>
                    </div>
                </div>

                <div class="admin-meta-field" id="wrap-create-pin">
                    <label class="admin-meta-label">Kode PIN Privat (Custom)</label>
                    <input type="text" name="pin_code" id="create-pin-code" class="admin-form-input" value="" maxlength="10" placeholder="4–8 digit angka, contoh: 7291">
                    <span style="font-size:11px; color:#6E7A8B; display:block; margin-top:2px;">Klien memasukkan PIN ini untuk membuka galeri pribadi mereka.</span>
                </div>

                <div class="admin-meta-field">
                    <label class="admin-meta-label">Unggah foto batch awal (opsional)</label>
                    <input type="file" id="create-gallery-photos-input" name="photos[]" class="admin-form-input" multiple accept="image/*">
                    <span style="font-size:11px; color:#6E7A8B; display:block; margin-top:3px;">Format JPG, PNG, atau WEBP. Maks 10 MB per foto.</span>
                </div>

                {{-- PRATINJAU FOTO BARU SAAT BUAT --}}
                <div class="admin-meta-field" id="wrap-create-photos-preview" style="display:none; margin-top:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <label class="admin-meta-label" style="color: #2563EB; margin:0;">
                            <strong>Pratinjau Foto Dipilih (<span id="count-create-photos">0</span> foto):</strong>
                        </label>
                        <button type="button" id="btn-cancel-all-create-photos" style="background:none; border:none; color:#DC2626; font-size:11px; cursor:pointer; font-weight:500; text-decoration:underline;">
                            Batal Semua Foto ✕
                        </button>
                    </div>
                    <div id="create-photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 6px; max-height: 180px; overflow-y: auto; padding: 8px; background: #EFF6FF; border: 1px dashed #93C5FD; border-radius: 6px;"></div>
                </div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-create-gallery">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Buat galeri draf
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DIALOG EDIT GALERI & KELOLA FOTO --}}
    <div class="admin-modal-overlay" id="modal-edit-gallery">
        <div class="admin-modal-dialog" style="max-width: 680px; max-height: 90vh; overflow-y: auto;">
            <form id="form-edit-gallery" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">Kelola Galeri &amp; Unggah Foto</h3>
                    <button type="button" class="admin-btn-close-modal" data-close="modal-edit-gallery">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#253247" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <p class="admin-modal-subtitle">Ubah status, kustomisasi PIN akses privat, dan tambahkan foto ke galeri ini</p>
                <hr class="divider">

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Judul galeri *</label>
                        <input type="text" id="edit-gallery-title" name="title" class="admin-form-input" required>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Status galeri *</label>
                        <select id="edit-gallery-status" name="status" class="admin-form-input" required>
                            <option value="Draf">Draf</option>
                            <option value="Penyuntingan">Penyuntingan</option>
                            <option value="Siap">Siap</option>
                            <option value="Diterbitkan">Diterbitkan</option>
                        </select>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Pesanan terkait</label>
                        <select id="edit-gallery-order" name="order_id" class="admin-form-input">
                            <option value="">-- Tanpa Pesanan (Koleksi Umum) --</option>
                            @foreach($orders as $ord)
                                <option value="{{ $ord->id }}">
                                    {{ $ord->order_code }} · {{ $ord->customer_name }} ({{ $ord->package_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Target jumlah foto</label>
                        <input type="number" id="edit-gallery-target" name="target_photos" class="admin-form-input" min="1" required>
                    </div>
                </div>

                <div class="admin-meta-row-2">
                    <div class="admin-meta-field">
                        <label class="admin-meta-label">Tipe akses *</label>
                        <select id="edit-gallery-access" name="access_type" class="admin-form-input" required>
                            <option value="Privat">Privat (PIN / Tautan Terproteksi)</option>
                            <option value="Terbuka">Terbuka (Portofolio Publik)</option>
                        </select>
                    </div>
                    <div class="admin-meta-field" id="wrap-edit-pin">
                        <label class="admin-meta-label">Kode PIN Privat (Custom)</label>
                        <input type="text" id="edit-gallery-pin" name="pin_code" class="admin-form-input" maxlength="10" placeholder="4–8 digit angka">
                        <span style="font-size:11px; color:#6E7A8B; display:block; margin-top:2px;">Ubah PIN untuk keamanan akses klien.</span>
                    </div>
                </div>

                <hr class="divider">

                {{-- AREA UNGGAH FOTO BARU --}}
                <div class="admin-meta-field">
                    <label class="admin-meta-label"><strong>+ Tambah Foto Baru ke Galeri</strong> (Bisa pilih banyak sekaligus)</label>
                    <input type="file" id="edit-gallery-photos-input" name="photos[]" class="admin-form-input" multiple accept="image/*">
                    <span style="font-size:11px; color:#6E7A8B; display:block; margin-top:3px;">Format JPG, PNG, atau WEBP. Maks 10 MB per foto. Foto baru yang dipilih akan langsung muncul di pratinjau.</span>
                </div>

                {{-- PRATINJAU FOTO BARU YANG DIPILIH --}}
                <div class="admin-meta-field" id="wrap-new-photos-preview" style="display:none; margin-top:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <label class="admin-meta-label" style="color: #2563EB; margin:0;">
                            <strong>Pratinjau Foto Baru yang Ditambahkan (<span id="count-new-photos">0</span> foto):</strong>
                        </label>
                        <button type="button" id="btn-cancel-all-new-photos" style="background:none; border:none; color:#DC2626; font-size:11px; cursor:pointer; font-weight:500; text-decoration:underline;">
                            Batal Semua Foto Baru ✕
                        </button>
                    </div>
                    <div id="new-photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 6px; max-height: 180px; overflow-y: auto; padding: 8px; background: #EFF6FF; border: 1px dashed #93C5FD; border-radius: 6px;"></div>
                </div>

                {{-- AREA DAFTAR FOTO YANG SUDAH ADA --}}
                <div class="admin-meta-field" style="margin-top: 16px;">
                    <label class="admin-meta-label"><strong>Foto dalam Galeri Ini (<span id="count-existing-photos">0</span> foto):</strong></label>
                    <div id="existing-photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 8px; max-height: 220px; overflow-y: auto; padding: 8px; background: #F8FAFD; border: 1px solid #E1E7F0; border-radius: 6px;">
                        {{-- Diisi secara dinamis oleh JavaScript --}}
                    </div>
                </div>

                {{-- CONTAINER UNTUK MENAMPUNG ID FOTO YANG DITANDAI DIHAPUS (DIHAPUS SAAT SIMPAN) --}}
                <div id="deleted-photos-container"></div>

                <hr class="divider">

                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn-outline" data-close="modal-edit-gallery">Batal</button>
                    <button type="submit" class="admin-btn-primary">
                        Simpan Perubahan &amp; Foto
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
            const btn = document.getElementById('btn-open-create-gallery');
            const modalCreate = document.getElementById('modal-create-gallery');
            if (btn && modalCreate) {
                btn.addEventListener('click', () => {
                    createNewFiles = [];
                    if (createPhotosInput) createPhotosInput.value = '';
                    if (wrapCreatePreview) wrapCreatePreview.style.display = 'none';
                    if (createPhotosGrid) createPhotosGrid.innerHTML = '';
                    modalCreate.classList.add('is-open');
                });
            }

            // Toggle PIN di Modal Buat
            const createAccessType = document.getElementById('create-access-type');
            const wrapCreatePin = document.getElementById('wrap-create-pin');
            if (createAccessType && wrapCreatePin) {
                createAccessType.addEventListener('change', () => {
                    wrapCreatePin.style.display = (createAccessType.value === 'Privat') ? 'block' : 'none';
                });
            }

            // State Foto Baru di Modal Buat Galeri
            const createPhotosInput = document.getElementById('create-gallery-photos-input');
            const wrapCreatePreview = document.getElementById('wrap-create-photos-preview');
            const createPhotosGrid = document.getElementById('create-photos-grid');
            const countCreateEl = document.getElementById('count-create-photos');
            const btnCancelAllCreate = document.getElementById('btn-cancel-all-create-photos');
            let createNewFiles = [];

            const syncCreateFileInput = () => {
                if (window.DataTransfer) {
                    const dt = new DataTransfer();
                    createNewFiles.forEach(f => dt.items.add(f));
                    createPhotosInput.files = dt.files;
                }
            };

            const renderCreateNewPhotos = () => {
                createPhotosGrid.innerHTML = '';
                if (createNewFiles.length === 0) {
                    wrapCreatePreview.style.display = 'none';
                    countCreateEl.textContent = '0';
                    createPhotosInput.value = '';
                    return;
                }

                wrapCreatePreview.style.display = 'block';
                countCreateEl.textContent = createNewFiles.length;

                createNewFiles.forEach((file, index) => {
                    const card = document.createElement('div');
                    card.style.cssText = 'position:relative; border-radius:4px; overflow:hidden; border:1px solid #93C5FD; background:#000;';

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        card.innerHTML = `
                            <img src="${e.target.result}" style="width:100%; height:80px; object-fit:cover; display:block;">
                            <span style="position:absolute; bottom:2px; left:2px; background:rgba(37,99,235,0.85); color:#fff; font-size:9px; padding:1px 4px; border-radius:2px;">Baru</span>
                            <button type="button" class="btn-remove-new-photo" data-idx="${index}" style="position:absolute; top:3px; right:3px; width:20px; height:20px; border-radius:50%; background:rgba(220,38,38,0.92); color:#fff; border:none; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0; line-height:1; box-shadow:0 1px 3px rgba(0,0,0,0.3);" title="Batalkan/hapus foto ini">✕</button>
                        `;

                        const btn = card.querySelector('.btn-remove-new-photo');
                        if (btn) {
                            btn.addEventListener('click', (ev) => {
                                ev.preventDefault();
                                ev.stopPropagation();
                                createNewFiles.splice(index, 1);
                                syncCreateFileInput();
                                renderCreateNewPhotos();
                            });
                        }
                    };
                    reader.readAsDataURL(file);
                    createPhotosGrid.appendChild(card);
                });
            };

            if (createPhotosInput) {
                createPhotosInput.addEventListener('change', function() {
                    if (!this.files || this.files.length === 0) return;
                    createNewFiles = Array.from(this.files);
                    syncCreateFileInput();
                    renderCreateNewPhotos();
                });
            }

            if (btnCancelAllCreate) {
                btnCancelAllCreate.addEventListener('click', () => {
                    createNewFiles = [];
                    syncCreateFileInput();
                    renderCreateNewPhotos();
                });
            }

            // Toggle PIN di Modal Edit
            const editAccessType = document.getElementById('edit-gallery-access');
            const wrapEditPin = document.getElementById('wrap-edit-pin');
            if (editAccessType && wrapEditPin) {
                editAccessType.addEventListener('change', () => {
                    wrapEditPin.style.display = (editAccessType.value === 'Privat') ? 'block' : 'none';
                });
            }

            // Variabel state foto modal edit
            const modalEdit = document.getElementById('modal-edit-gallery');
            const formEdit = document.getElementById('form-edit-gallery');
            const existingPhotosGrid = document.getElementById('existing-photos-grid');
            const countExistingPhotosEl = document.getElementById('count-existing-photos');
            const deletedContainer = document.getElementById('deleted-photos-container');

            const editPhotosInput = document.getElementById('edit-gallery-photos-input');
            const wrapNewPreview = document.getElementById('wrap-new-photos-preview');
            const newPhotosGrid = document.getElementById('new-photos-grid');
            const countNewPhotosEl = document.getElementById('count-new-photos');
            const btnCancelAllNew = document.getElementById('btn-cancel-all-new-photos');

            let activeExistingPhotos = [];
            let editNewFiles = [];

            const syncEditFileInput = () => {
                if (window.DataTransfer) {
                    const dt = new DataTransfer();
                    editNewFiles.forEach(f => dt.items.add(f));
                    editPhotosInput.files = dt.files;
                }
            };

            const renderEditNewPhotos = () => {
                newPhotosGrid.innerHTML = '';
                if (editNewFiles.length === 0) {
                    wrapNewPreview.style.display = 'none';
                    countNewPhotosEl.textContent = '0';
                    editPhotosInput.value = '';
                    return;
                }

                wrapNewPreview.style.display = 'block';
                countNewPhotosEl.textContent = editNewFiles.length;

                editNewFiles.forEach((file, index) => {
                    const card = document.createElement('div');
                    card.style.cssText = 'position:relative; border-radius:4px; overflow:hidden; border:1px solid #93C5FD; background:#000;';

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        card.innerHTML = `
                            <img src="${e.target.result}" style="width:100%; height:80px; object-fit:cover; display:block;">
                            <span style="position:absolute; bottom:2px; left:2px; background:rgba(37,99,235,0.85); color:#fff; font-size:9px; padding:1px 4px; border-radius:2px;">Baru</span>
                            <button type="button" class="btn-remove-new-photo" data-idx="${index}" style="position:absolute; top:3px; right:3px; width:20px; height:20px; border-radius:50%; background:rgba(220,38,38,0.92); color:#fff; border:none; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0; line-height:1; box-shadow:0 1px 3px rgba(0,0,0,0.3);" title="Batalkan/hapus foto ini dari daftar unggah">✕</button>
                        `;

                        const btn = card.querySelector('.btn-remove-new-photo');
                        if (btn) {
                            btn.addEventListener('click', (ev) => {
                                ev.preventDefault();
                                ev.stopPropagation();
                                editNewFiles.splice(index, 1);
                                syncEditFileInput();
                                renderEditNewPhotos();
                            });
                        }
                    };
                    reader.readAsDataURL(file);
                    newPhotosGrid.appendChild(card);
                });
            };

            // Live Preview Foto Baru yang Dipilih pada Modal Edit
            if (editPhotosInput) {
                editPhotosInput.addEventListener('change', function() {
                    if (!this.files || this.files.length === 0) return;
                    editNewFiles = Array.from(this.files);
                    syncEditFileInput();
                    renderEditNewPhotos();
                });
            }

            if (btnCancelAllNew) {
                btnCancelAllNew.addEventListener('click', () => {
                    editNewFiles = [];
                    syncEditFileInput();
                    renderEditNewPhotos();
                });
            }

            // Buka Modal Edit Galeri & Kelola Foto
            document.querySelectorAll('.btn-edit-gallery').forEach(b => {
                b.addEventListener('click', () => {
                    const id = b.getAttribute('data-id');
                    formEdit.action = `/admin/galeri/${id}`;

                    // Reset form & container foto yang ditandai hapus
                    deletedContainer.innerHTML = '';
                    editNewFiles = [];
                    if (editPhotosInput) editPhotosInput.value = '';
                    wrapNewPreview.style.display = 'none';
                    newPhotosGrid.innerHTML = '';

                    document.getElementById('edit-gallery-title').value = b.getAttribute('data-title');
                    document.getElementById('edit-gallery-status').value = b.getAttribute('data-status');
                    document.getElementById('edit-gallery-order').value = b.getAttribute('data-order-id') || '';
                    document.getElementById('edit-gallery-target').value = b.getAttribute('data-target') || '20';
                    
                    const access = b.getAttribute('data-access') || 'Privat';
                    document.getElementById('edit-gallery-access').value = access;
                    document.getElementById('edit-gallery-pin').value = b.getAttribute('data-pin') || '';
                    if (wrapEditPin) {
                        wrapEditPin.style.display = (access === 'Privat') ? 'block' : 'none';
                    }

                    // Render Foto-foto yang sudah ada
                    try {
                        activeExistingPhotos = JSON.parse(b.getAttribute('data-photos') || '[]');
                        renderExistingPhotos();
                    } catch(err) {
                        existingPhotosGrid.innerHTML = '<span style="font-size:12px; color:#6E7A8B;">Gagal memuat pratinjau foto.</span>';
                    }

                    modalEdit.classList.add('is-open');
                });
            });

            function renderExistingPhotos() {
                if (countExistingPhotosEl) countExistingPhotosEl.textContent = activeExistingPhotos.length;

                if (activeExistingPhotos.length === 0) {
                    existingPhotosGrid.innerHTML = '<span style="grid-column: 1 / -1; font-size:12px; color:#6E7A8B; text-align:center; padding:12px;">Tidak ada foto dalam galeri ini.</span>';
                    return;
                }

                let photosHtml = '';
                activeExistingPhotos.forEach(p => {
                    photosHtml += `
                        <div id="existing-card-${p.id}" style="position:relative; border-radius:4px; overflow:hidden; border:1px solid #CBD5E1; background:#000;">
                            <img src="${p.url}" alt="${p.caption || ''}" style="width:100%; height:80px; object-fit:cover; display:block;">
                            <button type="button" onclick="stageDeletePhoto(${p.id})" style="position:absolute; top:4px; right:4px; background:rgba(220,38,38,0.9); color:#fff; border:none; border-radius:50%; width:22px; height:22px; font-size:12px; cursor:pointer; line-height:22px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,0.3);" title="Hapus foto dari galeri">✕</button>
                        </div>
                    `;
                });
                existingPhotosGrid.innerHTML = photosHtml;
            }

            // Fungsi Soft-Delete Foto (Hilang dari Tampilan, Dihapus Saat Klik "Simpan Perubahan & Foto")
            window.stageDeletePhoto = function(photoId) {
                // 1. Hilangkan elemen gambar dari tampilan langsung
                const cardEl = document.getElementById(`existing-card-${photoId}`);
                if (cardEl) {
                    cardEl.remove();
                }

                // 2. Buat hidden input deleted_photo_ids[] di form edit
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'deleted_photo_ids[]';
                hiddenInput.value = photoId;
                deletedContainer.appendChild(hiddenInput);

                // 3. Update counter tanpa menutup modal
                activeExistingPhotos = activeExistingPhotos.filter(p => p.id !== photoId);
                if (countExistingPhotosEl) countExistingPhotosEl.textContent = activeExistingPhotos.length;

                if (activeExistingPhotos.length === 0) {
                    existingPhotosGrid.innerHTML = '<span style="grid-column: 1 / -1; font-size:12px; color:#D97706; text-align:center; padding:12px; background:#FEF3C7; border-radius:4px;">Seluruh foto telah ditandai untuk dihapus. Klik "Simpan Perubahan & Foto" untuk menerapkan perubahan.</span>';
                }
            };

            // Tutup Modal
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
