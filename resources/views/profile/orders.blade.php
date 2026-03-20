@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>My Orders</h1>
        </div>
        <div class="col-md-4">
            <a href="{{ route('profile.show') }}" class="btn btn-secondary float-end">Back to Profile</a>
        </div>
    </div>

    <div class="row">
        @forelse($orders as $order)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $order->concert->title }}</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Artist:</strong> {{ $order->concert->artist }}</p>
                        <p><strong>Venue:</strong> {{ $order->concert->venue }}</p>
                        <p><strong>Concert Date:</strong> {{ \Carbon\Carbon::parse($order->concert->date)->format('F d, Y') }}</p>
                        <p><strong>Booked Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
                        <p><strong>Quantity:</strong> {{ $order->quantity }} tickets</p>
                        <p><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</p>
                        
                        @if($order->status === 'confirmed')
                            <span class="badge bg-success">Confirmed</span>
                        @else
                            <span class="badge bg-danger">Cancelled</span>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm">View Details</a>
                            <a href="{{ route('concerts.show', $order->concert) }}" class="btn btn-primary btn-sm">View Concert</a>
                            
                            @if($order->status === 'confirmed')
                                <form action="{{ route('orders.cancel', $order) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this order?')">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    You haven't booked any concerts yet. <a href="{{ route('concerts.index') }}">Browse concerts</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orders->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
