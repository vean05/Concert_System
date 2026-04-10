@extends('layouts.app')

@section('title', 'Admin Dashboard - ConcertHub')

@section('content')
<style>
    .admin-container {
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

    .admin-nav {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .admin-nav a {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .admin-nav a:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
    }

    .admin-nav a.active {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .stat-card h3 {
        font-size: 0.9rem;
        color: #666;
        margin: 0 0 1rem 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-card .number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #7c3aed;
        margin: 0;
    }

    .content-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        margin-bottom: 2rem;
    }

    .content-card h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 1.5rem;
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

    .btn-small {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        margin-right: 0.5rem;
    }

    .btn-view {
        background: #7c3aed;
        color: white;
    }

    .btn-view:hover {
        background: #6d28d9;
    }

    .btn-edit {
        background: #00b4d8;
        color: white;
    }

    .btn-edit:hover {
        background: #0096c7;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .btn-delete:hover {
        background: #c82333;
    }
</style>

<div class="admin-container container">
    <!-- Back Button -->
    <a href="javascript:history.back()" class="back-link" style="display: inline-block; margin-bottom: 1.5rem; color: #7c3aed; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- Admin Header -->
    <div class="admin-header">
        <h1><i class="fas fa-cogs" style="color: #7c3aed;"></i> Admin Dashboard</h1>
    </div>

    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="{{ route('admin.concerts.index') }}"><i class="fas fa-music"></i> Concerts</a>
        <a href="{{ route('admin.users.index') }}"><i class="fas fa-users"></i> Users</a>
        <a href="{{ route('admin.analytics') }}"><i class="fas fa-chart-bar"></i> Analytics</a>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Concerts</h3>
            <p class="number">{{ $totalConcerts }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="number">{{ $totalUsers }}</p>
        </div>
    </div>

    <!-- Recent Concerts -->
    <div class="content-card">
        <h2><i class="fas fa-music" style="color: #7c3aed;"></i> Recent Concerts</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Creator</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentConcerts as $concert)
                        <tr>
                            <td><strong>{{ $concert->title }}</strong></td>
                            <td>{{ $concert->artist }}</td>
                            <td>{{ $concert->venue }}</td>
                            <td>{{ $concert->date->format('M d, Y') }}</td>
                            <td>{{ $concert->creator->name ?? 'Unknown' }}</td>
                            <td>
                                <a href="{{ route('admin.concerts.show', $concert) }}" class="btn-small btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('concerts.edit', $concert) }}" class="btn-small btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999;">No concerts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upcoming Concerts -->
    <div class="content-card">
        <h2><i class="fas fa-calendar-alt" style="color: #7c3aed;"></i> Upcoming Concerts</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Tickets Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcomingConcerts as $concert)
                        <tr>
                            <td><strong>{{ $concert->title }}</strong></td>
                            <td>{{ $concert->date->format('M d, Y H:i') }}</td>
                            <td>{{ $concert->venue }}</td>
                            <td>{{ $concert->total_ticket }}</td>
                            <td>
                                <a href="{{ route('admin.concerts.show', $concert) }}" class="btn-small btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('concerts.edit', $concert) }}" class="btn-small btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999;">No upcoming concerts</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
