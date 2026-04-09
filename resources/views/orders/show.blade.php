@extends('layouts.app')

@section('title', 'Order Details - ConcertHub')

@section('content')
<style>
    .order-detail-container {
        padding: 2rem 0;
    }

    .order-detail-header {
        margin-bottom: 2rem;
    }

    .order-detail-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .order-detail-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .order-detail-header-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 2rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .order-header-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .order-header-item {
        display: flex;
        align-items: center;
    }

    .order-header-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        font-size: 1.3rem;
    }

    .order-header-text h6 {
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }

    .order-header-text p {
        color: #2c3e50;
        font-weight: 600;
        margin: 0;
    }

    .status-badge {
        display: inline-block;
        padding: 0.5rem 1.2rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .status-confirmed {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }

    .status-cancelled {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }

    .order-detail-body {
        padding: 2rem;
    }

    .detail-section {
        margin-bottom: 2rem;
    }

    .detail-section:last-of-type {
        margin-bottom: 0;
    }

    .detail-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .detail-section-title i {
        color: #ff6b35;
        margin-right: 0.7rem;
        font-size: 1.2rem;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .detail-row.full {
        grid-template-columns: 1fr;
    }

    .detail-item-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-radius: 8px;
    }

    .detail-item-box h6 {
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }

    .detail-item-box p {
        color: #2c3e50;
        font-weight: 600;
        margin: 0;
        font-size: 1rem;
    }

    .detail-item-box p.large {
        font-size: 1.5rem;
    }

    .concert-link {
        color: #ff6b35;
        text-decoration: none;
        font-weight: 700;
    }

    .concert-link:hover {
        text-decoration: underline;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f0f0f0;
    }

    .action-buttons a,
    .action-buttons button {
        flex: 1;
        padding: 0.9rem 1.5rem;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
    }

    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
    }

    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-view-concert {
        background: linear-gradient(135deg, #004e89 0%, #0077b6 100%);
        color: white;
    }

    .btn-view-concert:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 78, 137, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-cancel-order {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-cancel-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
    }

    @media (max-width: 768px) {
        .order-header-row,
        .detail-row {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .order-detail-header h1 {
            font-size: 1.6rem;
        }
    }
</style>

<div class="order-detail-container container">
    <!-- Header -->
    <div class="order-detail-header">
        <h1><i class="fas fa-receipt" style="color: #7c3aed;"></i> Order #{{ $order->id }}</h1>
    </div>

    <!-- Order Detail Card -->
    <div class="order-detail-card">
        <!-- Header Section -->
        <div class="order-detail-header-section">
            <div class="order-header-row">
                <div class="order-header-item">
                    <div class="order-header-icon">
                        @if($order->status === 'confirmed')
                            <i class="fas fa-check-circle"></i>
                        @else
                            <i class="fas fa-times-circle"></i>
                        @endif
                    </div>
                    <div class="order-header-text">
                        <h6>Order Status</h6>
                        <span class="status-badge @if($order->status === 'confirmed') status-confirmed @else status-cancelled @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                <div class="order-header-item">
                    <div class="order-header-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="order-header-text">
                        <h6>Order Date</h6>
                        <p>{{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body Section -->
        <div class="order-detail-body">
            <!-- Concert Information -->
            <div class="detail-section">
                <h3 class="detail-section-title">
                    <i class="fas fa-microphone"></i> Concert Information
                </h3>

                <div class="detail-row">
                    <div class="detail-item-box">
                        <h6>Concert Title</h6>
                        <p><a href="{{ route('concerts.show', $order->concert) }}" class="concert-link">{{ $order->concert->title }}</a></p>
                    </div>
                    <div class="detail-item-box">
                        <h6>Artist</h6>
                        <p>{{ $order->concert->artist }}</p>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-item-box">
                        <h6>Venue</h6>
                        <p>{{ $order->concert->venue }}</p>
                    </div>
                    <div class="detail-item-box">
                        <h6>Concert Date</h6>
                        <p>{{ \Carbon\Carbon::parse($order->concert->date)->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="detail-section">
                <h3 class="detail-section-title">
                    <i class="fas fa-ticket-alt"></i> Booking Details
                </h3>

                <div class="detail-row">
                    <div class="detail-item-box">
                        <h6>Number of Tickets</h6>
                        <p>{{ $order->quantity }} @if($order->quantity > 1)tickets@else ticket@endif</p>
                    </div>
                    <div class="detail-item-box">
                        <h6>Price per Ticket</h6>
                        <p>${{ number_format($order->concert->ticket_price, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Price Summary -->
            <div class="detail-section detail-row full" style="margin: 0; padding-top: 1.5rem; border-top: 2px solid #f0f0f0;">
                <div class="detail-item-box" style="background: linear-gradient(135deg, #e7f5ff 0%, #cce5ff 100%); border: 2px solid #b3d9ff;">
                    <h6 style="color: #004e89;">Total Price</h6>
                    <p class="large" style="color: #004e89;">${{ number_format($order->total_price, 2) }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('orders.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <a href="{{ route('concerts.show', $order->concert) }}" class="btn-view-concert">
                    <i class="fas fa-eye"></i> View Concert
                </a>
                @if($order->status === 'confirmed')
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" style="flex: 1;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-cancel-order" style="width: 100%;" onclick="return confirm('Are you sure you want to cancel this order?')">
                            <i class="fas fa-times-circle"></i> Cancel Order
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
