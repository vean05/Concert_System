@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $concert->title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('concerts.index') }}" class="btn btn-secondary">Back to Concerts</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Concert Details</h5>
                    <p><strong>Artist:</strong> {{ $concert->artist }}</p>
                    <p><strong>Venue:</strong> {{ $concert->venue }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($concert->date)->format('F d, Y \a\t h:i A') }}</p>
                    <p><strong>Price per Ticket:</strong> ${{ number_format($concert->ticket_price, 2) }}</p>
                    
                    @php
                        $bookedTickets = $concert->orders()
                            ->where('status', 'confirmed')
                            ->sum('quantity');
                        $availableTickets = $concert->total_ticket - $bookedTickets;
                    @endphp
                    <p><strong>Available Tickets:</strong> {{ $availableTickets }} / {{ $concert->total_ticket }}</p>
                    
                    <hr>
                    <h6>Description</h6>
                    <p>{{ $concert->description }}</p>
                    
                    @auth
                        @if(auth()->user()->role === 'admin' && auth()->user()->id === $concert->created_by)
                            <div class="mt-3">
                                <a href="{{ route('concerts.edit', $concert) }}" class="btn btn-warning">Edit Concert</a>
                                <form action="{{ route('concerts.destroy', $concert) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this concert?')">Delete Concert</button>
                                </form>
                            </div>
                        @elseif(auth()->user()->role === 'user' && $availableTickets > 0)
                            <div class="mt-3">
                                <a href="{{ route('orders.create', $concert) }}" class="btn btn-success btn-lg">Book Tickets</a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="card">
                <div class="card-header">
                    <h5>Reviews ({{ $concert->reviews()->count() }})</h5>
                </div>
                <div class="card-body">
                    @auth
                        @if(auth()->user()->orders()->where('concert_id', $concert->id)->where('status', 'confirmed')->exists() && !auth()->user()->reviews()->where('concert_id', $concert->id)->exists())
                            <div class="mb-3">
                                <a href="{{ route('reviews.create', $concert) }}" class="btn btn-primary btn-sm">Write a Review</a>
                            </div>
                        @endif
                    @endauth

                    @forelse($concert->reviews()->latest()->get() as $review)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>{{ $review->user->name }}</h6>
                                    <div class="mb-2">
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <span class="text-warning">★</span>
                                        @endfor
                                        <span class="ms-2 text-muted">({{ $review->rating }}/5)</span>
                                    </div>
                                </div>
                                @auth
                                    @if(auth()->user()->id === $review->user_id)
                                        <div>
                                            <a href="{{ route('reviews.edit', $review) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">Delete</button>
                                            </form>
                                        </div>
                                    @endif
                                @endauth
                            </div>
                            <p>{{ $review->comment }}</p>
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <p class="text-muted">No reviews yet. Be the first to review!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Created By</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ $concert->creator->name }}</strong></p>
                    <p class="text-muted">{{ $concert->creator->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
