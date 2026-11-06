@extends('layouts.app')

@section('title', $concert->title)

@section('content')
    <div class="page">
        <div class="container">
            <a class="back-link" href="{{ route('admin.concerts.index') }}"><i class="fas fa-arrow-left"></i> Concerts</a>
            <div class="page-head">
                <div>
                    <h1>{{ $concert->title }}</h1>
                    @unless($concert->is_published)
                        <span class="status status--low">Hidden from the public site</span>
                    @endunless
                    <p>{{ $concert->artist }} · {{ $concert->date->format('d M Y') }} · {{ $concert->venue }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-ghost" href="{{ route('concerts.show', $concert) }}">View page</a>
                    @if($concert->hasSeatMap() && $concert->is_published && $concert->canBeBooked())
                        <a class="btn btn-ghost" href="{{ route('seats.show', [$concert, 'map' => 'sales']) }}">Sales map</a>
                    @endif
                    <a class="btn btn-accent" href="{{ route('admin.concerts.edit', $concert) }}">Edit</a>
                    <form action="{{ route('admin.concerts.delete', $concert) }}" method="POST"
                          data-confirm="Delete {{ $concert->title }}? This also removes its orders and reviews.">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger-ghost" type="submit">Delete</button>
                    </form>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat"><strong>{{ number_format($concert->ticketsSold()) }}</strong><span class="small-label">of {{ number_format($concert->total_ticket) }} sold</span></div>
                <div class="stat"><strong>${{ number_format($concert->ticket_price, 2) }}</strong><span class="small-label">From price</span></div>
                <div class="stat"><strong>{{ $concert->reviews->count() }}</strong><span class="small-label">Reviews</span></div>
            </div>

            <section class="panel">
                <h2>Ticket types</h2>
                @foreach($concert->sections as $section)
                    @php $percent = $section->capacity ? round($section->soldCount() / $section->capacity * 100) : 100; @endphp
                    <div class="tier-row" style="--swatch: {{ $section->color }}">
                        <span class="tier-swatch" style="background: {{ $section->color }}"></span>
                        <div>
                            <h4>{{ $section->name }}</h4>
                            <div class="muted">{{ ucfirst($section->type) }} · {{ number_format($section->soldCount()) }} of {{ number_format($section->capacity) }} sold</div>
                            <div class="meter" aria-hidden="true"><span style="width: {{ $percent }}%"></span></div>
                        </div>
                        <span class="price">${{ number_format($section->price, 2) }}</span>
                    </div>
                @endforeach
            </section>

            <section class="panel">
                <h2>Orders</h2>
                @if($orders->isEmpty())
                    <p class="muted mb-0">No orders yet.</p>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Order</th><th>Customer</th><th>Tickets</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="mono">{{ $order->reference }}</td>
                                        <td><a href="{{ route('admin.users.show', $order->user) }}">{{ $order->user->name }}</a></td>
                                        <td class="mono">{{ $order->quantity }}</td>
                                        <td class="mono">${{ number_format($order->total_price, 2) }}</td>
                                        <td><span class="status {{ $order->status === 'cancelled' ? 'status--off' : 'status--ok' }}">{{ $order->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $orders->links() }}
                @endif
            </section>
        </div>
    </div>
@endsection
