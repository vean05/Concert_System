@extends('layouts.app')

@section('title', $user->name . ' - Admin - ConcertHub')

@section('content')
<style>
    .admin-detail-page {
        padding: 2rem 0;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        transform: translateX(-5px);
    }

    .user-header {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        margin-bottom: 2rem;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 2rem;
        align-items: start;
    }

    .user-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 2rem;
    }

    .user-header-info h1 {
        font-size: 2rem;
        color: #1a1a2e;
        margin: 0 0 0.5rem 0;
    }

    .user-email {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .user-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-admin {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        color: white;
    }

    .badge-verified {
        background: #d4edda;
        color: #155724;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .stat-item .label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .stat-item .value {
        font-size: 2rem;
        font-weight: 700;
        color: #7c3aed;
    }

    .section-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        margin-bottom: 2rem;
    }

    .section-card h2 {
        font-size: 1.5rem;
        color: #1a1a2e;
        margin: 0 0 1.5rem 0;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .list-item-title {
        font-weight: 600;
        color: #1a1a2e;
    }

    .list-item-meta {
        font-size: 0.9rem;
        color: #666;
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f8f9fa;
    }

    th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: #1a1a2e;
        border-bottom: 2px solid #e0e0e0;
    }

    td {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        color: #4a5568;
    }

    tbody tr:hover {
        background: rgba(124, 58, 237, 0.05);
    }

    @media (max-width: 768px) {
        .user-header {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-detail-page container">
    <a href="javascript:history.back()" class="back-link" style="display: inline-block; margin-bottom: 1.5rem; color: #7c3aed; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- User Header -->
    <div class="user-header">
        <div class="user-avatar-large">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="user-header-info">
            <h1>{{ $user->name }}</h1>
            <div class="user-email">{{ $user->email }}</div>
            <div class="user-badges">
                @if($user->is_admin)
                    <span class="badge badge-admin"><i class="fas fa-crown"></i> Admin</span>
                @endif
                @if($user->email_verified_at)
                    <span class="badge badge-verified"><i class="fas fa-check-circle"></i> Verified</span>
                @endif
            </div>
            <div style="margin-top: 1rem; color: #999; font-size: 0.9rem;">
                Joined {{ $user->created_at->format('F d, Y') }}
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="label">Concerts Created</div>
            <div class="value">{{ $user->concerts->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Orders Placed</div>
            <div class="value">{{ $user->orders->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Reviews Written</div>
            <div class="value">{{ $user->reviews->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Concerts Favourited</div>
            <div class="value">{{ $user->favouriteConcerts->count() }}</div>
        </div>
    </div>

    <!-- Concerts Created -->
    @if($user->concerts->count() > 0)
        <div class="section-card">
            <h2><i class="fas fa-music" style="color: #7c3aed;"></i> Concerts Created ({{ $user->concerts->count() }})</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->concerts as $concert)
                            <tr>
                                <td><strong>{{ $concert->title }}</strong></td>
                                <td>{{ $concert->date->format('M d, Y') }}</td>
                                <td>{{ $concert->venue }}</td>
                                <td>{{ $concert->orders->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Recent Orders -->
    @if($user->orders->count() > 0)
        <div class="section-card">
            <h2><i class="fas fa-shopping-cart" style="color: #7c3aed;"></i> Recent Orders (Last 5)</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Concert</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->orders->take(5) as $order)
                            <tr>
                                <td>{{ $order->concert->title }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td>${{ $order->total_price }}</td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Favourited Concerts -->
    @if($user->favouriteConcerts->count() > 0)
        <div class="section-card">
            <h2><i class="fas fa-heart" style="color: #ec4899;"></i> Favourited Concerts</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->favouriteConcerts as $concert)
                            <tr>
                                <td><strong>{{ $concert->title }}</strong></td>
                                <td>{{ $concert->artist }}</td>
                                <td>{{ $concert->date->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
