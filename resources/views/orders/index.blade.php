@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>My Orders</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @forelse($orders as $order)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $order->concert->title }}</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Artist:</strong> {{ $order->concert->artist }}</p>
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->concert->date)->format('F d, Y') }}</p>
                        <p><strong>Quantity:</strong> {{ $order->quantity }} tickets</p>
                        <p><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</p>
                        
                        @if($order->status === 'confirmed')
                            <span class="badge bg-success">Confirmed</span>
                        @else
                            <span class="badge bg-danger">Cancelled</span>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm">View Details</a>
                            
                            @if($order->status === 'confirmed')
                                <form action="{{ route('orders.cancel', $order) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this order?')">Cancel Order</button>
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
