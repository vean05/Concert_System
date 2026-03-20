@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="mb-4">Book Tickets for {{ $concert->title }}</h1>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Concert Details</h5>
                    <p><strong>Artist:</strong> {{ $concert->artist }}</p>
                    <p><strong>Venue:</strong> {{ $concert->venue }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($concert->date)->format('F d, Y') }}</p>
                    <p><strong>Price per Ticket:</strong> ${{ number_format($concert->ticket_price, 2) }}</p>
                    
                    @php
                        $bookedTickets = $concert->orders()
                            ->where('status', 'confirmed')
                            ->sum('quantity');
                        $availableTickets = $concert->total_ticket - $bookedTickets;
                    @endphp
                    <p><strong>Available Tickets:</strong> {{ $availableTickets }} / {{ $concert->total_ticket }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('orders.store', $concert) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Number of Tickets</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" name="quantity" min="1" max="{{ $availableTickets }}" 
                                   value="{{ old('quantity', 1) }}" required onchange="updateTotal()">
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Max {{ $availableTickets }} tickets available</small>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6>Total Price: <strong id="totalPrice">${{ number_format($concert->ticket_price, 2) }}</strong></h6>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg w-100">Confirm Booking</button>
                            <a href="{{ route('concerts.show', $concert) }}" class="btn btn-secondary w-100 mt-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTotal() {
        const quantity = document.getElementById('quantity').value;
        const pricePerTicket = {{ $concert->ticket_price }};
        const totalPrice = (quantity * pricePerTicket).toFixed(2);
        document.getElementById('totalPrice').textContent = '$' + totalPrice;
    }
</script>
@endsection
