@extends('layouts.app')

@section('title', 'Book Tickets - ConcertHub')

@section('content')
<style>
    .booking-container {
        padding: 2rem 0;
    }

    .booking-header {
        margin-bottom: 2rem;
    }

    .booking-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .concert-banner {
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .concert-banner-icon {
        font-size: 3rem;
        opacity: 0.9;
    }

    .concert-banner-text h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.3rem;
        font-weight: 700;
    }

    .concert-banner-text p {
        margin: 0.3rem 0;
        opacity: 0.95;
    }

    .booking-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }

    .booking-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .booking-card-header h5 {
        color: #2c3e50;
        font-weight: 700;
        margin: 0 0 1.5rem 0;
        font-size: 1.1rem;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .detail-item {
        display: flex;
        align-items: center;
    }

    .detail-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ff6b35 0%, #d94a2a 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
    }

    .detail-text h6 {
        color: #666;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        margin: 0 0 0.3rem 0;
    }

    .detail-text p {
        color: #2c3e50;
        font-weight: 600;
        margin: 0;
    }

    .booking-card-body {
        padding: 2rem;
    }

    .error-alert {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: none;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .error-alert i {
        margin-right: 0.5rem;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .error-alert li {
        margin-bottom: 0.3rem;
    }

    .error-alert li:last-child {
        margin-bottom: 0;
    }

    .form-label {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .form-label i {
        color: #ff6b35;
        margin-right: 0.5rem;
    }

    .quantity-input {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.8rem 1rem;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
    }

    .quantity-input:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        outline: none;
    }

    .help-text {
        color: #666;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    .price-summary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 2rem 0;
    }

    .price-summary h6 {
        color: #666;
        font-size: 0.85rem;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .price-summary .price-value {
        font-size: 2rem;
        font-weight: 700;
        color: #ff6b35;
        margin: 0;
    }

    .price-breakdown {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e0e0e0;
    }

    .breakdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .breakdown-label {
        color: #666;
        font-size: 0.9rem;
    }

    .breakdown-value {
        color: #2c3e50;
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-direction: column;
    }

    .action-buttons a,
    .action-buttons button {
        padding: 1rem;
        border-radius: 8px;
        border: none;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }

    .btn-confirm {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    }

    .btn-cancel {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        color: white;
        text-decoration: none;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: block;
    }

    @media (max-width: 768px) {
        .concert-banner {
            flex-direction: column;
            text-align: center;
        }

        .detail-row {
            grid-template-columns: 1fr;
        }

        .price-breakdown {
            grid-template-columns: 1fr;
        }

        .booking-header h1 {
            font-size: 1.6rem;
        }
    }
</style>

<div class="booking-container container">
    <!-- Header -->
    <div class="booking-header">
        <h1><i class="fas fa-ticket-alt" style="color: #7c3aed;"></i> Book Your Tickets</h1>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="error-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Concert Banner -->
    <div class="concert-banner">
        <div class="concert-banner-icon">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="concert-banner-text">
            <h3>{{ $concert->title }}</h3>
            <p><i class="fas fa-user-music"></i> {{ $concert->artist }}</p>
            <p><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($concert->date)->format('F d, Y') }}</p>
            <p><i class="fas fa-map-marker-alt"></i> {{ $concert->venue }}</p>
        </div>
    </div>

    <!-- Concert Details Card -->
    <div class="booking-card">
        <div class="booking-card-header">
            <h5><i class="fas fa-info-circle"></i> Concert Details</h5>
            <div class="detail-row">
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="detail-text">
                        <h6>Price per Ticket</h6>
                        <p>${{ number_format($concert->ticket_price, 2) }}</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        @php
                            $bookedTickets = $concert->orders()
                                ->where('status', 'confirmed')
                                ->sum('quantity');
                            $availableTickets = $concert->total_ticket - $bookedTickets;
                        @endphp
                        @if($availableTickets > 0)
                            <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        @else
                            <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                        @endif
                    </div>
                    <div class="detail-text">
                        <h6>Available Tickets</h6>
                        <p>{{ $availableTickets }} of {{ $concert->total_ticket }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Form -->
    <div class="booking-card">
        <div class="booking-card-body">
            <form action="{{ route('orders.store', $concert) }}" method="POST" id="bookingForm">
                @csrf

                <!-- Quantity Selection -->
                <div class="mb-4">
                    <label for="quantity" class="form-label">
                        <i class="fas fa-shopping-bag"></i> Number of Tickets
                    </label>
                    <input 
                        type="number" 
                        class="quantity-input @error('quantity') is-invalid @enderror" 
                        id="quantity" 
                        name="quantity" 
                        min="1" 
                        max="{{ $availableTickets }}" 
                        value="{{ old('quantity', 1) }}" 
                        required
                        onchange="updateTotal()"
                        oninput="updateTotal()"
                    >
                    <div class="help-text">
                        <i class="fas fa-info-circle"></i> Maximum {{ $availableTickets }} tickets available
                    </div>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Price Summary -->
                <div class="price-summary">
                    <h6>Total Price</h6>
                    <p class="price-value" id="totalPrice">${{ number_format($concert->ticket_price, 2) }}</p>
                    <div class="price-breakdown">
                        <div class="breakdown-item">
                            <span class="breakdown-label">Price per Ticket:</span>
                            <span class="breakdown-value">${{ number_format($concert->ticket_price, 2) }}</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Quantity:</span>
                            <span class="breakdown-value" id="quantityDisplay">1</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn-confirm" id="confirmBtn">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                    <a href="{{ route('concerts.show', $concert) }}" class="btn-cancel">
                        <i class="fas fa-times-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const pricePerTicket = {{ $concert->ticket_price }};

    function updateTotal() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const totalPrice = (quantity * pricePerTicket).toFixed(2);
        
        document.getElementById('totalPrice').textContent = '$' + new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(totalPrice);
        
        document.getElementById('quantityDisplay').textContent = quantity;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateTotal();
    });
</script>
@endsection
