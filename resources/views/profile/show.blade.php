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
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
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
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
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
        border: 2px solid #7c3aed;
        background: white;
        color: #ff6b35;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }

    .profile-nav-btn:hover {
        background: #7c3aed;
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
        color: #ff6b35;
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
        color: #ff6b35;
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
        color: #ff6b35;
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
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
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
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
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
        <h1><i class="fas fa-user-circle" style="color: #7c3aed;"></i> My Profile</h1>
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
                    <a href="{{ route('profile.orders') }}" class="profile-nav-btn">
                        <i class="fas fa-shopping-cart"></i> My Orders
                    </a>
                    <a href="{{ route('profile.reviews') }}" class="profile-nav-btn">
                        <i class="fas fa-star"></i> My Reviews
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-value">{{ $orders->count() }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value">{{ $reviews->count() }}</div>
                    <div class="stat-label">Reviews Written</div>
                </div>
            </div>

            <!-- Recent Orders -->
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

            <!-- Recent Reviews -->
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-comments"></i> Recent Reviews
                </h3>

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
                                // Truncate comment to 100 characters
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
            </div>
        </div>
    </div>
</div>
@endsection
