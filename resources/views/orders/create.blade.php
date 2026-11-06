@extends('layouts.app')

@section('title', 'Book ' . $concert->title)
@section('bare', true)
@section('body_class', 'nav-over')

@section('content')
    <header class="event-bar">
        <a class="event-bar__back" href="{{ route('concerts.show', $concert) }}" aria-label="Back to the concert"><i class="fas fa-arrow-left"></i></a>
        <div class="event-bar__text">
            <strong>{{ $concert->title }}</strong>
            <span>{{ $concert->date->format('D, d M Y') }} · {{ $concert->venue }}</span>
        </div>
        <div class="event-bar__end">
            @include('orders._steps', ['current' => 1])
        </div>
    </header>

    <form class="checkout" action="{{ route('orders.store', $concert) }}" method="POST" id="bookingForm">
        @csrf
        <div class="checkout__form">
            <div>
                <h1>Choose your tickets</h1>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <section class="check-step">
                    @foreach($concert->sections as $section)
                        @php $remaining = min($section->remaining(), 100); @endphp
                        <div class="tier-row" data-tier data-name="{{ $section->name }}" data-price="{{ $section->price }}">
                            <span class="tier-swatch" style="background: {{ $section->color }}"></span>
                            <div>
                                <h4>{{ $section->name }}</h4>
                                <div class="muted">
                                    <span class="price text-body">${{ number_format($section->price, 2) }}</span>
                                    · {{ $section->isSeated() ? 'Best available seats' : 'Standing' }}
                                    · {{ $section->remaining() ? number_format($section->remaining()) . ' left' : 'Sold out' }}
                                </div>
                            </div>
                            <div class="stepper">
                                <button type="button" data-step="-1" aria-label="Remove one {{ $section->name }} ticket">−</button>
                                <input type="number" name="tickets[{{ $section->id }}]" min="0" max="{{ $remaining }}"
                                       value="{{ old('tickets.' . $section->id, 0) }}"
                                       aria-label="{{ $section->name }} quantity" {{ $remaining ? '' : 'readonly' }}>
                                <button type="button" data-step="1" aria-label="Add one {{ $section->name }} ticket">+</button>
                            </div>
                        </div>
                    @endforeach
                </section>

                @include('orders._card_list')
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

                <div id="summaryLines"><p class="muted mb-0">No tickets selected yet.</p></div>
                <div class="summary-total">
                    <span>Total</span>
                    <strong id="summaryTotal">$0.00</strong>
                </div>
                <button class="btn btn-accent w-100 mt-3" type="submit" id="confirmButton" disabled>Confirm booking</button>

                <ul class="promise">
                    <li><i class="fas fa-check"></i> The price you see is the price you pay</li>
                    <li><i class="fas fa-check"></i> Cancel for a refund any time before the concert date</li>
                </ul>
            </div>
        </aside>
    </form>

    @include('orders._add_card')
@endsection

@push('scripts')
<script>
(function () {
    const tiers = Array.from(document.querySelectorAll('[data-tier]'));
    const lines = document.getElementById('summaryLines');
    const total = document.getElementById('summaryTotal');
    const confirmButton = document.getElementById('confirmButton');
    const money = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

    function refresh() {
        let sum = 0;
        let count = 0;
        lines.textContent = '';

        tiers.forEach(function (tier) {
            const input = tier.querySelector('input');
            const max = Number(input.max);
            const quantity = Math.min(Math.max(parseInt(input.value, 10) || 0, 0), max);
            input.value = quantity;

            const buttons = tier.querySelectorAll('[data-step]');
            buttons[0].disabled = quantity <= 0;
            buttons[1].disabled = quantity >= max;

            if (quantity > 0) {
                const cost = quantity * Number(tier.dataset.price);
                sum += cost;
                count += quantity;

                const line = document.createElement('div');
                line.className = 'summary-line';
                const label = document.createElement('span');
                label.textContent = quantity + ' × ' + tier.dataset.name;
                const amount = document.createElement('span');
                amount.className = 'mono';
                amount.textContent = money.format(cost);
                line.append(label, amount);
                lines.append(line);
            }
        });

        if (!count) {
            lines.innerHTML = '<p class="muted mb-0">No tickets selected yet.</p>';
        }

        total.textContent = money.format(sum);
        confirmButton.disabled = !count || !document.querySelector('input[name="payment_card_id"]:checked');
        confirmButton.textContent = count ? 'Confirm ' + count + (count === 1 ? ' ticket' : ' tickets') : 'Confirm booking';
    }

    tiers.forEach(function (tier) {
        const input = tier.querySelector('input');
        input.addEventListener('input', refresh);
        tier.querySelectorAll('[data-step]').forEach(function (button) {
            button.addEventListener('click', function () {
                input.value = (parseInt(input.value, 10) || 0) + Number(button.dataset.step);
                refresh();
            });
        });
    });

    document.getElementById('cardList').addEventListener('change', refresh);

    document.addEventListener('card-added', refresh);

    refresh();
})();
</script>
@endpush
