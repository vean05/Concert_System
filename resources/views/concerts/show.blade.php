@extends('layouts.app')

@section('title', $concert->title . ' - ConcertHub')

@section('content')
<style>
    .concert-hero {
        background: @if($concert->image_path) linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.4) 100%), url('{{ asset('storage/' . $concert->image_path) }}') center/cover no-repeat @else linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%) @endif;
        color: white;
        padding: 6rem 0;
        margin-bottom: 3rem;
        border-radius: 0;
        position: relative;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .concert-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(ellipse at center, transparent 0%, rgba(0,0,0,0.3) 100%);
        pointer-events: none;
    }

    .concert-hero-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
    }

    .concert-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        text-shadow: 0 4px 15px rgba(0,0,0,0.5);
        line-height: 1.2;
    }

    .concert-hero .artist-name {
        font-size: 1.8rem;
        opacity: 0.95;
        text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        margin-bottom: 2rem;
    }

    .concert-info-badge {
        display: inline-flex;
        gap: 2rem;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 50px;
        padding: 1rem 2rem;
        margin-bottom: 2rem;
    }

    .badge-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 1rem;
    }

    .badge-divider {
        width: 1px;
        height: 24px;
        background: rgba(255,255,255,0.3);
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(91, 163, 192, 0.12);
        margin-bottom: 2rem;
        border: 1px solid rgba(91, 163, 192, 0.1);
        transition: all 0.3s ease;
    }

    .detail-card:hover {
        box-shadow: 0 16px 48px rgba(91, 163, 192, 0.18);
    }

    .detail-list {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .detail-list-item {
        display: flex;
        gap: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e8e8ed;
    }

    .detail-list-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .detail-list-label {
        font-weight: 700;
        color: #5BA3C0;
        font-size: 1rem;
        min-width: 120px;
        text-transform: capitalize;
    }

    .detail-list-value {
        color: #1a1a2e;
        font-size: 1rem;
        line-height: 1.6;
        flex: 1;
    }

    /* 右侧侧边栏 */
    .booking-sidebar {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(91, 163, 192, 0.12);
        border: 1px solid rgba(91, 163, 192, 0.1);
        height: fit-content;
        position: sticky;
        top: 20px;
    }

    .booking-sidebar h4 {
        color: #666;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .booking-price {
        font-size: 2rem;
        font-weight: 800;
        color: #5BA3C0;
        margin-bottom: 1.5rem;
    }

    .booking-price span {
        font-size: 1rem;
        color: #999;
        font-weight: 600;
    }

    .booking-btn {
        width: 100%;
        padding: 1.2rem;
        background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 800;
        font-size: 1.05rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        box-shadow: 0 8px 24px rgba(255, 82, 82, 0.4);
        text-decoration: none !important;
        display: inline-block;
    }

    .booking-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(255, 82, 82, 0.5);
        background: linear-gradient(135deg, #ff5252 0%, #ff3838 100%);
        text-decoration: none !important;
    }

    .booking-btn:disabled {
        background: #e0e0e0;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* Important Notes Section */
    .important-notes {
        background: white;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(91, 163, 192, 0.12);
        margin-top: 3rem;
        border: 1px solid rgba(91, 163, 192, 0.1);
    }

    .important-notes h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a1a2e;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 3px solid linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
    }

    .notes-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid #e8e8ed;
    }

    .notes-tab-btn {
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        font-weight: 600;
        color: #999;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        position: relative;
    }

    .notes-tab-btn.active {
        color: #5BA3C0;
    }

    .notes-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
    }

    .notes-content {
        color: #4a5568;
        line-height: 1.8;
        font-size: 1rem;
    }

    .notes-content ul {
        margin: 1rem 0;
        padding-left: 2rem;
    }

    .notes-content li {
        margin-bottom: 0.8rem;
    }

    .description-section {
        background: white;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(91, 163, 192, 0.12);
        margin-bottom: 2rem;
        border: 1px solid rgba(91, 163, 192, 0.1);
        transition: all 0.3s ease;
    }

    .description-section:hover {
        box-shadow: 0 16px 48px rgba(91, 163, 192, 0.18);
    }

    .description-section h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a1a2e;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
    }

    .description-section p {
        color: #4a5568;
        line-height: 1.8;
        font-size: 1.05rem;
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
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(91, 163, 192, 0.3);
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
        background: transparent;
        color: #5BA3C0;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        padding: 0.5rem 0 !important;
    }

    .btn-back:hover {
        transform: none;
        color: #4A8FA3;
        text-decoration: none;
        margin-left: -4px;
    }

    .reviews-section {
        background: white;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(91, 163, 192, 0.12);
        margin-bottom: 2rem;
        border: 1px solid rgba(91, 163, 192, 0.1);
        transition: all 0.3s ease;
    }

    .reviews-section:hover {
        box-shadow: 0 16px 48px rgba(91, 163, 192, 0.18);
    }

    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
    }

    .reviews-header h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a1a2e;
        margin: 0;
    }

    .review-count-badge {
        background: #5BA3C0;
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .btn-write-review {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
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
        box-shadow: 0 8px 20px rgba(91, 163, 192, 0.3);
        color: white;
        text-decoration: none;
    }

    .review-item {
        border-bottom: 1px solid #e8e8ed;
        padding: 1.5rem;
        margin: 0 -2.5rem;
        padding: 1.5rem 2.5rem;
        background: linear-gradient(135deg, rgba(91, 163, 192, 0.02) 0%, rgba(74, 143, 163, 0.01) 100%);
        transition: all 0.2s ease;
    }

    .review-item:hover {
        background: linear-gradient(135deg, rgba(91, 163, 192, 0.06) 0%, rgba(74, 143, 163, 0.03) 100%);
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
        background: linear-gradient(135deg, #ffffff 0%, rgba(91, 163, 192, 0.05) 100%);
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(91, 163, 192, 0.12);
        text-align: center;
        border: 1px solid rgba(91, 163, 192, 0.1);
        transition: all 0.3s ease;
    }

    .creator-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(91, 163, 192, 0.18);
    }

    .creator-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #D3A5A5 0%, #C98E8E 100%);
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
        color: #5BA3C0;
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

        .booking-sidebar {
            position: static;
            margin-top: 2rem;
        }

        .notes-tabs {
            flex-wrap: wrap;
        }

        .notes-tab-btn {
            padding: 0.8rem 1rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container" style="padding: 2rem 0;">
    @php
        // 计算可用票数
        $bookedTickets = $concert->orders()
            ->where('status', 'confirmed')
            ->sum('quantity');
        $availableTickets = $concert->total_ticket - $bookedTickets;
    @endphp

    <!-- Back Button -->
    <div style="margin-bottom: 1.5rem;">
        <a href="javascript:void(0);" onclick="window.history.back();" class="btn-back" style="display: inline-block; padding: 0.6rem 1.5rem; cursor: pointer;">
            <i class="fas fa-arrow-left"></i> Back to Concerts
        </a>
    </div>

    <!-- Concert Hero Section -->
    <div class="concert-hero">
        <div class="concert-hero-content">
            <h1><i class="fas fa-music" style="margin-right: 1rem;"></i>{{ $concert->title }}</h1>
            <p class="artist-name">
                <i class="fas fa-user-music"></i> {{ $concert->artist }}
            </p>

            <div class="concert-info-badge">
                <div class="badge-item">
                    <i class="fas fa-calendar-alt"></i>
                    {{ \Carbon\Carbon::parse($concert->date)->format('M d, Y') }}
                </div>
                <div class="badge-divider"></div>
                <div class="badge-item">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $concert->venue }}
                </div>
                <div class="badge-divider"></div>
                <div class="badge-item">
                    <i class="fas fa-dollar-sign"></i>
                    ${{ number_format($concert->ticket_price, 0) }}
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <!-- Main Content - Left Side (8 columns) -->
        <div class="col-lg-8">
            <!-- Concert Details Card -->
            <div class="detail-card">
                <div class="detail-list">
                    <div class="detail-list-item">
                        <div class="detail-list-label">Date</div>
                        <div class="detail-list-value">{{ \Carbon\Carbon::parse($concert->date)->format('d M Y h:i A') }}</div>
                    </div>

                    <div class="detail-list-item">
                        <div class="detail-list-label">Venue</div>
                        <div class="detail-list-value">{{ $concert->venue }}</div>
                    </div>

                    @if($concert->seating_areas)
                        @php
                            $seatingAreas = json_decode($concert->seating_areas, true) ?? [];
                        @endphp
                        @if(count($seatingAreas) > 0)
                            <div class="detail-list-item">
                                <div class="detail-list-label">Seating</div>
                                <div class="detail-list-value">
                                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                        @foreach($seatingAreas as $area)
                                            <span style="background: rgba(91, 163, 192, 0.15); color: #5BA3C0; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 600;">{{ $area }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="detail-list-item">
                        <div class="detail-list-label">Status</div>
                        <div class="detail-list-value">
                            @if($availableTickets > 0)
                                <span style="color: #28a745; font-weight: 700;">✓ {{ $availableTickets }} of {{ $concert->total_ticket }} Tickets Available</span>
                            @else
                                <span style="color: #dc3545; font-weight: 700;">✕ Sold Out</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="description-section">
                <h3><i class="fas fa-align-left"></i> About This Concert</h3>
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
                    @endif
                @endauth
            </div>

            <!-- Important Notes Section -->
            <div class="important-notes">
                <h3><i class="fas fa-exclamation-circle"></i> Important Notes</h3>
                
                <div class="notes-tabs">
                    <button class="notes-tab-btn active" onclick="showNoteTab(0, this)">Notes</button>
                    <button class="notes-tab-btn" onclick="showNoteTab(1, this)">Terms of Sales</button>
                    <button class="notes-tab-btn" onclick="showNoteTab(2, this)">Terms & Conditions</button>
                </div>

                <!-- Notes Content -->
                <div class="notes-content" id="note-0">
                    <ul>
                        <li>Please arrive at least 30 minutes before the concert starts</li>
                        <li>Tickets are non-refundable once purchased</li>
                        <li>Children under 12 may require parental supervision</li>
                        <li>Photography and recording are prohibited during the performance</li>
                        <li>Venue capacity is limited - arrive early for best seating</li>
                    </ul>
                </div>

                <!-- Terms of Sales -->
                <div class="notes-content" id="note-1" style="display: none;">
                    <p>By purchasing tickets, you agree to the following terms:</p>
                    <ul>
                        <li>All ticket sales are final. No refunds or exchanges permitted</li>
                        <li>Resale of tickets is strictly prohibited without written permission</li>
                        <li>Prices exclude booking fees and transaction charges</li>
                        <li>Early bird discounts and promotions cannot be combined</li>
                        <li>We reserve the right to change performers or reschedule dates with advance notice</li>
                    </ul>
                </div>

                <!-- Terms & Conditions -->
                <div class="notes-content" id="note-2" style="display: none;">
                    <p>General Terms & Conditions:</p>
                    <ul>
                        <li>Valid ID required for entry to the venue</li>
                        <li>Right of admission reserved - management reserves the right to refuse entry</li>
                        <li>Prohibited items: weapons, glass bottles, recording devices, professional cameras</li>
                        <li>Age restrictions may apply - check venue policies</li>
                        <li>ConcertHub is not responsible for lost or stolen items</li>
                        <li>By attending, you consent to photography and videography for promotional purposes</li>
                    </ul>
                </div>
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

        <!-- Sidebar - Right Side (4 columns) -->
        <div class="col-lg-4">
            <div class="booking-sidebar">
                <h4>Starting From</h4>
                <div class="booking-price">
                    $<span>{{ number_format($concert->ticket_price, 0) }}</span>
                </div>

                @if($availableTickets > 0)
                    @auth
                        @if(auth()->user()->role === 'user')
                            <a href="{{ route('orders.create', $concert) }}" class="booking-btn">
                                Book Now
                            </a>
                        @else
                            <button class="booking-btn" disabled>
                                Not Available for Admin
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="booking-btn">
                            Sign In to Book
                        </a>
                    @endauth
                @else
                    <button class="booking-btn" disabled>
                        Sold Out
                    </button>
                @endif

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e8e8ed;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.9rem;">
                        <span style="color: #999;">Available Tickets:</span>
                        <strong style="color: #1a1a2e;">{{ $availableTickets }} / {{ $concert->total_ticket }}</strong>
                    </div>
                    <div style="width: 100%; height: 6px; background: #e8e8ed; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: linear-gradient(90deg, #5BA3C0 0%, #4A8FA3 100%); width: {{ $availableTickets > 0 ? min(100, round(($concert->total_ticket - $availableTickets) / $concert->total_ticket * 100)) : 100 }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showNoteTab(tabIndex, button) {
        // Hide all notes
        document.getElementById('note-0').style.display = 'none';
        document.getElementById('note-1').style.display = 'none';
        document.getElementById('note-2').style.display = 'none';

        // Show selected note
        document.getElementById('note-' + tabIndex).style.display = 'block';

        // Update active button
        document.querySelectorAll('.notes-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
    }
</script>

@endsection
