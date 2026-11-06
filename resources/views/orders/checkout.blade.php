@extends('layouts.app')

@section('title', 'Checkout · ' . $concert->title)
@section('bare', true)
@section('body_class', 'nav-over')

@section('content')
    @php $total = $seats->sum(function ($seat) { return $seat->section->price; }); @endphp

    <header class="event-bar">
        <span class="brand text-white">Concert<span>Hub</span></span>
        <div class="event-bar__end">
            @include('orders._steps', ['current' => 2])
            <span class="hold-timer" role="timer" title="Your seats are held until this runs out">
                <i class="far fa-clock"></i>
                <span data-countdown="{{ $heldUntil->toIso8601String() }}" data-countdown-style="clock" data-expired-url="{{ route('seats.show', $concert) }}">{{ \App\Services\SeatHoldService::MINUTES }}:00</span>
            </span>
        </div>
    </header>

    <form class="checkout" action="{{ route('seats.purchase', $concert) }}" method="POST">
        @csrf
        <div class="checkout__form">
            <div>
                <h1>Checkout</h1>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <section class="check-step">
                    <div class="check-step__head"><h2>Contact</h2></div>
                    <p class="mb-0">{{ auth()->user()->name }}<br><span class="muted">{{ auth()->user()->email }}. Your tickets will appear in My Tickets.</span></p>
                </section>

                @include('orders._card_list')

                <section class="check-step">
                    <div class="check-step__head"><h2>Promo code</h2></div>
                    <div class="promo-row">
                        <input class="form-control" type="text" id="promoInput" aria-label="Promo code" autocomplete="off" maxlength="32">
                        <button class="btn btn-ghost" type="button" id="promoApply" data-url="{{ route('seats.promo', $concert) }}">Apply</button>
                    </div>
                    <p class="promo-message" id="promoMessage" role="status" hidden></p>
                    <input type="hidden" name="promo_code" id="promoCode">
                </section>

                <button class="btn btn-accent w-100 mt-3" style="padding: .85rem" type="submit" id="confirmButton" {{ $paymentCards->isEmpty() ? 'disabled' : '' }}>Pay and confirm · <span id="payAmount">${{ number_format($total, 2) }}</span></button>
                <p class="pay-secure"><i class="fas fa-lock"></i> Demo checkout. No real payment is taken.</p>
            </div>
        </div>

        <aside class="checkout__order" aria-label="Your order">
            <div>
                <div class="order-event">
                    @if($concert->hero_url)
                        <img src="{{ $concert->hero_url }}" alt="">
                    @endif
                    <div>
                        <strong>{{ $concert->title }}</strong>
                        <span>{{ $concert->date->format('D, d M Y') }}@if($concert->start_time), {{ $concert->start_time }}@endif</span>
                        <span>{{ $concert->venue }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>{{ $seats->count() }} {{ \Illuminate\Support\Str::plural('seat', $seats->count()) }}</strong>
                    <button class="btn-link-plain" type="submit" form="releaseSeats">Change seats</button>
                </div>
                @foreach($seats as $seat)
                    <div class="order-seat">
                        <span class="tier-swatch" style="background: {{ $seat->section->color }}"></span>
                        <span>{{ $seat->label }}<small>{{ $seat->section->name }}</small></span>
                        <span class="mono">${{ number_format($seat->section->price, 2) }}</span>
                    </div>
                @endforeach

                <div class="summary-line" id="discountRow" hidden>
                    <span id="discountLabel"></span>
                    <span class="mono" id="discountAmount"></span>
                </div>

                <div class="summary-total">
                    <span>Total</span>
                    <strong id="orderTotal">${{ number_format($total, 2) }}</strong>
                </div>

                <ul class="promise">
                    <li><i class="fas fa-check"></i> The price you see is the price you pay</li>
                    <li><i class="fas fa-check"></i> Cancel for a refund any time before the concert date</li>
                </ul>
            </div>
        </aside>
    </form>

    <form id="releaseSeats" action="{{ route('seats.release', $concert) }}" method="POST" hidden>
        @csrf
        @method('DELETE')
    </form>

    @include('orders._add_card')
@endsection

@push('scripts')
<script>
document.addEventListener('card-added', function () {
    document.getElementById('confirmButton').disabled = false;
});

(function () {
    const input = document.getElementById('promoInput');
    const button = document.getElementById('promoApply');
    const message = document.getElementById('promoMessage');
    const money = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    const token = document.querySelector('meta[name="csrf-token"]').content;

    function say(text, good) {
        message.hidden = false;
        message.textContent = text;
        message.classList.toggle('is-good', good);
    }

    function setPrice(promo) {
        document.getElementById('promoCode').value = promo.code;
        document.getElementById('discountRow').hidden = false;
        document.getElementById('discountLabel').textContent = `${promo.code}, ${promo.label}`;
        document.getElementById('discountAmount').textContent = `-${money.format(promo.discount)}`;
        document.getElementById('orderTotal').textContent = money.format(promo.total);
        document.getElementById('payAmount').textContent = money.format(promo.total);
    }

    function apply() {
        if (!input.value.trim()) {
            return;
        }

        button.disabled = true;

        fetch(button.dataset.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ code: input.value }),
        })
            .then((response) => response.json().then((body) => ({ ok: response.ok, body })))
            .then(({ ok, body }) => {
                if (!ok) {
                    say(body.message || 'That code could not be applied.', false);
                    return;
                }
                setPrice(body);
                say(`${body.code} applied. You save ${money.format(body.discount)}.`, true);
            })
            .catch(() => say('That code could not be checked. Try again.', false))
            .finally(() => {
                button.disabled = false;
            });
    }

    button.addEventListener('click', apply);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            apply();
        }
    });
})();
</script>
@endpush
