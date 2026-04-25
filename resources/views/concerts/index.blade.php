@extends('layouts.app')

@section('title', 'Concerts - ConcertHub')

@section('content')
<style>
    .concert-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);
        border: 1px solid rgba(100, 116, 139, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        min-height: 520px;
        display: flex;
        flex-direction: column;
        position: relative;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }

    .concert-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.03) 0%, rgba(0, 180, 216, 0.03) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
        pointer-events: none;
        border-radius: 20px;
    }

    .concert-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 16px 40px rgba(31, 38, 135, 0.15);
        border-color: rgba(91, 163, 192, 0.3);
    }

    .concert-card:hover::before {
        opacity: 1;
    }

    .concert-card-header {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        height: 260px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        flex-shrink: 0;
    }

    .concert-card-header img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #f8f9fa;
        display: block;
    }

    .concert-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.3), transparent),
                    radial-gradient(circle at 70% 70%, rgba(0, 0, 0, 0.1), transparent);
    }

    .concert-card-header.has-image::after {
        display: none;
    }

    .concert-card-header i {
        opacity: 0.2;
        position: relative;
        z-index: 1;
    }

    .concert-card-body {
        padding: 2rem 1.5rem 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 2;
    }

    .concert-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: -0.5px;
    }

    .concert-artist {
        color: #6BB6D6;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }

    .concert-details {
        flex-grow: 1;
    }

    .concert-detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.85rem;
        color: #4a5568;
        font-size: 0.9rem;
    }

    .concert-detail-item i {
        width: 24px;
        color: #D3A5A5;
        margin-right: 0.75rem;
        font-size: 1rem;
    }

    .ticket-availability {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
        color: #155724;
        padding: 0.65rem 1.2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-top: 1rem;
        text-align: center;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .ticket-availability.sold-out {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(253, 126, 20, 0.1));
        color: #721c24;
        border-color: rgba(220, 53, 69, 0.3);
    }

    .concert-heart-btn {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid rgba(91, 163, 192, 0.3);
        color: rgba(91, 163, 192, 0.6);
        font-size: 1.3rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(10px);
    }

    .concert-heart-btn:hover {
        background: rgba(255, 255, 255, 0.95);
        color: #ff6b6b;
        border-color: #ff6b6b;
        transform: scale(1.1);
    }

    .concert-heart-btn.active {
        background: #ff6b6b;
        color: white;
        border-color: #ff6b6b;
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    .concert-heart-btn.active:hover {
        transform: scale(1.15);
    }

    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(255, 152, 0, 0.25);
        padding: 0.65rem 0.5rem;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        text-align: center;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
        flex: 1;
    }

    .btn-edit:hover {
        transform: translateY(-3px);
        color: white;
        text-decoration: none;
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.25);
    }

    .btn-delete:hover {
        transform: translateY(-3px);
        color: white;
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    }

    .search-filter-box {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);
        border: 1px solid rgba(100, 116, 139, 0.1);
        margin-bottom: 3rem;
    }

    .search-filter-box h3 {
        color: #1a1a2e;
        font-weight: 700;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
    }

    .filter-input {
        border: 1.5px solid rgba(100, 116, 139, 0.2);
        border-radius: 12px;
        padding: 0.85rem 1.2rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        font-weight: 500;
    }

    .filter-input:focus {
        border-color: #5BA3C0;
        box-shadow: 0 0 0 4px rgba(91, 163, 192, 0.1);
        outline: none;
        background: rgba(255, 255, 255, 0.8);
    }

    .btn-filter {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.85rem 2.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(91, 163, 192, 0.25);
    }

    .btn-filter:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(91, 163, 192, 0.35);
    }

    .btn-create {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.85rem 2.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 4px 15px rgba(91, 163, 192, 0.25);
    }

    .btn-create:hover {
        transform: translateY(-4px);
        color: white;
        text-decoration: none;
        box-shadow: 0 8px 30px rgba(91, 163, 192, 0.35);
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3rem;
        margin-top: 1rem;
    }

    .header-section h1 {
        font-size: 2.8rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        letter-spacing: -1px;
    }

    /* Grid Layout for Concerts */
    .concerts-grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    @media (max-width: 1200px) {
        .concerts-grid-container {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .concerts-grid-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
    }

    .pagination {
        margin-top: 3rem;
        gap: 0.5rem;
    }

    .page-link {
        color: #5BA3C0;
        border-color: rgba(91, 163, 192, 0.3);
        background: rgba(255, 255, 255, 0.8);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        color: white;
        background-color: #5BA3C0;
        border-color: #5BA3C0;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #5BA3C0, #4A8FA3);
        border-color: #5BA3C0;
        box-shadow: 0 4px 15px rgba(91, 163, 192, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);
        border: 1px solid rgba(100, 116, 139, 0.1);
    }

    .empty-state i {
        font-size: 4rem;
        background: linear-gradient(135deg, #D3A5A5, #6BB6D6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.5rem;
    }

    .empty-state h3 {
        color: #1a1a2e;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .empty-state p {
        color: #4a5568;
    }
</style>

<div class="container" style="padding: 2rem 0;">
    <!-- Header with Create Button -->
    <div class="header-section">
        <h1><i class="fas fa-music" style="color: #D3A5A5;"></i> Upcoming Concerts</h1>
        @auth
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('concerts.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i> Add Concert
                </a>
            @endif
        @endauth
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-box">
        <h3><i class="fas fa-filter"></i> Search & Filter</h3>
        <form action="{{ route('concerts.filter') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <input 
                        type="text" 
                        name="artist" 
                        class="form-control filter-input" 
                        placeholder="Search by artist or title"
                        value="{{ request('artist') }}"
                    >
                </div>
                <div class="col-md-3">
                    <input 
                        type="date" 
                        name="date" 
                        class="form-control filter-input" 
                        value="{{ request('date') }}"
                    >
                </div>
                <div class="col-md-3">
                    <input 
                        type="text" 
                        name="venue" 
                        class="form-control filter-input" 
                        placeholder="Filter by venue"
                        value="{{ request('venue') }}"
                    >
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn-filter w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Concerts Grid -->
    @if($concerts->count() > 0)
        <div class="concerts-grid-container">
            @forelse($concerts as $concert)
                <div class="concert-card" onclick="window.location.href='{{ route('concerts.show', $concert) }}'" style="cursor: pointer;">
                    <!-- Heart Button - Top Right -->
                    @auth
                        <button type="button" class="concert-heart-btn @if(auth()->user()->hasFavourited($concert->id)) active @endif" 
                                onclick="toggleFavourite(event, {{ $concert->id }}, this);" 
                                title="Add to favourites">
                            <i class="fas fa-heart"></i>
                        </button>
                    @else
                        <button type="button" class="concert-heart-btn" disabled 
                                title="Please log in to add to favourites"
                                style="opacity: 0.5; cursor: not-allowed;">
                            <i class="fas fa-heart"></i>
                        </button>
                    @endauth

                    <!-- Card Header with Image -->
                    <div class="concert-card-header {{ $concert->image_path ? 'has-image' : '' }}">
                        @if($concert->image_path)
                            <img src="{{ asset('storage/' . $concert->image_path) }}" alt="{{ $concert->title }}">
                        @else
                            <i class="fas fa-microphone"></i>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="concert-card-body">
                        <h4 class="concert-title" title="{{ $concert->title }}">{{ $concert->title }}</h4>
                        <p class="concert-artist">
                            <i class="fas fa-user-music"></i> {{ $concert->artist }}
                        </p>

                        <!-- Concert Details -->
                        <div class="concert-details">
                            <div class="concert-detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ \Carbon\Carbon::parse($concert->date)->format('M d, Y') }}</span>
                            </div>
                            <div class="concert-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $concert->venue }}</span>
                            </div>
                            <div class="concert-detail-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span>${{ number_format($concert->ticket_price, 2) }}</span>
                            </div>

                            <!-- Availability Badge -->
                            @php
                                $bookedTickets = $concert->orders()
                                    ->where('status', 'confirmed')
                                    ->sum('quantity');
                                $availableTickets = $concert->total_ticket - $bookedTickets;
                            @endphp

                            <div class="ticket-availability @if($availableTickets <= 0) sold-out @endif">
                                @if($availableTickets > 0)
                                    <i class="fas fa-check-circle"></i> {{ $availableTickets }} Available
                                @else
                                    <i class="fas fa-times-circle"></i> Sold Out
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-music"></i>
                    <h3>No Concerts Found</h3>
                    <p>There are currently no concerts available. Check back soon for upcoming events!</p>
                </div>
            @endforelse
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-music"></i>
            <h3>No Concerts Found</h3>
            <p>There are currently no concerts available. Check back soon for upcoming events!</p>
        </div>
    @endif

    <!-- Pagination -->
    @if($concerts->hasPages())
        <div style="display: flex; justify-content: center;">
            {{ $concerts->links() }}
        </div>
    @endif
</div>

<script>
function toggleFavourite(event, concertId, button) {
    event.preventDefault();
    event.stopPropagation();

    fetch(`/concerts/${concertId}/toggle-favourite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                alert('Please log in to add concerts to favourites.');
                window.location.href = '/login';
                return;
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'favourited') {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endsection
