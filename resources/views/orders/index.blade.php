@extends('layouts.app')

@section('title', 'My Orders - ConcertHub')

@section('content')
<style>
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .order-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .order-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 5px solid #7c3aed;
    }

    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .order-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .order-card-header h5 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }

    .order-card-artist {
        color: #ff6b35;
        font-weight: 600;
        font-size: 0.95rem;
        margin: 0;
    }

    .order-card-body {
        padding: 1.5rem;
    }

    .order-detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .order-detail-item {
        display: flex;
        align-items: center;
    }

    .order-detail-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .order-detail-text h6 {
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
        margin: 0 0 0.3rem 0;
    }

    .order-detail-text p {
        color: #2c3e50;
        font-weight: 600;
        margin: 0;
    }

    .status-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-confirmed {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }

    .status-cancelled {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }

    .order-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .order-actions a,
    .order-actions button {
        flex: 1;
        padding: 0.7rem;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
    }

    .btn-view-order {
        background: linear-gradient(135deg, #004e89 0%, #0077b6 100%);
        color: white;
    }

    .btn-view-order:hover {
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

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 3rem;
        color: #ff6b35;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #2c3e50;
        font-weight: 700;
    }

    .empty-state a {
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        margin-top: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .empty-state a:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
        color: white;
        text-decoration: none;
    }

    .alert-custom {
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: none;
    }

    .alert-success-custom {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }

    .alert-danger-custom {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }

    .pagination {
        margin-top: 2rem;
    }

    .page-link {
        color: #ff6b35;
        border-color: #e0e0e0;
    }

    .page-link:hover {
        color: #d94a2a;
        background-color: #fff8f5;
    }

    .page-item.active .page-link {
        background-color: #7c3aed;
        border-color: #7c3aed;
    }

    @media (max-width: 768px) {
        .order-detail-row {
            grid-template-columns: 1fr;
        }

        .order-actions {
            flex-direction: column;
        }

        .order-header h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="container" style="padding: 2rem 0;">
    <!-- Header -->
    <div class="order-header">
        <h1><i class="fas fa-receipt" style="color: #7c3aed;"></i> My Orders</h1>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert-custom alert-success-custom">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-custom alert-danger-custom">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Orders List -->
    @forelse($orders as $order)
        <div class="order-card mb-4">
            <!-- Header -->
            <div class="order-card-header">
                <h5>{{ $order->concert->title }}</h5>
                <p class="order-card-artist">
                    <i class="fas fa-user-music"></i> {{ $order->concert->artist }}
                </p>
            </div>

            <!-- Body -->
            <div class="order-card-body">
                <div class="order-detail-row">
                    <div class="order-detail-item">
                        <div class="order-detail-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="order-detail-text">
                            <h6>Concert Date</h6>
                            <p>{{ \Carbon\Carbon::parse($order->concert->date)->format('F d, Y') }}</p>
                        </div>
                    </div>

                    <div class="order-detail-item">
                        <div class="order-detail-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="order-detail-text">
                            <h6>Tickets</h6>
                            <p>{{ $order->quantity }} @if($order->quantity > 1) tickets @else ticket @endif</p>
                        </div>
                    </div>
                </div>

                <div class="order-detail-row">
                    <div class="order-detail-item">
                        <div class="order-detail-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="order-detail-text">
                            <h6>Total Price</h6>
                            <p>${{ number_format($order->total_price, 2) }}</p>
                        </div>
                    </div>

                    <div class="order-detail-item">
                        <div class="order-detail-icon">
                            @if($order->status === 'confirmed')
                                <i class="fas fa-check-circle"></i>
                            @else
                                <i class="fas fa-times-circle"></i>
                            @endif
                        </div>
                        <div class="order-detail-text">
                            <h6>Status</h6>
                            <span class="status-badge @if($order->status === 'confirmed') status-confirmed @else status-cancelled @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="order-actions">
                    <a href="{{ route('orders.show', $order) }}" class="btn-view-order">
                        <i class="fas fa-eye"></i> View Details
                    </a>

                    @if($order->status === 'confirmed')
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn-cancel-order" style="width: 100%;" onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    @empty
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <h3>No Orders Yet</h3>
            <p>You haven't booked any concerts yet.</p>
            <a href="{{ route('concerts.index') }}">
                <i class="fas fa-music"></i> Browse Concerts
            </a>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($orders->count() > 0)
        <div class="mt-5">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
