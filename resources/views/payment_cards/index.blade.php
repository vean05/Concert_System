@extends('layouts.app')

@section('title', 'Payment Cards - ConcertHub')

@section('content')
<style>
    .page-container { padding: 2rem 0; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #5BA3C0;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-link:hover { transform: translateX(-5px); color: #4A8FA3; }

    .btn-create {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-create:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(91,163,192,0.3); color: white; }

    .card-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .payment-card {
        background: linear-gradient(135deg, #2c3e50 0%, #1a1a2e 100%);
        color: white;
        border-radius: 16px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    .payment-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.25); }

    .card-bg-icon {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 8rem;
        opacity: 0.05;
        z-index: 0;
    }

    .card-content { position: relative; z-index: 1; }

    .card-type-icon { font-size: 2.5rem; margin-bottom: 1rem; }
    .visa { color: #1a1f71; background: white; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; }
    .master { color: #eb001b; background: white; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; }

    .card-number { font-size: 1.4rem; font-family: monospace; letter-spacing: 2px; margin-bottom: 1.5rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); }

    .card-details { display: flex; justify-content: space-between; align-items: flex-end; }
    .card-detail-group { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; color: #a0aec0; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.2rem; }
    .detail-value { font-size: 1rem; font-weight: 600; text-transform: uppercase; }

    .delete-btn-wrapper { margin-top: 1.5rem; text-align: right; }
    .btn-delete {
        background: rgba(220, 53, 69, 0.2);
        color: #ff6b6b;
        border: 1px solid rgba(220, 53, 69, 0.4);
        border-radius: 6px;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-delete:hover { background: #dc3545; color: white; }

    .empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    .empty-state i { font-size: 3rem; color: #5BA3C0; margin-bottom: 1rem; display: block; opacity: 0.5; }
    .empty-state h3 { color: #2c3e50; font-weight: 700; margin-bottom: 1rem;}

    .alert-success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #c3e6cb; }
</style>

<div class="page-container container">
    <a href="{{ route('profile.show') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>

    <div class="page-header">
        <h1><i class="fas fa-credit-card" style="color:#5BA3C0;"></i> My Payment Cards</h1>
        <a href="{{ route('payment_cards.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Add Card
        </a>
    </div>

    @if($cards->count() > 0)
        <div class="card-list">
            @foreach($cards as $card)
                <div class="payment-card">
                    <i class="fas fa-credit-card card-bg-icon"></i>
                    <div class="card-content">
                        <div class="card-type-icon">
                            @if($card->card_type == 'visa')
                                <span class="visa"><i class="fab fa-cc-visa"></i></span>
                            @else
                                <span class="master"><i class="fab fa-cc-mastercard"></i></span>
                            @endif
                        </div>
                        
                        @php
                            // Mask the card number, showing only the last 4 digits
                            $maskedNumber = '**** **** **** ' . substr($card->card_number, -4);
                        @endphp
                        <div class="card-number">{{ $maskedNumber }}</div>
                        
                        <div class="card-details">
                            <div class="card-detail-group">
                                <span class="detail-label">Card Holder</span>
                                <span class="detail-value">{{ $card->full_name }}</span>
                            </div>
                            <div class="card-detail-group" style="text-align: right;">
                                <span class="detail-label">Expires</span>
                                <span class="detail-value">{{ $card->expiry_date }}</span>
                            </div>
                        </div>

                        <div class="delete-btn-wrapper">
                            <form action="{{ route('payment_cards.destroy', $card) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this card?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-credit-card"></i>
            <h3>No Payment Cards Found</h3>
            <p style="color: #666; margin-bottom: 1.5rem;">You haven't added any payment cards yet. Add a card to speed up your checkout process.</p>
            <a href="{{ route('payment_cards.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Add Your First Card
            </a>
        </div>
    @endif
</div>
@endsection
