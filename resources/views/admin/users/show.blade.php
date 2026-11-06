@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="page">
        <div class="container">
            <a class="back-link" href="{{ route('admin.users.index') }}"><i class="fas fa-arrow-left"></i> Users</a>
            <div class="page-head">
                <div>
                    <h1>{{ $user->name }}</h1>
                    <p>{{ $user->email }} · Joined {{ optional($user->created_at)->format('d M Y') }}</p>
                </div>
                @if($user->is_admin)<span class="status status--low">Admin</span>@endif
            </div>

            <div class="stat-grid">
                <div class="stat"><strong>{{ $user->orders->count() }}</strong><span class="small-label">Orders</span></div>
                <div class="stat"><strong>{{ $user->reviews->count() }}</strong><span class="small-label">Reviews</span></div>
                <div class="stat"><strong>{{ $user->favouriteConcerts->count() }}</strong><span class="small-label">Favourites</span></div>
            </div>

            <section class="panel">
                <h2>Orders</h2>
                @if($user->orders->isEmpty())
                    <p class="muted mb-0">No orders yet.</p>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Order</th><th>Concert</th><th>Tickets</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($user->orders->sortByDesc('created_at') as $order)
                                    <tr>
                                        <td class="mono">{{ $order->reference }}</td>
                                        <td><a href="{{ route('admin.concerts.show', $order->concert) }}">{{ $order->concert->title }}</a></td>
                                        <td class="mono">{{ $order->quantity }}</td>
                                        <td class="mono">${{ number_format($order->total_price, 2) }}</td>
                                        <td><span class="status {{ $order->status === 'cancelled' ? 'status--off' : 'status--ok' }}">{{ $order->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="panel">
                <h2>Reviews</h2>
                @forelse($user->reviews as $review)
                    <article class="review">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('admin.concerts.show', $review->concert) }}"><strong>{{ $review->concert->title }}</strong></a>
                            <span class="stars">{{ str_repeat('★', $review->rating) }}</span>
                        </div>
                        <p class="mt-1 mb-0 muted">{{ $review->comment }}</p>
                    </article>
                @empty
                    <p class="muted mb-0">No reviews yet.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
