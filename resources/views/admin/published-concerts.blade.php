@extends('layouts.app')

@section('title', 'Published Concerts - Admin Panel - ConcertHub')

@section('content')
<style>
    .page-container { padding: 2rem 0; }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #5BA3C0;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .back-link:hover { transform: translateX(-5px); color: #4A8FA3; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .table-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31,38,135,0.12);
    }

    table { width: 100%; border-collapse: collapse; }
    thead { background: linear-gradient(135deg, #f8f9fa 0%, #f0f4f8 100%); }
    th { padding: 1rem; text-align: left; font-weight: 700; color: #1a1a2e; border-bottom: 2px solid #e0e0e0; }
    td { padding: 1rem; border-bottom: 1px solid #f0f0f0; color: #4a5568; }
    tbody tr:hover { background: rgba(107,182,214,0.05); }

    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        margin-right: 0.3rem;
        border: none;
        cursor: pointer;
    }
    .btn-view  { background: #5BA3C0; color: white; }
    .btn-view:hover  { background: #4A8FA3; color: white; }
    .btn-edit  { background: #6BB6D6; color: white; }
    .btn-edit:hover  { background: #5BA3C0; color: white; }
    .btn-delete { background: #D9A5A5; color: white; }
    .btn-delete:hover { background: #C98E8E; color: white; }

    .badge-upcoming { background: #d4edda; color: #155724; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
    .badge-past     { background: #f8d7da; color: #721c24; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }

    .empty-state { text-align: center; padding: 3rem; color: #999; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.4; display: block; }

    .btn-create {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-create:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(91,163,192,0.3); color: white; }
</style>

<div class="page-container container">
    <a href="javascript:void(0);" onclick="window.history.back();" class="back-link">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="page-header">
        <h1><i class="fas fa-music" style="color:#5BA3C0;"></i> Published Concerts</h1>
        <a href="{{ route('admin.concerts.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Create New Concert
        </a>
    </div>

    <div class="table-card">
        @if($concerts->count() > 0)
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Tickets</th>
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
                                <td>${{ number_format($concert->ticket_price, 2) }}</td>
                                <td>{{ $concert->total_ticket }}</td>
                                <td>
                                    @if($concert->date > now())
                                        <span class="badge-upcoming">Upcoming</span>
                                    @else
                                        <span class="badge-past">Past</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.concerts.show', $concert) }}" class="btn-action btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('concerts.edit', $concert) }}" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.concerts.delete', $concert) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this concert?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:1.5rem;">
                {{ $concerts->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-music"></i>
                <p>No published concerts yet.</p>
                <a href="{{ route('admin.concerts.create') }}" class="btn-create">Create your first concert</a>
            </div>
        @endif
    </div>
</div>
@endsection
