<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Backoffice — Clara Hayes Photography</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="admin-login-body">
    <div class="admin-login-container">
        <div class="admin-login-card">
            {{-- BRAND HEADER --}}
            <div class="admin-login-header">
                <a href="{{ url('/') }}" class="admin-login-brand">
                    <span class="admin-brand-name">clara hayes.</span>
                    <span class="admin-brand-tag">STUDIO FOTOGRAFI / BACKOFFICE</span>
                </a>
                <h1 class="admin-login-title">Masuk ke Backoffice</h1>
                <p class="admin-login-subtitle">Akses dasbor manajemen pesanan, pembayaran, jadwal, dan galeri studio.</p>
            </div>

            {{-- ALERT NOTIFIKASI --}}
            @if(session('success'))
                <div class="admin-alert-box alert-success-box" style="margin-bottom: 20px;">
                    <strong class="admin-alert-title text-green">{{ session('success') }}</strong>
                </div>
            @endif

            @if($errors->any())
                <div class="admin-alert-box alert-danger-box" style="margin-bottom: 20px; background-color: #FEF2F2; border-color: #F87171;">
                    <strong class="admin-alert-title" style="color: #991B1B;">Gagal Masuk</strong>
                    <ul style="margin: 4px 0 0 16px; padding: 0; color: #991B1B; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM LOGIN --}}
            <form action="{{ route('admin.login.submit') }}" method="POST" class="admin-login-form">
                @csrf
                <div class="admin-form-group">
                    <label class="admin-meta-label" for="email">Email Admin *</label>
                    <div class="admin-input-icon-wrap">
                        <svg class="admin-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="admin-form-input @error('email') is-invalid @enderror" 
                            value="{{ old('email') }}" 
                            placeholder="Masukkan email Anda" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-meta-label" for="password">Kata Sandi *</label>
                    <div class="admin-input-icon-wrap">
                        <svg class="admin-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="admin-form-input @error('password') is-invalid @enderror" 
                            value="" 
                            placeholder="Masukkan kata sandi Anda" 
                            required
                        >
                    </div>
                </div>

                <div class="admin-login-remember">
                    <label class="admin-checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ingat sesi masuk di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="admin-btn-primary" style="width: 100%; justify-content: center; height: 46px; font-size: 15px; margin-top: 10px;">
                    Masuk ke Backoffice →
                </button>
            </form>

            <div class="admin-login-footer">
                <a href="{{ url('/') }}" class="admin-login-back-link">
                    ← Kembali ke Situs Publik Studio
                </a>
            </div>
        </div>
    </div>
</body>
</html>

