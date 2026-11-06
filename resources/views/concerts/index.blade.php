@extends('layouts.app')

@section('title', 'Concerts')
@section('body_class', 'nav-over')

@section('content')
    @php
        $scene = config('media.scenes.stage.photo');
        $filtered = request()->hasAny(['q', 'venue', 'genre', 'date']);
        $tabUrl = function ($genre) {
            return route('concerts.index', array_filter(array_merge(request()->except(['genre', 'page']), ['genre' => $genre])));
        };
    @endphp

    <header class="browse-head">
        @if($scene)
            <img src="{{ asset($scene) }}" alt="">
        @endif
        <div class="container">
            <h1 class="shout">Concerts</h1>
            <p>{{ number_format($concerts->total()) }} {{ \Illuminate\Support\Str::plural('show', $concerts->total()) }}, soonest first</p>
        </div>
    </header>

    <nav class="tabs" aria-label="Genres">
        <div class="container tabs__row">
            <a class="{{ request('genre') ? '' : 'is-active' }}" href="{{ $tabUrl(null) }}">All</a>
            @foreach($genres as $genre)
                <a class="{{ request('genre') === $genre ? 'is-active' : '' }}" href="{{ $tabUrl($genre) }}">{{ $genre }}</a>
            @endforeach
        </div>
    </nav>

    <div class="container pb-5">
        <form class="filter-bar" action="{{ route('concerts.index') }}" method="GET" role="search" data-autosubmit>
            <input type="hidden" name="when" value="{{ $when }}">
            @if(request('genre'))
                <input type="hidden" name="genre" value="{{ request('genre') }}">
            @endif
            <input class="pill d-lg-none {{ request('q') ? 'is-set' : '' }}" type="text" name="q" value="{{ request('q') }}" placeholder="Artist or tour" aria-label="Artist or tour">
            @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All dates'] as $value => $label)
                <a class="pill {{ $when === $value ? 'is-set' : '' }}" href="{{ route('concerts.index', array_merge(request()->except(['when', 'page']), ['when' => $value])) }}">{{ $label }}</a>
            @endforeach
            <input class="pill {{ request('venue') ? 'is-set' : '' }}" type="text" name="venue" value="{{ request('venue') }}" placeholder="Venue or city" aria-label="Venue or city">
            <input class="pill {{ request('date') ? 'is-set' : '' }} @error('date') is-invalid @enderror" type="date" name="date" value="{{ request('date') }}" aria-label="Date">
            @if($filtered)
                <a class="pill" href="{{ route('concerts.index') }}"><i class="fas fa-xmark me-2"></i>Clear</a>
            @endif
            <span class="filter-bar__end">
                <button class="view-toggle" type="button" data-view="grid" aria-pressed="true" aria-label="Show as a grid"><i class="fas fa-table-cells-large"></i></button>
                <button class="view-toggle" type="button" data-view="list" aria-pressed="false" aria-label="Show as a list"><i class="fas fa-list"></i></button>
            </span>
            <button class="visually-hidden" type="submit">Search</button>
        </form>

        @error('date')
            <p class="text-danger small">{{ $message }}</p>
        @enderror

        @if($concerts->isEmpty())
            <div class="empty">
                <h3>No concerts match your search</h3>
                <p>Try a different artist, or look in past concerts.</p>
                <a class="btn btn-ghost" href="{{ route('concerts.index') }}">Clear filters</a>
            </div>
        @else
            <div class="concert-grid" id="concertGrid">
                @foreach($concerts as $concert)
                    <x-concert-card :concert="$concert" :favourited="in_array($concert->id, $favouriteIds)" />
                @endforeach
            </div>
            {{ $concerts->links() }}
        @endif
    </div>
@endsection
