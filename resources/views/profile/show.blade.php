@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 980px">
            <div class="page-head">
                <div>
                    <h1>{{ $user->name }}</h1>
                    <p>{{ $user->email }} · Member since {{ $user->created_at->format('M Y') }}</p>
                </div>
                <a class="btn btn-ghost" href="{{ route('profile.edit') }}">Edit profile</a>
            </div>

            <div class="stat-grid">
                <a class="stat" href="{{ route('orders.index') }}"><strong>{{ $stats['tickets'] }}</strong><span class="small-label">Tickets</span></a>
                <a class="stat" href="{{ route('orders.index') }}"><strong>{{ $stats['orders'] }}</strong><span class="small-label">Orders</span></a>
                <a class="stat" href="{{ route('concerts.favourites') }}"><strong>{{ $stats['favourites'] }}</strong><span class="small-label">Favourites</span></a>
                <a class="stat" href="{{ route('profile.reviews') }}"><strong>{{ $stats['reviews'] }}</strong><span class="small-label">Reviews</span></a>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Recent orders</h2>
                <a class="btn btn-ghost btn-sm" href="{{ route('orders.index') }}">View all</a>
            </div>
            @forelse($orders as $order)
                @include('orders._row', ['order' => $order])
            @empty
                <div class="empty"><p class="mb-0">No orders yet.</p></div>
            @endforelse

            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <h2 class="mb-0">Recent reviews</h2>
                <a class="btn btn-ghost btn-sm" href="{{ route('profile.reviews') }}">View all</a>
            </div>
            <div class="panel">
                @forelse($reviews as $review)
                    <article class="review">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('concerts.show', $review->concert) }}"><strong>{{ $review->concert->title }}</strong></a>
                            <span class="stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}</span>
                        </div>
                        <p class="mt-1 mb-0 muted">{{ \Illuminate\Support\Str::limit($review->comment, 160) }}</p>
                    </article>
                @empty
                    <p class="muted mb-0">No reviews yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
