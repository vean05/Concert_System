@extends('layouts.app')

@section('body_class', 'nav-over')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.css">
@endpush

@section('content')
    @php
        $lead = $featured->first();
        $scene = config('media.scenes.stage.photo');
        $cards = $hasUpcoming ? $upcoming->take(4) : $past;
        $calendar = $hasUpcoming ? $upcoming->slice(4) : collect();
    @endphp

    <section class="hero" id="heroStage">
        <div class="hero__media" id="heroMedia" aria-hidden="true">
            @if($lead)
                <img class="is-on" src="{{ $lead->hero_url }}" alt="">
            @elseif($scene)
                <img class="is-on" src="{{ asset($scene) }}" alt="">
            @endif
        </div>

        <div class="container">
            <div class="hero__body">
                <div>
                    <p class="hero__meta" id="heroMeta">{{ $lead ? $lead->date->format('D, d M') . ' · ' . $lead->venue : 'New shows soon' }}</p>
                    <h1 class="shout" id="heroTitle">{{ optional($lead)->artist ?? 'Your next night out' }}</h1>
                    <p class="hero__lead" id="heroLead">{{ optional($lead)->tagline ?? 'Walk the arena in 3D and pick the exact seat you want.' }}</p>
                    @if($lead)
                        <div class="hero__actions">
                            <a class="btn btn-white" id="heroLink" href="{{ route('concerts.show', $lead) }}">Get tickets</a>
                            <button class="btn btn-ghost-light" type="button" id="heroSound" hidden><i class="fas fa-play"></i> Play a song</button>
                            <span class="hero__price" id="heroPrice">From ${{ number_format($lead->ticket_price, 2) }}</span>
                        </div>
                    @endif
                </div>

                @if($featured->count() > 1)
                    <div class="hero-queue" id="heroQueue" aria-label="Featured concerts">
                        @foreach($featured as $concert)
                            @php $media = $concert->artistMedia(); @endphp
                            <button type="button" class="{{ $loop->first ? 'is-on' : '' }}" @if($loop->index > 2) hidden @endif
                                    data-artist="{{ $concert->artist }}"
                                    data-lead="{{ $concert->tagline ?: $concert->title }}"
                                    data-meta="{{ $concert->date->format('D, d M') }} · {{ $concert->venue }}"
                                    data-price="From ${{ number_format($concert->ticket_price, 2) }}"
                                    data-url="{{ route('concerts.show', $concert) }}"
                                    data-photo="{{ $concert->hero_url }}"
                                    data-video="{{ $media['video']['url'] ?? '' }}"
                                    data-song="{{ json_encode($media['songs'][0] ?? null) }}">
                                <img src="{{ $concert->hero_url }}" alt="" loading="lazy">
                                <strong>{{ $concert->artist }}</strong>
                                <span>{{ $concert->date->format('d M') }}</span>
                                <i></i>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if($genres->isNotEmpty())
        <nav class="tabs" aria-label="Genres">
            <div class="container tabs__row">
                <a class="is-active" href="{{ route('concerts.index') }}">All concerts</a>
                @foreach($genres as $concert)
                    <a href="{{ route('concerts.index', ['genre' => $concert->genre]) }}">{{ $concert->genre }}</a>
                @endforeach
            </div>
        </nav>
    @endif

    <section class="section-gap reveal">
        <div class="container">
            <div class="section-head">
                <h2>{{ $hasUpcoming ? 'On sale now' : 'Recent concerts' }}</h2>
                <a href="{{ route('concerts.index') }}">See all <i class="fas fa-arrow-right"></i></a>
            </div>

            @if($cards->isEmpty())
                <div class="empty">
                    <h3>No concerts yet</h3>
                    <p class="mb-0">New shows will appear here as soon as they are announced.</p>
                </div>
            @else
                <div class="concert-grid">
                    @foreach($cards as $concert)
                        <x-concert-card :concert="$concert" :favourited="in_array($concert->id, $favouriteIds)" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($flagship)
        @php $venue = $flagship->venueDetails(); @endphp
        <section class="seat-band reveal">
            @if($scene)
                <img src="{{ asset($scene) }}" alt="" loading="lazy">
            @endif
            <div class="container">
                <h2 class="shout">See your seat before you buy it</h2>
                <p>Pick any seat in {{ $venue['name'] }} and the camera moves to your view of the stage, with the show playing on the big screen.</p>
                <a class="btn btn-white" href="{{ route('concerts.show', $flagship) }}">See {{ $flagship->artist }}</a>
            </div>
        </section>
    @endif

    @if($calendar->isNotEmpty())
        <section class="section-gap pb-0 reveal">
            <div class="container">
                <div class="section-head">
                    <h2>Coming up</h2>
                    <a href="{{ route('concerts.index') }}">Full calendar <i class="fas fa-arrow-right"></i></a>
                </div>
                <div>
                    @foreach($calendar as $concert)
                        <div class="date-row">
                            <div class="date-row__date">
                                <strong>{{ $concert->date->format('d M') }}</strong>
                                <span>{{ $concert->date->format('D') }}@if($concert->start_time) · {{ $concert->start_time }}@endif</span>
                            </div>
                            <a class="date-row__what" href="{{ route('concerts.show', $concert) }}">
                                <strong>{{ $concert->title }}</strong>
                                <span class="date-row__where">{{ $concert->venue }}@if($concert->city), {{ $concert->city }}@endif</span>
                            </a>
                            <a class="btn btn-accent btn-sm" href="{{ route('concerts.show', $concert) }}">From ${{ number_format($concert->ticket_price, 2) }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($songs->isNotEmpty())
        <section class="section-gap pb-0 reveal">
            <div class="container">
                <div class="section-head">
                    <h2>Hear the setlist</h2>
                    <div class="rail-nav">
                        <button type="button" data-rail-prev="songRail" aria-label="Previous songs"><i class="fas fa-arrow-left"></i></button>
                        <button type="button" data-rail-next="songRail" aria-label="Next songs"><i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                <div class="swiper rail rail--small" id="songRail" data-rail>
                    <div class="swiper-wrapper">
                        @foreach($songs as $song)
                            <div class="swiper-slide">
                                <button class="sound-tile" type="button"
                                        data-preview="{{ $song->preview }}" data-title="{{ $song->title }}" data-artist="{{ $song->artist }}"
                                        data-art="{{ $song->artwork }}" data-link="{{ $song->link }}">
                                    <span class="sound-tile__art">
                                        <img src="{{ $song->artwork }}" alt="" loading="lazy">
                                        <span class="sound-tile__play"><i class="fas fa-play"></i></span>
                                    </span>
                                    <strong>{{ $song->title }}</strong>
                                    <small>{{ $song->artist }}</small>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($venues->isNotEmpty())
        <section class="section-gap pb-0 reveal">
            <div class="container">
                <div class="section-head"><h2>Venues</h2></div>
                <div class="row g-3">
                    @foreach($venues as $venue)
                        @php $photo = $venue->next->venuePhoto(); @endphp
                        <div class="col-6 col-lg-3">
                            <a class="venue-card" href="{{ route('concerts.index', ['venue' => $venue->next->venue]) }}">
                                <img src="{{ $photo ? asset($photo['photo']) : $venue->next->photo_url }}" alt="" loading="lazy">
                                <h3>{{ $venue->next->venue }}</h3>
                                <span>{{ $venue->next->city }} · {{ $venue->shows }} {{ \Illuminate\Support\Str::plural('show', $venue->shows) }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($hasUpcoming && $past->isNotEmpty())
        <section class="section-gap reveal">
            <div class="container">
                <div class="section-head">
                    <h2>Recently played</h2>
                    <div class="rail-nav">
                        <button type="button" data-rail-prev="pastRail" aria-label="Previous concerts"><i class="fas fa-arrow-left"></i></button>
                        <button type="button" data-rail-next="pastRail" aria-label="Next concerts"><i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                <div class="swiper rail" id="pastRail" data-rail>
                    <div class="swiper-wrapper">
                        @foreach($past as $concert)
                            <div class="swiper-slide">
                                <x-concert-card :concert="$concert" :favourited="in_array($concert->id, $favouriteIds)" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @else
        <div class="section-gap pt-0"></div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.js"></script>
    <script src="{{ asset('js/market.js') }}"></script>
@endpush
