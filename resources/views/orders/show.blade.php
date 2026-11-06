@extends('layouts.app')

@section('title', 'Order ' . $order->reference)

@section('content')
    @php
        $concert = $order->concert;
        $cancelled = $order->status === 'cancelled';
        $received = $order->isTransferred();
    @endphp

    <div class="page">
        <div class="container" style="max-width: 980px">
            <a class="back-link" href="{{ route('orders.index') }}"><i class="fas fa-arrow-left"></i> My tickets</a>

            <div class="pass-layout">
                <article class="pass {{ $cancelled ? 'is-void' : '' }}">
                    <div class="pass__art">
                        @if($concert->hero_url)
                            <img src="{{ $concert->hero_url }}" alt="">
                        @endif
                    </div>
                    <div class="pass__title">
                        <h2 class="shout">{{ $concert->artist }}</h2>
                        <p>{{ $concert->venue }}@if($concert->city), {{ $concert->city }}@endif</p>
                    </div>
                    <div class="pass__facts">
                        <div><span>Date</span><strong>{{ $concert->date->format('d M Y') }}</strong></div>
                        <div><span>Doors</span><strong>{{ $concert->doors_time ?: $concert->start_time ?: 'TBC' }}</strong></div>
                        <div><span>Tickets</span><strong>{{ $order->quantity }}</strong></div>
                    </div>
                    <div class="pass__tear"></div>
                    <div class="pass__code">
                        @if($cancelled)
                            <strong>Cancelled</strong>
                        @else
                            <div class="pass__live" id="livePass" data-url="{{ route('orders.pass', $order) }}">
                                <div class="pass__qr" id="passCode" role="img" aria-label="Door code for this ticket"></div>
                                <div class="pass__used" id="passUsed" hidden><i class="fas fa-check"></i> <span>Checked in</span></div>
                                <div class="pass__timer" aria-hidden="true"><span id="passTimer"></span></div>
                                <p class="pass__note" id="passNote">This code changes every 30 seconds</p>
                                <button class="pass__copy" type="button" id="passText" title="Copy the code"></button>
                            </div>
                        @endif
                        <span class="pass__ref">{{ $order->reference }}</span>
                    </div>
                </article>

                <div>
                    <h1>{{ $concert->title }}</h1>
                    <p class="muted">{{ $cancelled ? 'This order was cancelled.' : 'You are going. Show this code at the door.' }}</p>
                    @if($received)
                        <p class="sent-note"><i class="fas fa-gift"></i> Sent to you by {{ optional($order->transferredFrom)->name ?? 'another fan' }} on {{ $order->transferred_at->format('d M Y') }}.</p>
                    @endif

                    <h2 class="h6 mt-4">Your tickets</h2>
                    <ul class="order-facts">
                        @foreach($order->items as $item)
                            <li>
                                <div>
                                    {{ $item->quantity }} × {{ $item->section_name }}
                                    @if($item->seat_label)
                                        <small class="muted d-block">{{ $item->seat_label }}</small>
                                    @endif
                                </div>
                                @unless($received)
                                    <strong class="mono">${{ number_format($item->unit_price * $item->quantity, 2) }}</strong>
                                @endunless
                            </li>
                        @endforeach
                        @unless($received)
                            @if($order->discount > 0)
                                <li><span>Promo {{ optional($order->promoCode)->code }}</span><strong class="mono">-${{ number_format($order->discount, 2) }}</strong></li>
                            @endif
                            <li><span>Total paid</span><strong class="mono">${{ number_format($order->total_price, 2) }}</strong></li>
                            <li>
                                <span>Payment</span>
                                @if($order->paymentCard)
                                    <span class="text-body">{{ $order->paymentCard->brand }} ending in <span class="mono">{{ $order->paymentCard->last_four }}</span></span>
                                @else
                                    <span>Card no longer on file</span>
                                @endif
                            </li>
                            <li><span>Booked</span><span class="text-body">{{ $order->created_at->format('d M Y, H:i') }}</span></li>
                        @endunless
                    </ul>

                    <div class="d-flex flex-wrap gap-2">
                        @if(! $cancelled && $concert->isUpcoming())
                            <a class="btn btn-accent" href="{{ route('concerts.calendar', $concert) }}"><i class="far fa-calendar-plus"></i> Add to calendar</a>
                        @endif
                        <a class="btn btn-ghost" href="{{ route('concerts.show', $concert) }}">View concert</a>
                        @if($order->canBeCancelled())
                            <form action="{{ route('orders.cancel', $order) }}" method="POST"
                                  data-confirm="Cancel this order? Your tickets will be released and refunded.">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-danger-ghost" type="submit">Cancel order</button>
                            </form>
                        @endif
                    </div>

                    @if($order->canBeTransferred())
                        <details class="transfer">
                            <summary><i class="fas fa-paper-plane"></i> Send to a friend</summary>
                            <form action="{{ route('orders.transfer', $order) }}" method="POST"
                                  data-confirm="Send these tickets? They will leave your account and cannot be cancelled or refunded afterwards.">
                                @csrf
                                <label class="form-label" for="transferEmail">Their ConcertHub email</label>
                                <div class="promo-row">
                                    <input class="form-control" id="transferEmail" type="email" name="email" required>
                                    <button class="btn btn-accent" type="submit">Send tickets</button>
                                </div>
                                <p class="muted mt-2 mb-0">The whole order moves to their account. They will not see what you paid.</p>
                            </form>
                        </details>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @unless($cancelled)
        <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
        <script src="{{ asset('js/live-pass.js') }}"></script>
    @endunless
@endpush
