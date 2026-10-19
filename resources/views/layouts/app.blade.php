<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Clara Hayes Photography')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        // Setiap reload selalu mulai dari atas (hero), bukan posisi terakhir
        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        if (location.hash) history.replaceState(null, '', location.pathname + location.search);
        window.addEventListener('load', () => window.scrollTo(0, 0));
    </script>
</head>
<body>
    {{-- NAVIGASI --}}
    <header class="nav">
        <a href="{{ url('/') }}" class="brand brand--light">
            <span class="brand__name">clara hayes.</span>
            <span class="brand__tag">PHOTOGRAPHY STUDIO</span>
        </a>
        <nav class="nav__links">
            <a href="{{ url('/#karya') }}" class="nav__link">Karya terbaru</a>
            <a href="{{ url('/#paket') }}" class="nav__link">Paket layanan</a>
            <a href="{{ route('cek-pesanan') }}" class="btn btn--outline-light" style="width:180px">
                Cek Pesanan @include('partials.arrow')
            </a>
        </nav>
    </header>

    @yield('content')

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="footer__top">
            <div class="footer__identity">
                <span class="brand__name">clara hayes.</span>
                <span class="brand__tag muted">STUDIO FOTOGRAFI</span>
                <p class="body-text">Fotografi otentik dan penuh hati untuk orang-orang yang ingin cerita mereka diceritakan dengan baik.</p>
            </div>
            <div class="footer__nav">
                <div class="footer__col" style="width:140px">
                    <span class="eyebrow">JELAJAHI</span>
                    <a href="#karya">Karya terbaru</a>
                    <a href="#paket">Paket layanan</a>
                    <a href="#">Jurnal</a>
                    <a href="#">Tentang</a>
                </div>
                <div class="footer__col" style="width:180px">
                    <span class="eyebrow">HUBUNGI</span>
                    <a href="mailto:hello@clarahayes.studio" class="icon-item"><img src="{{ asset('images/icon-email.svg') }}" alt="">hello@clarahayes.studio</a>
                    <span class="icon-item"><img src="{{ asset('images/icon-phone.svg') }}" alt="">Hubungi studio untuk informasi</span>
                    <span class="icon-item"><img src="{{ asset('images/icon-location.svg') }}" alt="">Jakarta, Indonesia</span>
                </div>
                <div class="footer__col" style="width:140px">
                    <span class="eyebrow">SOSIAL</span>
                    <a href="#" class="icon-item"><img src="{{ asset('images/icon-instagram.svg') }}" alt="">Instagram</a>
                    <a href="#" class="icon-item"><img src="{{ asset('images/icon-pinterest.svg') }}" alt="">Pinterest</a>
                    <a href="#" class="icon-item"><img src="{{ asset('images/icon-journal.svg') }}" alt="">Jurnal</a>
                </div>
            </div>
        </div>
        <hr class="divider">
        <div class="footer__bottom">
            <span>© {{ date('Y') }} CLARA HAYES PHOTOGRAPHY</span>
            <div class="footer__legal">
                <a href="#">PRIVASI</a>
                <a href="#">SYARAT</a>
            </div>
        </div>
    </footer>

    {{-- Animasi muncul saat di-scroll --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const targets = document.querySelectorAll(
                '.section__intro, .work, .package, .section__note, .footer__top, .footer__bottom'
            );
            targets.forEach(el => {
                el.classList.add('reveal');
                // efek berurutan untuk elemen yang bersebelahan
                const index = [...el.parentElement.children].indexOf(el);
                el.style.transitionDelay = (index % 3) * 0.12 + 's';
            });

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        // hapus jeda setelah muncul agar efek hover langsung responsif
                        setTimeout(() => entry.target.style.transitionDelay = '', 1200);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            targets.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>

