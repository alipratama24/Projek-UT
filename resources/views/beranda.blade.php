@extends('layouts.app')

@section('title', 'Beranda — naro capture')

@section('content')
    {{-- HERO --}}
    <section class="hero" style="background-image: linear-gradient(90deg, rgba(18,32,24,.82) 0%, rgba(18,32,24,0.15) 70%), url('{{ asset('images/hero.jpg') }}?v={{ time() }}')">
        <div class="hero__message">
            <span class="eyebrow eyebrow--light">FOTOGRAFI LIFESTYLE &amp; PORTRAIT INDONESIA</span>
            <h1 class="hero__title">Cerita Anda,<br>di setiap momen.</h1>
            <p class="hero__lead">Abadikan kebersamaan dan momen berharga dengan foto yang alami, hangat, dan penuh makna.</p>
            <a href="#paket" class="btn btn--fill" style="width:240px">Paket Layanan @include('partials.arrow')</a>
        </div>
        <div class="hero__details">
            <span class="hero__location"><i class="dot"></i>BERBASIS DI JAKARTA · MELAYANI SELURUH INDONESIA</span>
            <span>Menangkap momen-momen sederhana dengan hati.</span>
        </div>
    </section>

    {{-- KARYA TERBARU --}}
    <section id="karya" class="section">
        <div class="section__intro">
            <div class="section__heading">
                <span class="eyebrow">BEBERAPA CERITA YANG TELAH KUABADIKAN</span>
                <h2 class="section__title">Karya Terbaru</h2>
            </div>
            <p class="body-text" style="width:360px">Kumpulan potret keluarga, hubungan, dan momen sehari-hari yang ditangkap dengan alami dan penuh hati.</p>
        </div>

        <div class="masonry">
            @foreach ($columns as $column)
                <div class="masonry__col">
                    @foreach ($column as $work)
                        @php
                            $targetUrl = !empty($work['slug']) ? route('galeri-klien', ['slug' => $work['slug']]) : route('galeri-klien');
                            $imgSrc = $work['image'];
                            if (!\Illuminate\Support\Str::startsWith($imgSrc, ['http://', 'https://'])) {
                                if (file_exists(public_path('images/' . $imgSrc))) {
                                    $imgSrc = asset('images/' . $imgSrc);
                                } elseif (file_exists(public_path('storage/' . $imgSrc))) {
                                    $imgSrc = asset('storage/' . $imgSrc);
                                } else {
                                    $imgSrc = asset('images/work-1.jpg');
                                }
                            }
                        @endphp
                        <a href="{{ $targetUrl }}" class="work-link" style="text-decoration:none; color:inherit; display:block;" title="Buka Galeri: {{ $work['title'] }}">
                            <figure class="work">
                                <div class="work__img" style="position:relative; overflow:hidden; border-radius:4px;">
                                    <img src="{{ $imgSrc }}" alt="{{ $work['title'] }}" style="height:{{ $work['height'] }}px; object-fit:cover; width:100%; transition:transform 0.3s ease;">
                                    @if(!empty($work['is_private']))
                                        <span style="position:absolute; top:12px; right:12px; background:rgba(15,23,42,0.85); color:#fff; font-size:11px; font-weight:500; padding:4px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:4px; backdrop-filter:blur(4px); box-shadow:0 2px 4px rgba(0,0,0,0.2);">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                            Privat
                                        </span>
                                    @endif
                                </div>
                                <figcaption>
                                    <span class="work__title">{{ $work['title'] }}</span>
                                    <span class="work__cat">{{ $work['category'] }}</span>
                                </figcaption>
                            </figure>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="section__note">
            <span>Kebersamaan yang nyata, diabadikan sepenuh hati.</span>
            <span class="small-caps">KUMPULAN KARYA · 2025—2026</span>
        </div>
    </section>

    {{-- PAKET LAYANAN --}}
    <section id="paket" class="section section--tint" style="padding-bottom:48px">
        <div class="section__intro">
            <div class="section__heading">
                <span class="eyebrow">MARI BUAT KENANGAN YANG BERARTI</span>
                <h2 class="section__title">Paket Layanan</h2>
            </div>
            <p class="body-text" style="width:360px">Sesi yang sederhana, dipandu dengan lembut, dan memberi ruang bagi Anda untuk menjadi diri sendiri.</p>
        </div>

        @php $packageCount = count($packages); @endphp

        {{-- Jika lebih dari 3 paket: mode swipe horizontal, jika ≤ 3: grid biasa --}}
        <div class="packages {{ $packageCount > 3 ? 'packages--swipe' : '' }}" id="packages-track">
            @foreach ($packages as $pkg)
                <article class="package {{ $pkg['featured'] ? 'package--featured' : '' }}">
                    <img class="package__img" src="{{ asset('images/' . $pkg['image']) }}" alt="{{ $pkg['name'] }}">
                    <div class="package__body">
                        <div class="package__intro">
                            <div class="package__label">
                                <span>{{ $pkg['label'] }}</span>
                                @if ($pkg['featured'])
                                    <span class="badge">PILIHAN TERPOPULER</span>
                                @endif
                            </div>
                            <h3 class="package__name">{{ $pkg['name'] }}</h3>
                            <p class="body-text package__desc">{{ $pkg['description'] }}</p>
                        </div>
                        <div class="package__price">
                            <span>{{ $pkg['price'] }}</span><small>/ sesi</small>
                        </div>
                        <hr class="divider">
                        <ul class="package__list">
                            @foreach ($pkg['items'] as $item)
                                <li><img src="{{ asset('images/check.svg') }}" alt="">{{ $item }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pemesanan', ['package' => Str::slug($pkg['name'])]) }}"
                           class="btn {{ $pkg['featured'] ? 'btn--fill' : 'btn--outline' }} btn--block">
                            Pesan {{ $pkg['name'] }} @include('partials.arrow')
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if($packageCount > 3)
        {{-- Navigasi swipe + indikator dot --}}
        <div class="packages-nav" id="packages-nav">
            <button type="button" class="packages-nav__btn" id="pkg-prev" aria-label="Paket sebelumnya">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <div class="packages-nav__dots" id="pkg-dots">
                @for($i = 0; $i < $packageCount; $i++)
                    <span class="pkg-dot {{ $i === 0 ? 'is-active' : '' }}" data-index="{{ $i }}"></span>
                @endfor
            </div>
            <button type="button" class="packages-nav__btn" id="pkg-next" aria-label="Paket berikutnya">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>

        <script>
        (function() {
            const track = document.getElementById('packages-track');
            const dots = document.querySelectorAll('.pkg-dot');
            const btnPrev = document.getElementById('pkg-prev');
            const btnNext = document.getElementById('pkg-next');
            if (!track) return;

            let currentIndex = 0;
            const cards = track.querySelectorAll('.package');
            const total = cards.length;

            function scrollToCard(idx) {
                if (idx < 0) idx = 0;
                if (idx >= total) idx = total - 1;
                currentIndex = idx;
                cards[idx].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
                dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
            }

            btnPrev.addEventListener('click', () => scrollToCard(currentIndex - 1));
            btnNext.addEventListener('click', () => scrollToCard(currentIndex + 1));

            // Update dot saat scroll manual
            let scrollTimer;
            track.addEventListener('scroll', () => {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => {
                    let closestIdx = 0;
                    let minDist = Infinity;
                    cards.forEach((card, i) => {
                        const dist = Math.abs(card.getBoundingClientRect().left - track.getBoundingClientRect().left);
                        if (dist < minDist) { minDist = dist; closestIdx = i; }
                    });
                    currentIndex = closestIdx;
                    dots.forEach((d, i) => d.classList.toggle('is-active', i === closestIdx));
                }, 80);
            });

            // Klik dot
            dots.forEach((dot, i) => dot.addEventListener('click', () => scrollToCard(i)));
        })();
        </script>
        @endif

        <div class="section__note" style="align-items:center">
            <span style="line-height:1.6">Setiap sesi dilengkapi dengan sentuhan yang lebih personal. Galeri biasanya dikirim dalam waktu dua minggu.</span>
            <a href="mailto:hello@narocapture.com" class="link-blue">Butuh paket khusus?&nbsp; hello@narocapture.com ↗</a>
        </div>
    </section>
@endsection

