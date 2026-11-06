@props(['concert', 'favourited' => false])

@php
    $upcoming = $concert->isUpcoming();
    $remaining = $concert->ticketsRemaining();
    $fewLeft = $remaining > 0 && $remaining <= max(10, $concert->total_ticket * 0.1);
    $video = $concert->artistMedia()['video']['url'] ?? null;
    $price = '$' . number_format($concert->ticket_price, 2);
@endphp

<article class="concert-card {{ $upcoming ? '' : 'is-past' }}" @if($video) data-video="{{ $video }}" @endif>
    <a href="{{ route('concerts.show', $concert) }}" class="concert-card__media" tabindex="-1" aria-hidden="true">
        @if($concert->photo_url)
            <img src="{{ $concert->photo_url }}" alt="" loading="lazy">
        @else
            <div class="poster-fallback">{{ mb_strtoupper(mb_substr($concert->artist, 0, 1)) }}</div>
        @endif
    </a>

    @auth
        @unless(auth()->user()->is_admin)
            <button type="button" class="fav-btn {{ $favourited ? 'is-active' : '' }}"
                    data-favourite-url="{{ route('concerts.toggle-favourite', $concert) }}"
                    aria-pressed="{{ $favourited ? 'true' : 'false' }}"
                    aria-label="{{ $favourited ? 'Remove from favourites' : 'Add to favourites' }}">
                <i class="{{ $favourited ? 'fas' : 'far' }} fa-heart"></i>
            </button>
        @endunless
    @endauth

    <div class="concert-card__body">
        <h3><a href="{{ route('concerts.show', $concert) }}">{{ $concert->title }}</a></h3>
        <div class="concert-card__date">{{ $concert->date->format('D, d M') }}@if($concert->start_time), {{ $concert->start_time }}@endif</div>
        <div class="concert-card__venue">{{ $concert->venue }}@if($concert->city), {{ $concert->city }}@endif</div>
        <div class="concert-card__price">
            @if(! $upcoming)
                <span class="muted">Ended</span>
            @elseif($remaining === 0)
                <span class="muted">Sold out</span>
            @else
                From {{ $price }}
                @if($fewLeft)
                    <em>{{ number_format($remaining) }} left</em>
                @endif
            @endif
        </div>
    </div>

    <a class="btn btn-ghost btn-sm concert-card__cta" href="{{ route('concerts.show', $concert) }}" tabindex="-1">{{ $upcoming ? 'From ' . $price : 'View' }}</a>
</article>
