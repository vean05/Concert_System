@extends('layouts.app')

@section('title', 'Choose seats · ' . $concert->title)
@section('bare', true)
@section('body_class', 'nav-over')

@push('head')
    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
        }
    }
    </script>
@endpush

@section('content')
    <header class="event-bar">
        <a class="event-bar__back" href="{{ route('concerts.show', $concert) }}" aria-label="Back to the concert"><i class="fas fa-arrow-left"></i></a>
        @if($concert->hero_url)
            <img src="{{ $concert->hero_url }}" alt="">
        @endif
        <div class="event-bar__text">
            <strong>{{ $concert->title }}</strong>
            <span>{{ $concert->date->format('D, d M Y') }} · {{ $venue['name'] }}, {{ $venue['city'] }}</span>
        </div>
        <div class="event-bar__end">
            @include('orders._steps', ['current' => 1])
        </div>
    </header>

    <div class="seat-picker" id="seatPicker"
         data-data-url="{{ route('seats.data', $concert) }}"
         data-status-url="{{ route('seats.status', $concert) }}"
         data-hold-url="{{ route('seats.hold', $concert) }}"
         data-party-url="{{ route('seats.party.start', $concert) }}"
         data-party-code="{{ $partyCode }}"
         @if(auth()->user()->is_admin) data-sales-url="{{ route('admin.concerts.sales-map', $concert) }}" @endif>

        <aside class="seat-panel" aria-label="Tickets">
            <div class="seat-filters" id="tierList">
                <button class="seat-pill" type="button" id="quantityButton" aria-pressed="true"><span id="quantityLabel">2 tickets</span> <i class="fas fa-chevron-down" style="background: none; width: auto; height: auto; font-size: .65rem"></i></button>
            </div>

            <label class="seat-range" id="priceBox" hidden>
                <span>Budget</span>
                <input type="range" id="priceLimit" step="1" aria-label="Highest price per ticket">
                <output id="priceOutput"></output>
            </label>

            <div class="seat-tools">
                <button class="seat-tool" type="button" id="pickForMe"><i class="fas fa-wand-magic-sparkles"></i> Pick for me</button>
                <button class="seat-tool" type="button" id="partyButton"><i class="fas fa-user-group"></i> Invite friends</button>
            </div>

            <section class="party" id="partyBox" aria-label="Your group" hidden>
                <div class="party__head">
                    <strong>Your group</strong>
                    <button type="button" id="partyLeave">Leave</button>
                </div>
                <ul class="party__members" id="partyMembers"></ul>
                <div class="party__link">
                    <input type="text" id="partyLink" readonly aria-label="Group link">
                    <button class="btn btn-ghost btn-sm" type="button" id="partyCopy">Copy link</button>
                </div>
                <p>Everyone sees each other's picks and pays for their own seats. A pick reserves nothing until that person continues to payment.</p>
            </section>

            <div class="listing-head">
                <span id="listingCount">Loading tickets</span>
                <button class="listing-sort" type="button" id="listingSortButton" title="Change the order"><i class="fas fa-arrow-down-wide-short"></i> <span id="listingSort">Best view</span></button>
            </div>

            <button class="compare-bar" type="button" id="compareBar" hidden></button>

            <ul class="listings" id="listings"></ul>

            <footer class="seat-cart">
                <ul class="seat-cart__list" id="selectionList" hidden></ul>
                <div class="seat-cart__total">
                    <span>Total, the price you'll pay</span>
                    <strong id="selectionTotal">$0.00</strong>
                </div>
                <button class="btn btn-accent w-100" type="button" id="continueButton" disabled>Choose your seats</button>
                <p class="seat-cart__note">Seats are held for {{ \App\Services\SeatHoldService::MINUTES }} minutes while you pay. {{ $venue['accuracy_note'] }} The crowd in the 3D view is there for atmosphere, so check seat colours for what is free.</p>
            </footer>
        </aside>

        <div class="seat-stage" id="seatCanvas">
            <p class="seat-loading" id="seatLoading" role="status">Setting the stage…</p>

            <div class="seat-toolbar">
                <button type="button" id="homeButton" aria-label="Show the whole arena" title="Show the whole arena"><i class="fas fa-expand"></i></button>
                <button type="button" id="tourButton" aria-label="Tour the venue" title="Tour the venue"><i class="fas fa-video"></i></button>
            </div>

            <div class="live-bar" id="liveBar" hidden>
                <button class="live-bar__play" type="button" id="livePlay" aria-pressed="false" aria-label="Play or pause a song"><i class="fas fa-play"></i> <span id="liveSong">Play a song</span></button>
                <span class="live-eq" id="liveEq" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
                <button type="button" id="liveNext" aria-label="Next song" title="Next song"><i class="fas fa-forward-step"></i></button>
                <button type="button" id="livePhones" aria-pressed="false" aria-label="Phone lights"><i class="fas fa-mobile-screen"></i> Phone lights</button>
                <button type="button" id="liveWave" aria-label="Start a wave"><i class="fas fa-water"></i> Wave</button>
                <button type="button" id="liveConfetti" aria-label="Fire confetti"><i class="fas fa-burst"></i> Confetti</button>
            </div>
            <p class="live-credit" id="liveCredit" hidden>Previews from Apple Music</p>

            <div class="map-scale" id="mapScale" hidden>
                <span id="mapScaleLow"></span>
                <i></i>
                <span id="mapScaleHigh"></span>
            </div>

            <div class="map-modes" id="mapModes" role="group" aria-label="Colour the seats by">
                <button type="button" data-mode="tiers" aria-pressed="true">Tiers</button>
                <button type="button" data-mode="price" aria-pressed="false">Price</button>
                <button type="button" data-mode="view" aria-pressed="false">View</button>
                @if(auth()->user()->is_admin)
                    <button type="button" data-mode="sales" aria-pressed="false">Sales</button>
                @endif
            </div>

            <div class="seat-legend" aria-label="Seat colours">
                <span><i class="is-mine"></i> Your seats</span>
                <span><i class="is-held"></i> Held</span>
                <span><i class="is-sold"></i> Sold</span>
            </div>

            <p class="seat-hint">Click any seat to see the stage from there</p>

            <div class="seat-view-bar" id="seatViewBar" hidden>
                <button class="btn btn-ghost-light btn-sm" type="button" id="seatViewPrev" aria-label="Seat to the left"><i class="fas fa-chevron-left"></i></button>
                <div class="seat-view-bar__text">
                    <strong id="seatViewLabel"></strong>
                    <span id="seatViewPrice"></span>
                </div>
                <button class="btn btn-ghost-light btn-sm" type="button" id="seatViewNext" aria-label="Seat to the right"><i class="fas fa-chevron-right"></i></button>
                <button class="btn btn-white btn-sm" type="button" id="seatViewToggle"></button>
                <button class="btn btn-ghost-light btn-sm" type="button" id="exitSeatView">Back to arena</button>
            </div>

            <aside class="sales-panel" id="salesPanel" aria-label="Sales by section" hidden></aside>

            <div class="seat-tooltip" id="seatTooltip" hidden></div>
            <div class="seat-notice" id="seatNotice" role="status" hidden></div>
        </div>

        <div class="qty-sheet" id="qtySheet" hidden>
            <div class="qty-sheet__box" role="dialog" aria-labelledby="qtyTitle">
                <h2 id="qtyTitle">How many tickets?</h2>
                <p>We'll show seats you can sit in together.</p>
                <div class="qty-grid" id="qtyGrid"></div>
            </div>
        </div>

        <div class="qty-sheet" id="pickSheet" hidden>
            <div class="qty-sheet__box" role="dialog" aria-labelledby="pickTitle">
                <h2 id="pickTitle">What matters most?</h2>
                <p id="pickIntro"></p>
                <div class="pick-options" id="pickOptions"></div>
                <button class="sheet-close" type="button" data-close-sheet>Not now</button>
            </div>
        </div>

        <div class="qty-sheet" id="compareSheet" hidden>
            <div class="qty-sheet__box compare-box" role="dialog" aria-labelledby="compareTitle">
                <h2 id="compareTitle">Compare seats</h2>
                <div class="compare-grid" id="compareGrid"></div>
                <button class="sheet-close" type="button" data-close-sheet>Close</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('js/seat-map.js') }}"></script>
@endpush
