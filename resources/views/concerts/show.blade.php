@extends('layouts.app')

@section('title', $concert->title)
@section('body_class', 'nav-over page-dark')

@push('head')
    @if($similar->isNotEmpty())
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.css">
    @endif
@endpush

@section('content')
    @php
        $upcoming = $concert->isUpcoming();
        $soldOut = $concert->isSoldOut();
        $bookable = $concert->canBeBooked();
        $isAdmin = auth()->check() && auth()->user()->is_admin;
        $favourited = in_array($concert->id, $favouriteIds);
        $reviewCount = $concert->reviews->count();
        $averageRating = $concert->reviews->avg('rating');
        $venue = $concert->venueDetails();
        $lineup = $concert->lineup ?: [$concert->artist];
        $buyUrl = $concert->hasSeatMap() ? route('seats.show', $concert) : route('orders.create', $concert);
        $buyLabel = $concert->hasSeatMap() ? 'Choose seats in 3D' : 'Get tickets';
        $media = $concert->artistMedia();
        $songs = $media['songs'] ?? [];
        $video = $media['video'] ?? null;
        $venuePhoto = $concert->venuePhoto();
    @endphp

    <section class="event-hero">
        @if($concert->hero_url)
            <img class="event-hero__art" src="{{ $concert->hero_url }}" alt="">
        @endif
        @if($video)
            <video class="event-hero__video" id="heroVideo" src="{{ $video['url'] }}" muted loop playsinline preload="metadata" aria-hidden="true"></video>
            <button class="icon-btn event-hero__sound" type="button" id="heroVideoSound" hidden><i class="fas fa-volume-xmark"></i> <span>Sound off</span></button>
        @endif
        <div class="container">
            <p class="event-hero__date">{{ $concert->date->format('l, d F Y') }}@if($concert->start_time), {{ $concert->start_time }}@endif</p>
            <h1 class="shout">{{ $concert->artist }}</h1>
            <p class="event-hero__where">{{ $concert->title }}<br>{{ $concert->venue }}@if($concert->city), {{ $concert->city }}@endif @if($averageRating) · <i class="fas fa-star"></i> {{ number_format($averageRating, 1) }} ({{ $reviewCount }})@endif</p>
            <div class="event-hero__tools">
                @if($upcoming)
                    <a class="icon-btn" href="{{ route('concerts.calendar', $concert) }}"><i class="far fa-calendar-plus"></i> Calendar</a>
                @endif
                <button class="icon-btn" type="button" data-copy-link><i class="fas fa-link"></i> <span>Share</span></button>
                @auth
                    @unless($isAdmin)
                        <button type="button" class="icon-btn {{ $favourited ? 'is-active' : '' }}"
                                data-favourite-url="{{ route('concerts.toggle-favourite', $concert) }}"
                                aria-pressed="{{ $favourited ? 'true' : 'false' }}"
                                aria-label="{{ $favourited ? 'Remove from favourites' : 'Add to favourites' }}">
                            <i class="{{ $favourited ? 'fas' : 'far' }} fa-heart"></i>
                        </button>
                    @endunless
                @endauth
            </div>
        </div>
    </section>

    <div class="event-main">
        @if($concert->hero_url)
            <div class="event-ambient" aria-hidden="true"><img src="{{ $concert->hero_url }}" alt=""></div>
        @endif
        <div class="container">
            <div class="event-body">
                <div>
                    <section class="event-section">
                        <h2>About this show</h2>
                        <p class="mb-0" style="white-space: pre-line">{{ $concert->description }}</p>
                    </section>

                    @if($songs)
                        <section class="event-section">
                            <h2>Top songs</h2>
                            <div>
                                @foreach($songs as $song)
                                    <div class="track">
                                        <span class="track__no">{{ $loop->iteration }}</span>
                                        <button class="track__art" type="button" aria-label="Play {{ $song['title'] }}"
                                                data-preview="{{ $song['preview'] }}" data-title="{{ $song['title'] }}" data-artist="{{ $concert->artist }}"
                                                data-art="{{ $song['artwork'] }}" data-link="{{ $song['link'] }}">
                                            <img src="{{ $song['artwork'] }}" alt="" loading="lazy">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <div class="text-truncate">
                                            <strong>{{ $song['title'] }}</strong>
                                            <small>{{ $song['album'] }}</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bars" aria-hidden="true"><i></i><i></i><i></i></span>
                                            <a href="{{ $song['link'] }}" target="_blank" rel="noopener">Apple Music</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="photo-credit">30-second previews provided by Apple Music.</p>
                        </section>
                    @endif

                    @if($video)
                        <section class="event-section">
                            <h2>Watch</h2>
                            <div class="watch">
                                <video src="{{ $video['url'] }}" poster="{{ $video['poster'] }}" controls playsinline preload="none"></video>
                            </div>
                            <p class="photo-credit">{{ $video['title'] }}. Preview provided by <a href="{{ $video['link'] }}" target="_blank" rel="noopener">Apple Music</a>.</p>
                        </section>
                    @endif

                    <section class="event-section">
                        <h2>Line-up</h2>
                        <div class="lineup">
                            @foreach($lineup as $index => $act)
                                <div class="lineup__act">
                                    @if($act === $concert->artist && $concert->hero_url)
                                        <img src="{{ $concert->hero_url }}" alt="">
                                    @else
                                        <i>{{ mb_strtoupper(mb_substr($act, 0, 1)) }}</i>
                                    @endif
                                    <span>{{ $act }}<small>{{ $index === 0 ? 'Headliner' : 'Special guest' }}</small></span>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    @if($concert->doors_time || $concert->start_time)
                        <section class="event-section">
                            <h2>On the night</h2>
                            <ul class="timeline">
                                @if($concert->doors_time)
                                    <li><strong>{{ $concert->doors_time }}</strong><span>Doors open</span></li>
                                @endif
                                @if($concert->start_time)
                                    <li><strong>{{ $concert->start_time }}</strong><span>Show starts</span></li>
                                @endif
                            </ul>
                        </section>
                    @endif

                    <section class="event-section">
                        <h2>Tickets</h2>
                        @if($upcoming)
                            <div class="live-sales" id="liveSales" data-url="{{ route('concerts.pulse', $concert) }}" aria-live="polite">
                                <div class="live-sales__head"><span class="live-dot"></span> Live sales <span class="muted" id="pulseLast"></span></div>
                                <div class="live-sales__stats">
                                    <div><strong id="pulsePercent">–</strong><span>sold</span></div>
                                    <div><strong id="pulseToday">–</strong><span>sold in 24 hours</span></div>
                                    <div><strong id="pulseHold">–</strong><span>seats in carts now</span></div>
                                </div>
                            </div>
                        @endif
                        @foreach($concert->sections as $section)
                            @php
                                $remaining = $section->remaining();
                                $soldPercent = $section->capacity ? round($section->soldCount() / $section->capacity * 100) : 100;
                            @endphp
                            <div class="tier-row" style="--swatch: {{ $section->color }}" data-tier-id="{{ $section->id }}">
                                <span class="tier-swatch" style="background: {{ $section->color }}"></span>
                                <div>
                                    <h4>{{ $section->name }}</h4>
                                    <div class="muted">
                                        {{ $section->isSeated() ? 'Reserved seat' : 'Standing' }}
                                        @if($upcoming) · <span data-tier-left>{{ $remaining ? number_format($remaining) . ' left' : 'Sold out' }}</span> @endif
                                    </div>
                                    <div class="meter" aria-hidden="true"><span style="width: {{ $soldPercent }}%"></span></div>
                                </div>
                                <span class="price">${{ number_format($section->price, 2) }}</span>
                            </div>
                        @endforeach
                    </section>

                    @if($venue)
                        <section class="event-section">
                            <h2>{{ $venue['name'] }}</h2>
                            @if($venuePhoto)
                                <img class="venue-photo" src="{{ asset($venuePhoto['photo']) }}" alt="{{ $venue['name'] }}" loading="lazy">
                            @endif
                            <div class="venue-facts">
                                <div><span>Address</span>{{ $venue['address'] }}</div>
                                <div><span>Capacity</span>{{ $venue['capacity_note'] }}</div>
                                <div><span>Getting there</span>{{ $venue['transit'] }}</div>
                            </div>
                            @if($bookable && ! $isAdmin)
                                <a class="btn btn-ghost" href="{{ $buyUrl }}">Explore the arena in 3D</a>
                            @endif
                        </section>
                    @endif

                    @if(! $venue && $venuePhoto)
                        <section class="event-section">
                            <h2>{{ $concert->venue }}</h2>
                            <img class="venue-photo mb-0" src="{{ asset($venuePhoto['photo']) }}" alt="{{ $concert->venue }}" loading="lazy">
                        </section>
                    @endif

                    <section class="event-section">
                        <h2>Good to know</h2>
                        <div class="accordion accordion-flush" id="faq">
                            @foreach([
                                'Can I cancel my tickets?' => 'Orders can be cancelled for a refund any time before the concert date. Open the order in My Tickets and choose Cancel order.',
                                'What should I bring?' => 'Valid ID is required for entry. Weapons, glass bottles and professional cameras are not allowed.',
                                'When should I arrive?' => 'Please arrive at least 30 minutes before the show starts. Children under 12 may need an adult with them.',
                                'Can I resell my ticket?' => 'Resale of tickets is prohibited without written permission. Performers and dates may change with advance notice.',
                            ] as $question => $answer)
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $loop->index }}" aria-expanded="false" aria-controls="faq{{ $loop->index }}">{{ $question }}</button>
                                    </h3>
                                    <div id="faq{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#faq">
                                        <div class="accordion-body">{{ $answer }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="event-section" id="reviews">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h2 class="mb-0">Reviews ({{ $reviewCount }})</h2>
                            @if($canReview)
                                <a class="btn btn-ghost btn-sm" href="{{ route('reviews.create', $concert) }}">Write a review</a>
                            @endif
                        </div>

                        @if($reviewCount)
                            <div class="rating-summary">
                                <div>
                                    <strong>{{ number_format($averageRating, 1) }}</strong>
                                    <span class="stars" aria-hidden="true">{{ str_repeat('★', (int) round($averageRating)) }}</span>
                                </div>
                                <div>
                                    @foreach($ratings as $stars => $count)
                                        <div class="rating-bar">
                                            <span>{{ $stars }}★</span>
                                            <div class="meter" aria-hidden="true"><span style="width: {{ round($count / $reviewCount * 100) }}%"></span></div>
                                            <span>{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @forelse($concert->reviews->sortByDesc('created_at') as $review)
                            <article class="review">
                                <div class="d-flex justify-content-between gap-3">
                                    <strong>{{ optional($review->user)->name ?? 'Former customer' }}</strong>
                                    <span class="stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                </div>
                                <p class="mt-2 mb-1">{{ $review->comment }}</p>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="small-label">{{ $review->created_at->format('d M Y') }}</span>
                                    @if($userReview && $userReview->id === $review->id)
                                        <a class="btn btn-ghost btn-sm" href="{{ route('reviews.edit', $review) }}">Edit</a>
                                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" data-confirm="Delete your review?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger-ghost btn-sm" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="muted mb-0">No reviews yet. Ticket holders can share theirs here.</p>
                        @endforelse
                    </section>
                </div>

                <aside class="event-body__aside">
                    <div class="buy-box" id="buyAnchor">
                        @if($upcoming && ! $soldOut)
                            <div class="buy-box__price">From ${{ number_format($concert->ticket_price, 2) }}</div>
                            <p class="buy-box__note">The price you see is the price you pay.</p>
                        @else
                            <div class="buy-box__price">{{ $upcoming ? 'Sold out' : 'This show has ended' }}</div>
                            <p class="buy-box__note">{{ $upcoming ? 'All tickets have been sold.' : 'Tickets are no longer on sale.' }}</p>
                        @endif

                        @if($isAdmin)
                            <a class="btn btn-white" href="{{ route('admin.concerts.show', $concert) }}">Manage concert</a>
                        @elseif($bookable)
                            <a class="btn btn-white" href="{{ $buyUrl }}">{{ $buyLabel }}</a>
                        @endif

                        @if($upcoming)
                            <div class="buy-box__left">
                                <span>{{ number_format($concert->ticketsRemaining()) }} of {{ number_format($concert->total_ticket) }} left</span>
                                <div class="countdown" data-countdown="{{ $concert->startsAt()->toIso8601String() }}" role="timer" aria-label="Time until the show">
                                    <div><strong data-unit="days">0</strong><span>d</span></div>
                                    <div><strong data-unit="hours">00</strong><span>h</span></div>
                                    <div><strong data-unit="minutes">00</strong><span>m</span></div>
                                    <div><strong data-unit="seconds">00</strong><span>s</span></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @foreach(array_filter(['Artist photo' => $media['credit'] ?? null, 'Venue photo' => $venuePhoto['credit'] ?? null]) as $label => $credit)
                        <p class="photo-credit">{{ $label }}: {{ $credit['author'] }}, <a href="{{ $credit['source'] }}" target="_blank" rel="noopener">{{ $credit['licence'] }}</a></p>
                    @endforeach
                </aside>
            </div>

            @if($similar->isNotEmpty())
                <section class="section-gap pt-0">
                    <div class="section-head">
                        <h2>You might also like</h2>
                        <div class="rail-nav">
                            <button type="button" data-rail-prev="similarRail" aria-label="Previous concerts"><i class="fas fa-arrow-left"></i></button>
                            <button type="button" data-rail-next="similarRail" aria-label="Next concerts"><i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    <div class="swiper rail" id="similarRail" data-rail>
                        <div class="swiper-wrapper">
                            @foreach($similar as $other)
                                <div class="swiper-slide">
                                    <x-concert-card :concert="$other" :favourited="in_array($other->id, $favouriteIds)" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    @if($bookable && ! $isAdmin)
        <div class="buy-bar" id="buyBar">
            <div class="container">
                <div class="text-truncate">
                    <strong>{{ $concert->title }}</strong>
                    <span class="muted">From ${{ number_format($concert->ticket_price, 2) }}</span>
                </div>
                <a class="btn btn-white flex-shrink-0" href="{{ $buyUrl }}">{{ $buyLabel }}</a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if($upcoming ?? false)
        <script src="{{ asset('js/concert-live.js') }}" defer></script>
    @endif
    @if($similar->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.js"></script>
        <script src="{{ asset('js/market.js') }}"></script>
    @endif
@endpush
