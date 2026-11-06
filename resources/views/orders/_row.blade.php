<a class="order-row" href="{{ route('orders.show', $order) }}">
    <div class="order-row__date">
        <strong>{{ $order->concert->date->format('d M') }}</strong>
        <span>{{ $order->concert->date->format('Y') }}</span>
    </div>
    <div class="order-row__thumb">
        @if($order->concert->photo_url)
            <img src="{{ $order->concert->photo_url }}" alt="" loading="lazy">
        @else
            <div class="poster-fallback">{{ mb_strtoupper(mb_substr($order->concert->artist, 0, 1)) }}</div>
        @endif
    </div>
    <div>
        <h3>{{ $order->concert->title }}</h3>
        <div class="muted">{{ $order->concert->venue }} · {{ $order->quantity }} {{ \Illuminate\Support\Str::plural('ticket', $order->quantity) }}</div>
    </div>
    <div class="text-sm-end">
        @if($order->isTransferred())
            <div class="muted mb-1">Sent to you</div>
        @else
            <div class="price mb-1">${{ number_format($order->total_price, 2) }}</div>
        @endif
        @if($order->status === 'cancelled')
            <span class="status status--off">Cancelled</span>
        @elseif($order->concert->isUpcoming())
            <span class="status status--ok">Confirmed</span>
        @else
            <span class="status">Attended</span>
        @endif
    </div>
</a>
