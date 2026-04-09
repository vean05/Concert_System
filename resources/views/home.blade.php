@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
        color: #1d1d1f;
        line-height: 1.6;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    /* ===== 英雄部分 ===== */
    .hero {
        padding: 120px 0 80px;
        text-align: center;
        background: linear-gradient(135deg, #ffffff 0%, #f5f5f7 100%);
        position: relative;
        overflow: hidden;
    }

    .hero::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 20% 50%, rgba(0, 0, 0, 0.02) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(0, 0, 0, 0.02) 0%, transparent 50%);
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero h1 {
        font-size: 4.5rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
    }

    .hero p {
        font-size: 1.4rem;
        color: #555;
        margin-bottom: 2.5rem;
        line-height: 1.6;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-cta {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 28px;
        border-radius: 980px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-block;
    }

    .btn-primary {
        background: #0071e3;
        color: white;
    }

    .btn-primary:hover {
        background: #0077ed;
        box-shadow: 0 8px 24px rgba(0, 113, 227, 0.25);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #f5f5f7;
        color: #1d1d1f;
        border: 1px solid #d2d2d7;
    }

    .btn-secondary:hover {
        background: #efefef;
        transform: translateY(-2px);
    }

    /* ===== 轮播区域 ===== */
    .carousel-section {
        padding: 60px 0;
        background: #ffffff;
    }

    .section-title {
        font-size: 2.8rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 3rem;
        letter-spacing: -0.01em;
    }

    .swiper {
        width: 100%;
    }

    .swiper-slide {
        height: auto;
    }

    .concert-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #f5f5f7;
    }

    .concert-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
    }

    .concert-image {
        width: 100%;
        height: 240px;
        background: linear-gradient(135deg, #e8e8ed 0%, #f5f5f7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        position: relative;
        overflow: hidden;
    }

    .concert-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .concert-content {
        padding: 2rem;
    }

    .concert-artist {
        font-size: 0.9rem;
        color: #0071e3;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .concert-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.8rem;
        color: #1d1d1f;
    }

    .concert-meta {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 1.2rem;
        line-height: 1.6;
    }

    .concert-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.6rem;
    }

    .concert-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1.2rem;
        border-top: 1px solid #f5f5f7;
    }

    .concert-price {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1d1d1f;
    }

    .concert-btn {
        background: #0071e3;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .concert-btn:hover {
        background: #0977f5;
    }

    /* ===== 网格区域 ===== */
    .concerts-grid {
        padding: 80px 0;
        background: #f5f5f7;
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2.5rem;
    }

    .concert-card-grid {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .concert-card-grid:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    .concert-card-grid .concert-image {
        height: 200px;
    }

    .concert-card-grid .concert-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* ===== 特性区域 ===== */
    .features {
        padding: 100px 0;
        background: white;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 3rem;
    }

    .feature-item {
        text-align: center;
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
    }

    .feature-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 0.8rem;
        color: #1d1d1f;
    }

    .feature-desc {
        color: #555;
        line-height: 1.7;
        font-size: 1rem;
    }

    /* ===== 分类区域 ===== */
    .categories {
        padding: 80px 0;
        background: #f5f5f7;
    }

    .categories-flex {
        display: flex;
        gap: 2rem;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding-bottom: 1rem;
    }

    .categories-flex::-webkit-scrollbar {
        height: 6px;
    }

    .categories-flex::-webkit-scrollbar-track {
        background: #e8e8ed;
        border-radius: 3px;
    }

    .categories-flex::-webkit-scrollbar-thumb {
        background: #999;
        border-radius: 3px;
    }

    .categories-flex::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .category-chip {
        white-space: nowrap;
        padding: 10px 20px;
        border-radius: 980px;
        background: white;
        border: 1px solid #d2d2d7;
        color: #1d1d1f;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .category-chip:hover {
        background: #0071e3;
        color: white;
        border-color: #0071e3;
    }

    /* ===== CTA 区域 ===== */
    .cta {
        padding: 80px 0;
        background: linear-gradient(135deg, #ffffff 0%, #f5f5f7 100%);
        text-align: center;
    }

    .cta h2 {
        font-size: 3.2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        letter-spacing: -0.01em;
    }

    .cta p {
        font-size: 1.2rem;
        color: #555;
        margin-bottom: 3rem;
    }

    /* ===== 页脚信息 ===== */
    .info-banner {
        padding: 40px 0;
        background: #ffffff;
        text-align: center;
        border-top: 1px solid #f5f5f7;
    }

    .info-banner p {
        color: #555;
        font-size: 1rem;
    }

    /* ===== 响应式 ===== */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.8rem;
        }

        .hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .grid-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .cta h2 {
            font-size: 2rem;
        }

        .hero-cta {
            flex-direction: column;
            align-items: center;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<!-- 英雄区域 -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Experience Live Music</h1>
            <p>Discover, book, and enjoy the world's best concerts all in one place</p>
            
            <div class="hero-cta">
                <a href="#concerts" class="btn btn-primary">
                    <i class="fas fa-ticket-alt"></i> Browse Concerts
                </a>
                <a href="{{ route('concerts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Explore All
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 轮播区域 -->
@if($concerts->count() > 0)
<section class="carousel-section">
    <div class="container">
        <h2 class="section-title">Now Playing</h2>
        
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                @foreach($concerts->take(8) as $concert)
                    @php
                        $bookedTickets = $concert->orders()
                            ->where('status', 'confirmed')
                            ->sum('quantity');
                        $availableTickets = $concert->total_ticket - $bookedTickets;
                    @endphp
                    <div class="swiper-slide">
                        <a href="{{ route('concerts.show', $concert) }}" style="text-decoration: none; color: inherit;">
                            <div class="concert-card">
                                <div class="concert-image">
                                    @if($concert->image_path)
                                        <img src="{{ asset('storage/' . $concert->image_path) }}" alt="{{ $concert->title }}">
                                    @else
                                        🎵
                                    @endif
                                </div>
                                <div class="concert-content">
                                    <div class="concert-artist">{{ $concert->artist }}</div>
                                    <h3 class="concert-title">{{ $concert->title }}</h3>
                                    
                                    <div class="concert-meta">
                                        <div class="concert-meta-item">
                                            <i class="fas fa-map-marker-alt" style="color: #0071e3; font-size: 0.8rem;"></i>
                                            <span>{{ Str::limit($concert->venue, 25) }}</span>
                                        </div>
                                        <div class="concert-meta-item">
                                            <i class="fas fa-calendar" style="color: #0071e3; font-size: 0.8rem;"></i>
                                            <span>{{ \Carbon\Carbon::parse($concert->date)->format('M d, Y') }}</span>
                                        </div>
                                    </div>

                                    <div class="concert-footer" style="margin-top: auto;">
                                        <span class="concert-price">${{ number_format($concert->ticket_price, 0) }}</span>
                                        <a href="{{ route('concerts.show', $concert) }}" class="concert-btn">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="swiper-button-next" style="color: #0071e3; background: white; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
            <div class="swiper-button-prev" style="color: #0071e3; background: white; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
        </div>
    </div>
</section>
@endif

<!-- 网格区域 -->
<section class="concerts-grid" id="concerts">
    <div class="container">
        <h2 class="section-title">Upcoming Concerts</h2>
        
        @if($concerts->count() > 0)
            <div class="grid-container">
                @foreach($concerts as $concert)
                    @php
                        $bookedTickets = $concert->orders()
                            ->where('status', 'confirmed')
                            ->sum('quantity');
                        $availableTickets = $concert->total_ticket - $bookedTickets;
                    @endphp
                    <a href="{{ route('concerts.show', $concert) }}" style="text-decoration: none; color: inherit;">
                        <div class="concert-card-grid">
                            <div class="concert-image">
                                @if($concert->image_path)
                                    <img src="{{ asset('storage/' . $concert->image_path) }}" alt="{{ $concert->title }}">
                                @else
                                    🎤
                                @endif
                            </div>
                            <div class="concert-content">
                                <div class="concert-artist">{{ $concert->artist }}</div>
                                <h3 class="concert-title">{{ $concert->title }}</h3>
                                
                                <div class="concert-meta">
                                    <div class="concert-meta-item">
                                        <i class="fas fa-map-marker-alt" style="color: #0071e3; font-size: 0.8rem;"></i>
                                        <span>{{ Str::limit($concert->venue, 25) }}</span>
                                    </div>
                                    <div class="concert-meta-item">
                                        <i class="fas fa-calendar" style="color: #0071e3; font-size: 0.8rem;"></i>
                                        <span>{{ \Carbon\Carbon::parse($concert->date)->format('M d, Y') }}</span>
                                    </div>
                                </div>

                                <div class="concert-footer">
                                    <span class="concert-price">${{ number_format($concert->ticket_price, 0) }}</span>
                                    <a href="{{ route('concerts.show', $concert) }}" class="concert-btn" onclick="event.preventDefault(); window.location.href=this.href;">
                                        {{ $availableTickets > 0 ? 'Book Now' : 'Sold Out' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 4rem 2rem;">
                <p style="font-size: 1.1rem; color: #555;">No concerts available at this moment</p>
            </div>
        @endif
    </div>
</section>

<!-- 特性区域 -->
<section class="features">
    <div class="container">
        <h2 class="section-title">Why Choose Us</h2>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Secure Checkout</h3>
                <p class="feature-desc">Your payment information is encrypted and protected with industry-leading security</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">Instant Delivery</h3>
                <p class="feature-desc">Get your digital tickets instantly via email after booking confirmation</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Easy to Use</h3>
                <p class="feature-desc">Simple and intuitive interface designed for a smooth booking experience</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📞</div>
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-desc">Our dedicated support team is always ready to help you anytime</p>
            </div>
        </div>
    </div>
</section>

<!-- 分类区域 -->
<section class="categories">
    <div class="container">
        <h2 class="section-title">Browse by Genre</h2>
        
        <div class="categories-flex">
            <div class="category-chip" onclick="filterByGenre('Rock')">🎸 Rock</div>
            <div class="category-chip" onclick="filterByGenre('Pop')">🎤 Pop</div>
            <div class="category-chip" onclick="filterByGenre('Jazz')">🎹 Jazz</div>
            <div class="category-chip" onclick="filterByGenre('Electronic')">🎧 Electronic</div>
            <div class="category-chip" onclick="filterByGenre('Hip-Hop')">🎶 Hip-Hop</div>
            <div class="category-chip" onclick="filterByGenre('Classical')">🎼 Classical</div>
            <div class="category-chip" onclick="filterByGenre('R&B')">💫 R&B</div>
        </div>
    </div>
</section>

<!-- CTA 区域 -->
<section class="cta">
    <div class="container">
        <h2>Ready for Amazing Shows?</h2>
        <p>Book your favorite tickets before they sell out</p>
        <a href="{{ route('concerts.index') }}" class="btn btn-primary" style="font-size: 1.1rem;">
            Start Exploring
        </a>
    </div>
</section>

<!-- 页脚信息 -->
<section class="info-banner">
    <div class="container">
        <p>✨ Join thousands of music lovers discovering their next favorite concert</p>
    </div>
</section>

<script>
    // 初始化 Swiper
    const swiper = new Swiper('.mySwiper', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 6000,
            disableOnInteraction: false,
        },
        slidesPerView: 1,
        spaceBetween: 24,
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 16,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 24,
            },
        }
    });

    function filterByGenre(genre) {
        window.location.href = "{{ route('concerts.filter') }}?genre=" + encodeURIComponent(genre);
    }
</script>

@endsection
