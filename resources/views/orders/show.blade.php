@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4">Order Details</h1>

            <div class="card">
                <div class="card-header">
                    <h5>Order #{{ $order->id }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Status</h6>
                            @if($order->status === 'confirmed')
                                <span class="badge bg-success" style="font-size: 16px; padding: 10px;">Confirmed</span>
                            @else
                                <span class="badge bg-danger" style="font-size: 16px; padding: 10px;">Cancelled</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Order Date</h6>
                            <p>{{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Concert Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Concert Title:</strong> {{ $order->concert->title }}</p>
                            <p><strong>Artist:</strong> {{ $order->concert->artist }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Venue:</strong> {{ $order->concert->venue }}</p>
                            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->concert->date)->format('F d, Y') }}</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Booking Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Number of Tickets:</strong> {{ $order->quantity }}</p>
                            <p><strong>Price per Ticket:</strong> ${{ number_format($order->concert->ticket_price, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Price:</strong> <span style="font-size: 18px; font-weight: bold;">${{ number_format($order->total_price, 2) }}</span></p>
                        </div>
                    </div>

                    <hr>

                    <div class="mt-4">
                        @if($order->status === 'confirmed')
                            <form action="{{ route('orders.cancel', $order) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this order?')">Cancel Order</button>
                            </form>
                        @endif
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to Orders</a>
                        <a href="{{ route('concerts.show', $order->concert) }}" class="btn btn-info">View Concert</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
