@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                    <p>
                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                    </p>
                    <div class="btn-group-vertical w-100" role="group">
                        <a href="{{ route('profile.orders') }}" class="btn btn-outline-primary">My Orders</a>
                        <a href="{{ route('profile.reviews') }}" class="btn btn-outline-primary">My Reviews</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <h1 class="mb-4">Profile</h1>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            @forelse($orders->take(5) as $order)
                                <div class="mb-3 pb-3 border-bottom">
                                    <h6><a href="{{ route('concerts.show', $order->concert) }}">{{ $order->concert->title }}</a></h6>
                                    <small>{{ $order->quantity }} tickets - ${{ number_format($order->total_price, 2) }}</small>
                                    <br>
                                    <span class="badge bg-{{ $order->status === 'confirmed' ? 'success' : 'danger' }}">{{ ucfirst($order->status) }}</span>
                                </div>
                            @empty
                                <p class="text-muted">No orders yet.</p>
                            @endforelse
                            @if($orders->count() > 5)
                                <a href="{{ route('profile.orders') }}" class="btn btn-sm btn-outline-primary">View All Orders</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Reviews</h5>
                        </div>
                        <div class="card-body">
                            @forelse($reviews->take(5) as $review)
                                <div class="mb-3 pb-3 border-bottom">
                                    <h6><a href="{{ route('concerts.show', $review->concert) }}">{{ $review->concert->title }}</a></h6>
                                    <div>
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <span class="text-warning">★</span>
                                        @endfor
                                    </div>
                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                </div>
                            @empty
                                <p class="text-muted">No reviews yet.</p>
                            @endforelse
                            @if($reviews->count() > 5)
                                <a href="{{ route('profile.reviews') }}" class="btn btn-sm btn-outline-primary">View All Reviews</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
