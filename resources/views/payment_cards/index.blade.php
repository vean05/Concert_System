@extends('layouts.app')

@section('title', 'Payment Cards')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 760px">
            <div class="page-head">
                <h1>Payment cards</h1>
                <a class="btn btn-accent" href="{{ route('payment_cards.create') }}"><i class="fas fa-plus"></i> Add card</a>
            </div>

            @forelse($cards as $card)
                <div class="pay-option justify-content-between" style="cursor: default">
                    <div class="d-flex align-items-center gap-3">
                        <i class="far fa-credit-card"></i>
                        <span>{{ $card->brand }} ending in <span class="mono">{{ $card->last_four }}</span>
                            <small class="muted d-block">{{ $card->full_name }} · Expires {{ $card->expiry_date }}</small></span>
                    </div>
                    <form action="{{ route('payment_cards.destroy', $card) }}" method="POST" data-confirm="Delete this card?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger-ghost btn-sm" type="submit">Delete</button>
                    </form>
                </div>
            @empty
                <div class="empty">
                    <h3>No saved cards</h3>
                    <p>Add a card to check out faster.</p>
                    <a class="btn btn-accent" href="{{ route('payment_cards.create') }}">Add card</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
