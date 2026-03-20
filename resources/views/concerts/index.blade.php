@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Concerts</h1>
        </div>
        <div class="col-md-4 text-right">
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('concerts.create') }}" class="btn btn-primary">Create Concert</a>
                @endif
            @endauth
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('concerts.filter') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="artist" class="form-control" placeholder="Search by artist or title" 
                                   value="{{ request('artist') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="venue" class="form-control" placeholder="Filter by venue"
                                   value="{{ request('venue') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Concerts Display -->
    <div class="row">
        @forelse($concerts as $concert)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $concert->title }}</h5>
                        <p class="card-text">
                            <strong>Artist:</strong> {{ $concert->artist }}<br>
                            <strong>Venue:</strong> {{ $concert->venue }}<br>
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($concert->date)->format('M d, Y') }}<br>
                            <strong>Price:</strong> ${{ number_format($concert->ticket_price, 2) }}<br>
                            <strong>Available Tickets:</strong> 
                            @php
                                $bookedTickets = $concert->orders()
                                    ->where('status', 'confirmed')
                                    ->sum('quantity');
                                $availableTickets = $concert->total_ticket - $bookedTickets;
                            @endphp
                            {{ $availableTickets }} / {{ $concert->total_ticket }}
                        </p>
                        <p class="card-text text-muted">{{ Str::limit($concert->description, 100) }}</p>
                        
                        <div class="btn-group" role="group">
                            <a href="{{ route('concerts.show', $concert) }}" class="btn btn-info btn-sm">View Details</a>
                            
                            @auth
                                @if(auth()->user()->role === 'admin' && auth()->user()->id === $concert->created_by)
                                    <a href="{{ route('concerts.edit', $concert) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('concerts.destroy', $concert) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this concert?')">Delete</button>
                                    </form>
                                @elseif(auth()->user()->role === 'user' && $availableTickets > 0)
                                    <a href="{{ route('orders.create', $concert) }}" class="btn btn-success btn-sm">Book Ticket</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="alert alert-info">No concerts available.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-md-12">
            {{ $concerts->links() }}
        </div>
    </div>
</div>
@endsection
