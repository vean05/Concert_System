@extends('layouts.app')

@section('title', 'Analytics - Admin - ConcertHub')

@section('content')
<style>
    .admin-page {
        padding: 2rem 0;
    }

    .admin-header {
        margin-bottom: 2rem;
    }

    .admin-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        text-align: center;
    }

    .stat-card h3 {
        font-size: 0.9rem;
        color: #666;
        margin: 0 0 1rem 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-card .number {
        font-size: 3rem;
        font-weight: 700;
        color: #7c3aed;
        margin: 0;
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
        font-weight: 700;
        color: #1a1a2e;
        margin: 0 0 1.5rem 0;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 2rem;
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #f0f4f8 100%);
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

    .rank {
        display: inline-block;
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: 700;
        margin-right: 1rem;
    }

    .progress-bar {
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #7c3aed 0%, #6d28d9 100%);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #999;
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<div class="admin-page container">
    <!-- Header -->
    <div class="admin-header">
        <h1><i class="fas fa-chart-bar" style="color: #7c3aed;"></i> Analytics Dashboard</h1>
    </div>

    <!-- Overall Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Concerts</h3>
            <p class="number">{{ $totalConcerts }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="number">{{ $totalUsers }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p class="number">{{ $totalOrders }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Reviews</h3>
            <p class="number">{{ $totalReviews }}</p>
        </div>
    </div>

    <!-- Most Popular Concerts -->
    <div class="section-card">
        <h2><i class="fas fa-crown" style="color: #ff6b35;"></i> Most Popular Concerts (by orders)</h2>
        @if($popularConcerts->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="50">Rank</th>
                            <th>Concert Title</th>
                            <th>Artist</th>
                            <th>Orders</th>
                            <th>Popularity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($popularConcerts as $index => $concert)
                            <tr>
                                <td><span class="rank">{{ $index + 1 }}</span></td>
                                <td><strong>{{ $concert->title }}</strong></td>
                                <td>{{ $concert->artist }}</td>
                                <td>{{ $concert->orders_count }}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ ($concert->orders_count / $popularConcerts->first()->orders_count * 100) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No order data available yet.</p>
            </div>
        @endif
    </div>

    <!-- Most Reviewed Concerts -->
    <div class="section-card">
        <h2><i class="fas fa-star" style="color: #ff6b35;"></i> Most Reviewed Concerts</h2>
        @if($reviewedConcerts->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="50">Rank</th>
                            <th>Concert Title</th>
                            <th>Artist</th>
                            <th>Reviews</th>
                            <th>Review Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviewedConcerts as $index => $concert)
                            <tr>
                                <td><span class="rank">{{ $index + 1 }}</span></td>
                                <td><strong>{{ $concert->title }}</strong></td>
                                <td>{{ $concert->artist }}</td>
                                <td>{{ $concert->reviews_count }}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ ($concert->reviews_count / $reviewedConcerts->first()->reviews_count * 100) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <p>No review data available yet.</p>
            </div>
        @endif
    </div>

    <!-- Concerts by Month -->
    <div class="section-card">
        <h2><i class="fas fa-calendar-alt" style="color: #7c3aed;"></i> Concerts Created by Month (Last 12 Months)</h2>
        @if($concertsByMonth->count() > 0)
            <div style="margin-top: 1.5rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Concerts Created</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($concertsByMonth as $item)
                            <tr>
                                <td><strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $item->month)->format('F Y') }}</strong></td>
                                <td>{{ $item->count }}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ ($item->count / $concertsByMonth->max('count') * 100) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-chart-bar"></i>
                <p>No concert creation data available yet.</p>
            </div>
        @endif
    </div>

    <!-- Quick Summary -->
    <div class="section-card">
        <h2><i class="fas fa-info-circle" style="color: #7c3aed;"></i> Quick Summary</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div>
                <h4 style="margin: 0 0 1rem 0; color: #7c3aed;">System Overview</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 0.5rem 0; color: #4a5568;">
                        <i class="fas fa-circle" style="color: #7c3aed; margin-right: 0.5rem;"></i>
                        Average concerts per user: <strong>{{ $totalConcerts > 0 && $totalUsers > 0 ? round($totalConcerts / $totalUsers, 2) : 0 }}</strong>
                    </li>
                    <li style="padding: 0.5rem 0; color: #4a5568;">
                        <i class="fas fa-circle" style="color: #7c3aed; margin-right: 0.5rem;"></i>
                        Total users: <strong>{{ $totalUsers }}</strong>
                    </li>
                    <li style="padding: 0.5rem 0; color: #4a5568;">
                        <i class="fas fa-circle" style="color: #7c3aed; margin-right: 0.5rem;"></i>
                        Active concerts: <strong>{{ \App\Models\Concert::where('date', '>=', now())->count() }}</strong>
                    </li>
                </ul>
            </div>
            <div>
                <h4 style="margin: 0 0 1rem 0; color: #7c3aed;">Engagement Metrics</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 0.5rem 0; color: #4a5568;">
                        <i class="fas fa-circle" style="color: #00b4d8; margin-right: 0.5rem;"></i>
                        Total orders: <strong>{{ $totalOrders }}</strong>
                    </li>
                    <li style="padding: 0.5rem 0; color: #4a5568;">
                        <i class="fas fa-circle" style="color: #ec4899; margin-right: 0.5rem;"></i>
                        Total reviews: <strong>{{ $totalReviews }}</strong>
                    </li>
                    <li style="padding: 0.5rem 0; color: #4a5568;">
                        <i class="fas fa-circle" style="color: #ff6b35; margin-right: 0.5rem;"></i>
                        Average reviews per concert: <strong>{{ $totalConcerts > 0 ? round($totalReviews / $totalConcerts, 2) : 0 }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
