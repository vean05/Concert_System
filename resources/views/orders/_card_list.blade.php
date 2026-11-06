<section class="check-step">
    <div class="check-step__head">
        <h2>Payment</h2>
        <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#addCardModal">
            <i class="fas fa-plus"></i> Add card
        </button>
    </div>
    <div id="cardList">
        @foreach($paymentCards as $card)
            <label class="pay-option">
                <input type="radio" name="payment_card_id" value="{{ $card->id }}"
                       {{ (int) old('payment_card_id', optional($paymentCards->first())->id) === $card->id ? 'checked' : '' }}>
                <i class="far fa-credit-card"></i>
                <span>{{ $card->brand }} ending in <span class="mono">{{ $card->last_four }}</span>
                    <small class="muted d-block">Expires {{ $card->expiry_date }}</small></span>
            </label>
        @endforeach
    </div>
    <p class="muted mb-0" id="noCards" {{ $paymentCards->isEmpty() ? '' : 'hidden' }}>You have no saved cards yet. Add one to finish your booking.</p>
</section>
