@extends('layouts.app')

@section('title', 'Manage Users - Admin - ConcertHub')

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

    .filter-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .filter-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .form-control {
        flex: 1;
        min-width: 200px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.8rem;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #7c3aed;
        outline: none;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }

    .btn-small {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .btn-search {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
    }

    .btn-reset {
        background: #6c757d;
        color: white;
    }

    .btn-reset:hover {
        background: #5a6268;
    }

    .table-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        overflow: hidden;
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

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(124, 58, 237, 0.1);
        color: #7c3aed;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
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

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination a, .pagination span {
        padding: 0.5rem 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        text-decoration: none;
        color: #7c3aed;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: #7c3aed;
        color: white;
    }

    .pagination .active span {
        background: #7c3aed;
        color: white;
        border-color: #7c3aed;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<div class="admin-page container">
    <!-- Back Button -->
    <a href="javascript:history.back()" class="back-link" style="display: inline-block; margin-bottom: 1.5rem; color: #7c3aed; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- Header -->
    <div class="admin-header">
        <h1><i class="fas fa-users" style="color: #7c3aed;"></i> Manage Users</h1>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="filter-row">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by name or email..."
                    value="{{ request('search') }}"
                >
                <button type="submit" class="btn-small btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-small btn-reset">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Concerts</th>
                            <th>Orders</th>
                            <th>Reviews</th>
                            <th>Favourites</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->is_admin)
                                                <div><span class="badge">Admin</span></div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span style="background: rgba(124, 58, 237, 0.1); padding: 0.25rem 0.75rem; border-radius: 20px; color: #7c3aed; font-weight: 600;">
                                        {{ $user->concerts_count }}
                                    </span>
                                </td>
                                <td>
                                    <span style="background: rgba(0, 180, 216, 0.1); padding: 0.25rem 0.75rem; border-radius: 20px; color: #00b4d8; font-weight: 600;">
                                        {{ $user->orders_count }}
                                    </span>
                                </td>
                                <td>
                                    <span style="background: rgba(236, 72, 153, 0.1); padding: 0.25rem 0.75rem; border-radius: 20px; color: #ec4899; font-weight: 600;">
                                        {{ $user->reviews_count }}
                                    </span>
                                </td>
                                <td>
                                    <span style="background: rgba(255, 152, 0, 0.1); padding: 0.25rem 0.75rem; border-radius: 20px; color: #ff6b35; font-weight: 600;">
                                        {{ $user->favourite_concerts_count }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-action btn-view" title="View">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $users->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No users found.</p>
            </div>
        @endif
    </div>
</div>
@endsection
