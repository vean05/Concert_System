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
        background: linear-gradient(135deg, #D3A5A5 0%, #C98E8E 100%);
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
        background: linear-gradient(135deg, #D3A5A5 0%, #C98E8E 100%);
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
        color: #D3A5A5;
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
        border-color: #5BA3C0;
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
        color: #D3A5A5;
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

    /* Payment Card Selection Styles */
    .payment-card-radio {
        border: 2px solid #e0e0e0; 
        border-radius: 8px; 
        padding: 1rem; 
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        gap: 1rem;
        transition: all 0.2s ease;
    }
    .payment-card-radio:hover { border-color: #5BA3C0; background: rgba(91,163,192,0.02); }
    .payment-card-radio.selected { border-color: #5BA3C0; background: rgba(91,163,192,0.05); }
    
    .btn-add-card-inline {
        width: 100%; 
        border: 2px dashed #ccc; 
        padding: 1rem; 
        background: transparent; 
        color: #5BA3C0; 
        font-weight: bold;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-add-card-inline:hover { border-color: #5BA3C0; background: rgba(91,163,192,0.05); }

    /* Add Card Modal Styles */
    .add-card-modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6); z-index: 1000;
        display: none; justify-content: center; align-items: center;
        backdrop-filter: blur(5px);
    }
    .add-card-modal {
        background: white; width: 95%; max-width: 600px; max-height: 90vh;
        border-radius: 12px; display: flex; flex-direction: column;
        overflow-y: auto; padding: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        position: relative;
    }
    .add-card-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .add-card-modal-header h3 { margin: 0; color: #1a1a2e; font-weight: 700; }
    .btn-close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }
    
    .form-group { margin-bottom: 1.2rem; position: relative; }
    .form-group label { display: block; margin-bottom: 0.4rem; font-weight: 600; color: #2c3e50; font-size: 0.9rem;}
    .form-control { width: 100%; padding: 0.7rem; border: 1px solid #ced4da; border-radius: 8px; font-size: 0.95rem; }
    .form-control:focus { outline: none; border-color: #5BA3C0; box-shadow: 0 0 0 3px rgba(91,163,192,0.1); }
    .form-control.is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
    .invalid-feedback { color: #dc3545; font-size: 0.8rem; margin-top: 0.3rem; display: none; }
    .is-invalid ~ .invalid-feedback { display: block; }
    
    .card-type-selector { display: flex; gap: 1rem; }
    .card-type-option { flex: 1; border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.8rem; text-align: center; cursor: pointer; position: relative; }
    .card-type-option input { position: absolute; opacity: 0; cursor: pointer; }
    .card-type-option i { font-size: 2rem; margin-bottom: 0.3rem; display: block; }
    .card-type-option .fa-cc-visa { color: #1a1f71; }
    .card-type-option .fa-cc-mastercard { color: #eb001b; }
    .card-type-option span { font-weight: 600; color: #4a5568; font-size: 0.9rem;}
    .card-type-option.selected { border-color: #5BA3C0; background: rgba(91,163,192,0.05); }

    .country-display { width: 100%; padding: 0.7rem; border: 1px solid #ced4da; border-radius: 8px; font-size: 0.95rem; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .country-display.is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
    
    /* Country Picker Custom UI (Z-index higher than Add Card modal) */
    .country-dropdown-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1050; display: none; justify-content: center; align-items: center; }
    .country-dropdown-modal { background: white; width: 90%; max-width: 500px; height: 80vh; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; position: relative; }
    .country-modal-header { padding: 1rem; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
    .country-modal-header h3 { margin: 0; font-size: 1.2rem; }
    .country-modal-body { display: flex; flex: 1; overflow: hidden; }
    .country-list-container { flex: 1; overflow-y: auto; padding: 1rem; scroll-behavior: smooth; }
    .alphabet-index { width: 30px; background: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: space-evenly; font-size: 0.75rem; font-weight: 600; color: #5BA3C0; border-left: 1px solid #e0e0e0; padding: 0.5rem 0; }
    .alphabet-index span { cursor: pointer; width: 100%; text-align: center; }
    .alphabet-index span:hover { background: #e9ecef; }
    .letter-group-title { background: #f0f0f0; padding: 0.2rem 0.5rem; font-weight: 700; color: #666; margin-top: 1rem; border-radius: 4px; }
    .letter-group-title:first-child { margin-top: 0; }
    .country-item { padding: 0.8rem 0.5rem; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s; }
    .country-item:hover { background: #f8f9fa; color: #5BA3C0; }
    
    .btn-save-card { background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%); color: white; border: none; border-radius: 8px; padding: 0.8rem 1.5rem; font-weight: 700; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 1rem; transition: all 0.3s ease; }
    .btn-save-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(91,163,192,0.3); }

    .row-flex { display: flex; flex-wrap: wrap; margin: 0 -10px; }
    .col-half { width: 50%; padding: 0 10px; }
</style>

<div class="booking-container container">
    <!-- Header -->
    <div class="booking-header">
        <h1><i class="fas fa-ticket-alt" style="color: #5BA3C0;"></i> Book Your Tickets</h1>
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

                <!-- Payment Method Selection -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-credit-card"></i> Payment Method
                    </label>
                    <div id="paymentCardsList" style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                        @forelse($paymentCards as $card)
                            <label class="payment-card-radio {{ $loop->first ? 'selected' : '' }}" onclick="updateRadioStyles()">
                                <input type="radio" name="payment_card_id" value="{{ $card->id }}" {{ $loop->first ? 'checked' : '' }} style="margin-right: 10px;">
                                @if($card->card_type == 'visa')
                                    <i class="fab fa-cc-visa" style="font-size: 2rem; color: #1a1f71;"></i>
                                @else
                                    <i class="fab fa-cc-mastercard" style="font-size: 2rem; color: #eb001b;"></i>
                                @endif
                                <div>
                                    <strong style="text-transform: uppercase;">{{ $card->card_type }}</strong> ending in {{ substr($card->card_number, -4) }}<br>
                                    <small style="color: #666;">Expires: {{ $card->expiry_date }}</small>
                                </div>
                            </label>
                        @empty
                            <div id="noCardsMsg" style="color: #666; font-style: italic; padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
                                You have no saved payment cards. Please add a card to proceed.
                            </div>
                        @endforelse
                    </div>
                    
                    <button type="button" class="btn-add-card-inline" onclick="openAddCardModal()">
                        <i class="fas fa-plus"></i> Add New Card
                    </button>
                    @error('payment_card_id')
                        <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
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

<!-- Add Card Modal -->
<div class="add-card-modal-overlay" id="addCardModal" onclick="closeAddCardModal(event)">
    <div class="add-card-modal" onclick="event.stopPropagation()">
        <div class="add-card-modal-header">
            <h3><i class="fas fa-plus-circle" style="color:#5BA3C0;"></i> Add Payment Card</h3>
            <button type="button" class="btn-close-modal" onclick="closeAddCardModal()"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="ajaxCardForm">
            <!-- Card Type -->
            <div class="form-group">
                <label>Card Type <span style="color:red;">*</span></label>
                <div class="card-type-selector" id="cardTypeSelector">
                    <label class="card-type-option" id="opt-visa">
                        <input type="radio" name="card_type" value="visa" class="d-none">
                        <i class="fab fa-cc-visa"></i>
                        <span>Visa</span>
                    </label>
                    <label class="card-type-option" id="opt-master">
                        <input type="radio" name="card_type" value="master" class="d-none">
                        <i class="fab fa-cc-mastercard"></i>
                        <span>Master</span>
                    </label>
                </div>
                <div class="invalid-feedback" id="err-card_type">Please select a card type.</div>
            </div>

            <!-- Card Number -->
            <div class="form-group">
                <label for="card_number">Card Number <span style="color:red;">*</span></label>
                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="e.g. 4111222233334444" maxlength="19">
                <div class="invalid-feedback">Please enter a valid card number (digits only).</div>
            </div>

            <div class="row-flex">
                <!-- Expiry Date -->
                <div class="form-group col-half">
                    <label for="expiry_date">Expiry (MM/YY) <span style="color:red;">*</span></label>
                    <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/YY" maxlength="5">
                    <div class="invalid-feedback">Invalid expiry date.</div>
                </div>

                <!-- CVV -->
                <div class="form-group col-half">
                    <label for="cvv">CVV <span style="color:red;">*</span></label>
                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" maxlength="4">
                    <div class="invalid-feedback">Invalid CVV.</div>
                </div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name">Cardholder Name <span style="color:red;">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="John Doe">
                <div class="invalid-feedback">Please enter the cardholder's name.</div>
            </div>

            <!-- Country -->
            <div class="form-group country-selector">
                <label>Country <span style="color:red;">*</span></label>
                <input type="hidden" name="country" id="country_input">
                <div class="country-display" id="country_display" onclick="openCountryModal()">
                    <span id="country_text" style="color: #6c757d;">Select a country...</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="invalid-feedback" id="err-country">Please select a country.</div>
            </div>

            <!-- Address -->
            <div class="form-group">
                <label for="address">Billing Address <span style="color:red;">*</span></label>
                <textarea id="address" name="address" class="form-control" rows="2" placeholder="Enter your full billing address"></textarea>
                <div class="invalid-feedback">Please enter your billing address.</div>
            </div>

            <button type="button" class="btn-save-card" id="saveCardBtn" onclick="submitAjaxCard()">
                <i class="fas fa-save"></i> Save & Select Card
            </button>
            <div id="ajaxCardError" style="color: #dc3545; font-size: 0.85rem; margin-top: 1rem; display: none; text-align: center;"></div>
        </form>
    </div>
</div>

<!-- Country Dropdown Modal -->
<div class="country-dropdown-overlay" id="countryModal" onclick="closeCountryModal(event)">
    <div class="country-dropdown-modal" onclick="event.stopPropagation()">
        <div class="country-modal-header">
            <h3>Select Country</h3>
            <button class="btn-close-modal" onclick="closeCountryModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="country-modal-body">
            <div class="country-list-container" id="countryList"></div>
            <div class="alphabet-index" id="alphabetIndex"></div>
        </div>
    </div>
</div>

<script>
    const pricePerTicket = {{ $concert->ticket_price }};
    const userHasCards = {{ $paymentCards->count() > 0 ? 'true' : 'false' }};

    function updateTotal() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const totalPrice = (quantity * pricePerTicket).toFixed(2);
        
        document.getElementById('totalPrice').textContent = '$' + new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(totalPrice);
        
        document.getElementById('quantityDisplay').textContent = quantity;
    }

    function updateRadioStyles() {
        document.querySelectorAll('.payment-card-radio').forEach(label => {
            label.classList.remove('selected');
            if (label.querySelector('input').checked) {
                label.classList.add('selected');
            }
        });
    }

    // Modal logic for Add Card
    function openAddCardModal() {
        document.getElementById('addCardModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeAddCardModal(event) {
        if (!event || event.target === document.getElementById('addCardModal')) {
            document.getElementById('addCardModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Country Modal Logic
    const countries = [
        "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan",
        "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi",
        "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czechia",
        "Denmark", "Djibouti", "Dominica", "Dominican Republic",
        "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia",
        "Fiji", "Finland", "France",
        "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana",
        "Haiti", "Honduras", "Hungary",
        "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy",
        "Jamaica", "Japan", "Jordan",
        "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan",
        "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
        "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar",
        "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway",
        "Oman",
        "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
        "Qatar",
        "Romania", "Russia", "Rwanda",
        "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria",
        "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu",
        "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan",
        "Vanuatu", "Vatican City", "Venezuela", "Vietnam",
        "Yemen",
        "Zambia", "Zimbabwe"
    ];

    function initCountryPicker() {
        const countryList = document.getElementById('countryList');
        const alphabetIndex = document.getElementById('alphabetIndex');
        const grouped = {};
        countries.forEach(c => {
            const firstLetter = c.charAt(0).toUpperCase();
            if (!grouped[firstLetter]) grouped[firstLetter] = [];
            grouped[firstLetter].push(c);
        });

        let listHtml = '';
        let alphabetHtml = '';
        for (let i = 65; i <= 90; i++) {
            const letter = String.fromCharCode(i);
            alphabetHtml += `<span onclick="scrollToLetter('${letter}')">${letter}</span>`;
            if (grouped[letter]) {
                listHtml += `<div class="letter-group-title" id="group-${letter}">${letter}</div>`;
                grouped[letter].forEach(country => {
                    listHtml += `<div class="country-item" onclick="selectCountry('${country}')">${country}</div>`;
                });
            }
        }
        countryList.innerHTML = listHtml;
        alphabetIndex.innerHTML = alphabetHtml;
    }

    function openCountryModal() {
        document.getElementById('countryModal').style.display = 'flex';
        document.getElementById('country_display').classList.remove('is-invalid');
    }

    function closeCountryModal(event) {
        if (!event || event.target === document.getElementById('countryModal')) {
            document.getElementById('countryModal').style.display = 'none';
        }
    }

    function selectCountry(country) {
        document.getElementById('country_input').value = country;
        document.getElementById('country_text').innerText = country;
        document.getElementById('country_text').style.color = '#2c3e50';
        closeCountryModal();
    }

    function scrollToLetter(letter) {
        const target = document.getElementById('group-' + letter);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Input Validation & Formatting setup for Modal
    document.querySelectorAll('.card-type-option').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.card-type-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            document.getElementById('cardTypeSelector').classList.remove('is-invalid');
            document.getElementById('err-card_type').style.display = 'none';
        });
    });

    document.getElementById('card_number').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, ''); 
        this.classList.remove('is-invalid');
    });

    document.getElementById('expiry_date').addEventListener('input', function(e) {
        let val = this.value.replace(/\D/g, ''); 
        if (val.length > 2) { val = val.substring(0, 2) + '/' + val.substring(2, 4); }
        this.value = val;
        this.classList.remove('is-invalid');
    });

    document.getElementById('cvv').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, ''); 
        this.classList.remove('is-invalid');
    });

    document.querySelectorAll('#ajaxCardForm input, #ajaxCardForm textarea').forEach(el => {
        el.addEventListener('input', function() { this.classList.remove('is-invalid'); });
    });

    // AJAX Form Submission
    async function submitAjaxCard() {
        let isValid = true;
        const form = document.getElementById('ajaxCardForm');
        
        // Client Validation
        const cardType = form.querySelector('input[name="card_type"]:checked');
        if (!cardType) { document.getElementById('cardTypeSelector').classList.add('is-invalid'); document.getElementById('err-card_type').style.display = 'block'; isValid = false; }
        
        const cardNo = document.getElementById('card_number');
        if (!/^\d{13,19}$/.test(cardNo.value)) { cardNo.classList.add('is-invalid'); isValid = false; }
        
        const expiry = document.getElementById('expiry_date');
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry.value)) { expiry.classList.add('is-invalid'); isValid = false; }
        
        const cvv = document.getElementById('cvv');
        if (!/^\d{3,4}$/.test(cvv.value)) { cvv.classList.add('is-invalid'); isValid = false; }
        
        const fullName = document.getElementById('full_name');
        if (fullName.value.trim() === '') { fullName.classList.add('is-invalid'); isValid = false; }
        
        const country = document.getElementById('country_input');
        if (country.value.trim() === '') { document.getElementById('country_display').classList.add('is-invalid'); document.getElementById('err-country').style.display = 'block'; isValid = false; }
        
        const address = document.getElementById('address');
        if (address.value.trim() === '') { address.classList.add('is-invalid'); isValid = false; }

        if (!isValid) return;

        // Submit via Fetch
        const btn = document.getElementById('saveCardBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        document.getElementById('ajaxCardError').style.display = 'none';

        try {
            const formData = new FormData(form);
            const response = await fetch("{{ route('payment_cards.storeAjax') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: formData
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                // Remove the "No cards found" message if it exists
                const noCardsMsg = document.getElementById('noCardsMsg');
                if (noCardsMsg) noCardsMsg.remove();

                // Append the new card to the list
                const list = document.getElementById('paymentCardsList');
                const cardHtml = `
                    <label class="payment-card-radio selected" onclick="updateRadioStyles()">
                        <input type="radio" name="payment_card_id" value="${data.card.id}" checked style="margin-right: 10px;">
                        <i class="fab fa-cc-${data.card.card_type === 'visa' ? 'visa' : 'mastercard'}" style="font-size: 2rem; color: ${data.card.card_type === 'visa' ? '#1a1f71' : '#eb001b'};"></i>
                        <div>
                            <strong style="text-transform: uppercase;">${data.card.card_type}</strong> ending in ${data.card.last_four}<br>
                            <small style="color: #666;">Expires: ${data.card.expiry_date}</small>
                        </div>
                    </label>
                `;
                
                // Deselect existing
                list.querySelectorAll('.payment-card-radio').forEach(el => {
                    el.classList.remove('selected');
                    el.querySelector('input').checked = false;
                });
                
                list.insertAdjacentHTML('beforeend', cardHtml);
                
                // Close modal & reset form
                closeAddCardModal();
                form.reset();
                document.getElementById('country_text').innerText = 'Select a country...';
                document.querySelectorAll('.card-type-option').forEach(opt => opt.classList.remove('selected'));
            } else {
                document.getElementById('ajaxCardError').innerText = data.message || 'Validation failed. Please check the inputs.';
                document.getElementById('ajaxCardError').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('ajaxCardError').innerText = 'A network error occurred.';
            document.getElementById('ajaxCardError').style.display = 'block';
        } finally {
            btn.innerHTML = '<i class="fas fa-save"></i> Save & Select Card';
            btn.disabled = false;
        }
    }

    // Form Submission check to ensure payment is selected
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const selectedCard = document.querySelector('input[name="payment_card_id"]:checked');
        if (!selectedCard) {
            e.preventDefault();
            alert('Please select a payment method before confirming the booking.');
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateTotal();
        initCountryPicker();
        updateRadioStyles();
        
        // Auto-show modal if no cards
        if (!userHasCards) {
            openAddCardModal();
        }
    });
</script>
@endsection
