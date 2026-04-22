@extends('layouts.app')

@section('title', 'My Profile - ConcertHub')

@section('content')
<style>
    .profile-container {
        padding: 2rem 0;
    }

    .profile-header {
        margin-bottom: 2rem;
    }

    .profile-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a1a2e;
    }

    .profile-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        margin: 0 auto 1.5rem;
        box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .profile-email {
        color: #4a5568;
        margin-bottom: 1rem;
    }

    .role-badge {
        display: inline-block;
        background: linear-gradient(135deg, #D3A5A5 0%, #C98E8E 100%);
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 1.5rem;
    }

    .profile-nav {
        display: flex;
        gap: 1rem;
        flex-direction: column;
    }

    .profile-nav-btn {
        flex: 1;
        padding: 0.9rem 1.5rem;
        border: 2px solid #5BA3C0;
        background: white;
        color: #5BA3C0;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }

    .profile-nav-btn:hover {
        background: #5BA3C0;
        color: white;
        transform: translateY(-2px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .stat-icon {
        font-size: 2rem;
        color: #5BA3C0;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-label {
        color: #666;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .section-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .section-title i {
        color: #5BA3C0;
        margin-right: 0.5rem;
    }

    .item-card {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .item-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .item-card:last-child {
        margin-bottom: 0;
    }

    .item-title {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .item-title a {
        color: #5BA3C0;
        text-decoration: none;
    }

    .item-title a:hover {
        text-decoration: underline;
    }

    .item-meta {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-confirmed {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }

    .status-cancelled {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }

    .rating-display {
        color: #ffc107;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .view-all-btn {
        background: linear-gradient(135deg, #D3A5A5 0%, #C98E8E 100%);
        color: white;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .view-all-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(211, 165, 165, 0.3);
        color: white;
        text-decoration: none;
    }

    .empty-message {
        text-align: center;
        padding: 2rem;
        color: #666;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .profile-header h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="profile-container container">
    <!-- Header -->
    <div class="profile-header">
        <h1><i class="fas fa-user-circle" style="color: #5BA3C0;"></i> My Profile</h1>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="profile-card">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-email">{{ $user->email }}</div>
                <div class="role-badge">
                    <i class="fas fa-badge"></i> {{ ucfirst($user->role) }}
                </div>
                <div class="profile-nav">
                    @if($user->is_admin)
                        <a href="{{ route('profile.published_concerts') }}" class="profile-nav-btn">
                            <i class="fas fa-music"></i> Published Concerts
                        </a>
                    @else
                        <a href="{{ route('profile.orders') }}" class="profile-nav-btn">
                            <i class="fas fa-shopping-cart"></i> My Orders
                        </a>
                    @endif
                    @if($user->is_admin)
                        <a href="{{ route('profile.admin_reviews') }}" class="profile-nav-btn">
                            <i class="fas fa-comments"></i> Concert Reviews
                        </a>
                    @else
                        <a href="{{ route('profile.reviews') }}" class="profile-nav-btn" style="margin-bottom: 1rem;">
                            <i class="fas fa-star"></i> My Reviews
                        </a>
                        <a href="{{ route('payment_cards.index') }}" class="profile-nav-btn">
                            <i class="fas fa-credit-card"></i> Payment Card
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        @if($user->is_admin)
                            <i class="fas fa-music"></i>
                        @else
                            <i class="fas fa-ticket-alt"></i>
                        @endif
                    </div>
                    @if($user->is_admin)
                        <div class="stat-value">{{ $totalPublished }}</div>
                        <div class="stat-label">Total Published Concerts</div>
                    @else
                        <div class="stat-value">{{ $orders->count() }}</div>
                        <div class="stat-label">Total Orders</div>
                    @endif
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    @if($user->is_admin)
                        <div class="stat-value">{{ $totalAdminReviews }}</div>
                        <div class="stat-label">Reviews on My Concerts</div>
                    @else
                        <div class="stat-value">{{ $reviews->count() }}</div>
                        <div class="stat-label">Reviews Written</div>
                    @endif
                </div>
            </div>

            @if($user->is_admin)
            <!-- Recent Concerts (upcoming within 1 month) -->
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-calendar-alt"></i> Recent Concerts
                    <small style="font-size:0.8rem; font-weight:400; color:#999; margin-left:0.5rem;">(Upcoming within 1 month)</small>
                </h3>

                @forelse($upcomingConcerts as $concert)
                    <div class="item-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div class="item-title">{{ $concert->title }}</div>
                                <div class="item-meta">
                                    <i class="fas fa-user-music"></i> {{ $concert->artist }}
                                    &nbsp;·&nbsp;
                                    <i class="fas fa-map-marker-alt"></i> {{ $concert->venue }}
                                    &nbsp;·&nbsp;
                                    <i class="fas fa-calendar"></i> {{ $concert->date->format('M d, Y') }}
                                </div>
                            </div>
                            <div style="display:flex; gap:0.5rem; flex-shrink:0;">
                                <a href="{{ route('admin.concerts.show', $concert) }}" class="view-all-btn" style="background:linear-gradient(135deg,#5BA3C0,#4A8FA3);">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('concerts.edit', $concert) }}" class="view-all-btn" style="background:linear-gradient(135deg,#6BB6D6,#5BA3C0);">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.concerts.delete', $concert) }}" method="POST" onsubmit="return confirm('Delete this concert?');" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="view-all-btn" style="background:linear-gradient(135deg,#D9A5A5,#C98E8E); border:none; cursor:pointer;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-message">
                        <i class="fas fa-calendar-check"></i> No concerts opening within the next month.
                    </div>
                @endforelse

                @if($totalPublished > 0)
                    <div style="text-align:center; margin-top:1.5rem;">
                        <a href="{{ route('profile.published_concerts') }}" class="view-all-btn">
                            View All Published Concerts
                        </a>
                    </div>
                @endif
            </div>
            @else
            <!-- Recent Orders (for regular users) -->
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-receipt"></i> Recent Orders
                </h3>

                @forelse($orders->take(5) as $order)
                    <div class="item-card">
                        <div class="item-title">
                            <a href="{{ route('concerts.show', $order->concert) }}">
                                {{ $order->concert->title }}
                            </a>
                        </div>
                        <div class="item-meta">
                            <i class="fas fa-user-music"></i> {{ $order->concert->artist }}
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="color: #666;">
                                    <i class="fas fa-ticket-alt"></i> {{ $order->quantity }}
                                    @if($order->quantity > 1) tickets @else ticket @endif -
                                    ${{ number_format($order->total_price, 2) }}
                                </span>
                            </div>
                            <span class="status-badge @if($order->status === 'confirmed') status-confirmed @else status-cancelled @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="empty-message">
                        <i class="fas fa-inbox"></i> You haven't booked any concerts yet.
                    </div>
                @endforelse

                @if($orders->count() > 5)
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="{{ route('profile.orders') }}" class="view-all-btn">
                            View All Orders
                        </a>
                    </div>
                @endif
            </div>
            @endif

            <!-- Recent Reviews -->
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-comments"></i> Recent Reviews
                </h3>

                @if($user->is_admin)
                    {{-- Admin: show reviews on their concerts --}}
                    @forelse($adminReviews->take(5) as $review)
                        <div class="item-card">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                                <div style="flex:1;">
                                    {{-- Who --}}
                                    <div style="font-weight:700; color:#2c3e50; margin-bottom:0.3rem;">
                                        <i class="fas fa-user" style="color:#5BA3C0;"></i>
                                        {{ $review->user->name ?? 'Unknown User' }}
                                    </div>
                                    {{-- Which concert --}}
                                    <div class="item-meta">
                                        <i class="fas fa-music"></i>
                                        <a href="{{ route('concerts.show', $review->concert) }}" style="color:#5BA3C0; text-decoration:none;">
                                            {{ $review->concert->title }}
                                        </a>
                                    </div>
                                    {{-- Rating --}}
                                    <div class="rating-display" style="margin-bottom:0.4rem;">
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <i class="fas fa-star"></i>
                                        @endfor
                                        <span style="color:#666; margin-left:0.4rem; font-size:0.85rem;">({{ $review->rating }}/5)</span>
                                    </div>
                                    {{-- Comment --}}
                                    <div class="item-meta" style="font-style:italic;">
                                        "{{ Str::limit($review->comment, 120) }}"
                                    </div>
                                </div>
                                <small style="color:#999; white-space:nowrap;">
                                    <i class="fas fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="empty-message">
                            <i class="fas fa-star"></i> No reviews on your concerts yet.
                        </div>
                    @endforelse

                    @if($totalAdminReviews > 5)
                        <div style="text-align:center; margin-top:1.5rem;">
                            <a href="{{ route('profile.admin_reviews') }}" class="view-all-btn">
                                View All Reviews
                            </a>
                        </div>
                    @endif

                @else
                    {{-- Regular user: show their own reviews --}}
                    @forelse($reviews->take(5) as $review)
                        <div class="item-card">
                            <div class="item-title">
                                <a href="{{ route('concerts.show', $review->concert) }}">
                                    {{ $review->concert->title }}
                                </a>
                            </div>
                            <div class="rating-display">
                                @for($i = 0; $i < $review->rating; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                                <span style="color: #666; margin-left: 0.5rem;">({{ $review->rating }}/5)</span>
                            </div>
                            <div class="item-meta">
                                @php
                                    $comment = strlen($review->comment) > 100 ? substr($review->comment, 0, 100) . '...' : $review->comment;
                                @endphp
                                "{{ $comment }}"
                            </div>
                            <small style="color: #999;">
                                <i class="fas fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                            </small>
                        </div>
                    @empty
                        <div class="empty-message">
                            <i class="fas fa-star"></i> You haven't written any reviews yet.
                        </div>
                    @endforelse

                    @if($reviews->count() > 5)
                        <div style="text-align: center; margin-top: 1.5rem;">
                            <a href="{{ route('profile.reviews') }}" class="view-all-btn">
                                View All Reviews
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
