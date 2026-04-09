@extends('layouts.app')

@section('title', $concert->title . ' - ConcertHub')

@section('content')
<style>
    .concert-hero {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        border-radius: 12px;
    }

    .concert-hero h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .concert-hero .artist-name {
        font-size: 1.3rem;
        opacity: 0.95;
    }

    .detail-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(100, 116, 139, 0.1);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);
        margin-bottom: 2rem;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .detail-item {
        display: flex;
        align-items: flex-start;
    }

    .detail-item-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .detail-item-content h6 {
        color: #4a5568;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
        margin: 0 0 0.3rem 0;
    }

    .detail-item-content p {
        color: #1a1a2e;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .description-section {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(100, 116, 139, 0.1);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);
        margin-bottom: 2rem;
    }

    .description-section h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 1.5rem;
    }

    .description-section p {
        color: #4a5568;
        line-height: 1.7;
        font-size: 1rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .action-buttons a,
    .action-buttons button {
        flex: 1;
        min-width: 150px;
        padding: 0.9rem 1.5rem;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }

    .btn-book {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .btn-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-edit {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
    }

    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
    }

    .btn-back:hover {
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .reviews-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .reviews-header h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .review-count-badge {
        background: #7c3aed;
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .btn-write-review {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-write-review:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
        color: white;
        text-decoration: none;
    }

    .review-item {
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem 0;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .review-user {
        flex-grow: 1;
    }

    .review-user h6 {
        color: #2c3e50;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
    }

    .review-rating {
        color: #ffc107;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .review-actions {
        display: flex;
        gap: 0.5rem;
    }

    .review-actions a,
    .review-actions button {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        border: none;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }

    .review-edit-btn {
        background: #ffc107;
        color: white;
    }

    .review-edit-btn:hover {
        background: #ff9800;
    }

    .review-delete-btn {
        background: #dc3545;
        color: white;
    }

    .review-delete-btn:hover {
        background: #c82333;
    }

    .review-comment {
        color: #555;
        line-height: 1.6;
        margin: 1rem 0;
    }

    .review-time {
        color: #999;
        font-size: 0.85rem;
    }

    .creator-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .creator-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        margin: 0 auto 1rem;
    }

    .creator-card h6 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .creator-card p {
        color: #666;
        font-size: 0.9rem;
        margin: 0;
    }

    .empty-reviews {
        text-align: center;
        padding: 2rem;
        color: #666;
    }

    .empty-reviews i {
        font-size: 2rem;
        color: #7c3aed;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .detail-row {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons a,
        .action-buttons button {
            min-width: unset;
        }

        .concert-hero h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="container" style="padding: 2rem 0;">
    <!-- Back Button -->
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('concerts.index') }}" class="btn-back" style="display: inline-block; padding: 0.6rem 1.5rem;">
            <i class="fas fa-arrow-left"></i> Back to Concerts
        </a>
    </div>

    <!-- Concert Hero Section -->
    <div class="concert-hero">
        <div class="container">
            <h1><i class="fas fa-music"></i> {{ $concert->title }}</h1>
            <p class="artist-name">
                <i class="fas fa-user-music"></i> {{ $concert->artist }}
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Concert Details Card -->
            <div class="detail-card">
                <h3 style="font-size: 1.3rem; font-weight: 700; color: #2c3e50; margin-bottom: 2rem;">
                    <i class="fas fa-info-circle"></i> Event Details
                </h3>

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-item-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="detail-item-content">
                            <h6>Date & Time</h6>
                            <p>{{ \Carbon\Carbon::parse($concert->date)->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-item-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="detail-item-content">
                            <h6>Venue</h6>
                            <p>{{ $concert->venue }}</p>
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-item-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="detail-item-content">
                            <h6>Price per Ticket</h6>
                            <p>${{ number_format($concert->ticket_price, 2) }}</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-item-icon">
                            @php
                                $bookedTickets = $concert->orders()
                                    ->where('status', 'confirmed')
                                    ->sum('quantity');
                                $availableTickets = $concert->total_ticket - $bookedTickets;
                            @endphp
                            @if($availableTickets > 0)
                                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                            @else
                                <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                            @endif
                        </div>
                        <div class="detail-item-content">
                            <h6>Available Tickets</h6>
                            <p>
                                @if($availableTickets > 0)
                                    {{ $availableTickets }} of {{ $concert->total_ticket }} left
                                @else
                                    <span style="color: #dc3545;">Sold Out</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if($concert->seating_areas)
                <div class="detail-row">
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-item-icon">
                            <i class="fas fa-chair"></i>
                        </div>
                        <div class="detail-item-content">
                            <h6>Seating Areas</h6>
                            <div style="display: flex; gap: 1rem; margin-top: 0.8rem; flex-wrap: wrap;">
                                @php
                                    $seatingAreas = json_decode($concert->seating_areas, true) ?? [];
                                @endphp
                                @forelse($seatingAreas as $area)
                                    <div style="background: rgba(124, 58, 237, 0.1); border: 1px solid rgba(124, 58, 237, 0.3); border-radius: 8px; padding: 0.6rem 1rem; font-weight: 500; color: #7c3aed;">
                                        <i class="fas fa-tag" style="margin-right: 0.4rem;"></i>{{ $area }}
                                    </div>
                                @empty
                                    <p style="color: #4a5568;">General seating available</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Description Section -->
            <div class="description-section">
                <h3><i class="fas fa-align-left"></i> Description</h3>
                <p>{{ $concert->description }}</p>

                <!-- Admin Actions -->
                @auth
                    @if(auth()->user()->role === 'admin' && auth()->user()->id === $concert->created_by)
                        <div class="action-buttons">
                            <a href="{{ route('concerts.edit', $concert) }}" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit Concert
                            </a>
                            <form action="{{ route('concerts.destroy', $concert) }}" method="POST" style="flex: 1; min-width: 150px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete" style="width: 100%; padding: 0.9rem;" onclick="return confirm('Are you sure you want to delete this concert?')">
                                    <i class="fas fa-trash"></i> Delete Concert
                                </button>
                            </form>
                        </div>
                    @elseif(auth()->user()->role === 'user' && $availableTickets > 0)
                        <div class="action-buttons">
                            <a href="{{ route('orders.create', $concert) }}" class="btn-book">
                                <i class="fas fa-ticket-alt"></i> Book Tickets Now
                            </a>
                        </div>
                    @endif
                @endauth

                <!-- Guest Booking Prompt -->
                @guest
                    <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); color: #2e7d32; padding: 1.5rem; border-radius: 8px; margin-top: 1.5rem;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Want to book?</strong> 
                        <a href="{{ route('login') }}" style="color: #1b5e20; font-weight: bold;">Sign in</a> or 
                        <a href="{{ route('register') }}" style="color: #1b5e20; font-weight: bold;">create an account</a> to purchase tickets.
                    </div>
                @endguest
            </div>

            <!-- Reviews Section -->
            <div class="reviews-section">
                <div class="reviews-header">
                    <div>
                        <h3>Reviews <span class="review-count-badge">{{ $concert->reviews()->count() }}</span></h3>
                    </div>
                    @auth
                        @if(auth()->user()->orders()->where('concert_id', $concert->id)->where('status', 'confirmed')->exists() && !auth()->user()->reviews()->where('concert_id', $concert->id)->exists())
                            <a href="{{ route('reviews.create', $concert) }}" class="btn-write-review">
                                <i class="fas fa-star"></i> Write a Review
                            </a>
                        @endif
                    @endauth
                </div>

                @forelse($concert->reviews()->latest()->get() as $review)
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-user">
                                <h6><i class="fas fa-user-circle"></i> {{ $review->user->name }}</h6>
                                <div class="review-rating">
                                    @for($i = 0; $i < $review->rating; $i++)
                                        <i class="fas fa-star"></i>
                                    @endfor
                                    <span style="color: #666; margin-left: 0.5rem;">({{ $review->rating }}/5)</span>
                                </div>
                            </div>
                            @auth
                                @if(auth()->user()->id === $review->user_id)
                                    <div class="review-actions">
                                        <a href="{{ route('reviews.edit', $review) }}" class="review-edit-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="review-delete-btn" onclick="return confirm('Delete this review?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </div>
                        <p class="review-comment">{{ $review->comment }}</p>
                        <small class="review-time">
                            <i class="fas fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                        </small>
                    </div>
                @empty
                    <div class="empty-reviews">
                        <i class="fas fa-comments"></i>
                        <p>No reviews yet. <strong>Be the first to review this concert!</strong></p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Creator Info -->
            <div class="creator-card">
                <div class="creator-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h6>Organized by</h6>
                <p>{{ $concert->creator->name }}</p>
                <p style="font-size: 0.8rem; color: #999;">{{ $concert->creator->email }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
