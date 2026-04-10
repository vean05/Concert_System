@extends('layouts.app')

@section('title', 'Manage Concerts - Admin - ConcertHub')

@section('content')
<style>
    .admin-page {
        padding: 2rem 0;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .admin-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
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
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-control {
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

    .filter-actions {
        display: flex;
        gap: 1rem;
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
        cursor: pointer;
        user-select: none;
    }

    th a {
        color: inherit;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    th a:hover {
        color: #7c3aed;
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
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }

    .actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
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
        <div>
            <h1><i class="fas fa-music" style="color: #7c3aed;"></i> Manage Concerts</h1>
        </div>
        <a href="{{ route('admin.concerts.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Create New Concert
        </a>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.concerts.index') }}" class="filter-form">
            <div class="filter-row">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by title, artist, or venue..."
                    value="{{ request('search') }}"
                >
                <select name="creator_id" class="form-control">
                    <option value="">All Creators</option>
                    @foreach($creators as $creator)
                        <option value="{{ $creator->id }}" {{ request('creator_id') == $creator->id ? 'selected' : '' }}>
                            {{ $creator->name }}
                        </option>
                    @endforeach
                </select>
                <select name="sort_by" class="form-control">
                    <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>Sort by Date</option>
                    <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Sort by Title</option>
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Sort by Created</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-small btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ route('admin.concerts.index') }}" class="btn-small btn-reset">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        @if($concerts->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Tickets</th>
                            <th>Creator</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($concerts as $concert)
                            <tr>
                                <td><strong>{{ $concert->title }}</strong></td>
                                <td>{{ $concert->artist }}</td>
                                <td>{{ $concert->venue }}</td>
                                <td>{{ $concert->date->format('M d, Y') }}</td>
                                <td>${{ $concert->ticket_price }}</td>
                                <td>{{ $concert->total_ticket }}</td>
                                <td>{{ $concert->creator->name ?? 'Unknown' }}</td>
                                <td>
                                    @if($concert->date > now())
                                        <span class="badge badge-success">Upcoming</span>
                                    @elseif($concert->date->copy()->addDays(30) > now())
                                        <span class="badge badge-warning">Active</span>
                                    @else
                                        <span class="badge badge-danger">Past</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.concerts.show', $concert) }}" class="btn-action btn-view" title="View">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('concerts.edit', $concert) }}" class="btn-action btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.concerts.delete', $concert) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $concerts->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No concerts found.</p>
                <a href="{{ route('concerts.create') }}" class="btn-primary">Create your first concert</a>
            </div>
        @endif
    </div>
</div>
@endsection
