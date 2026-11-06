<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const CONFIRMED = 'confirmed';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'concert_id',
        'quantity',
        'total_price',
        'discount',
        'status',
        'payment_card_id',
        'promo_code_id',
        'transferred_from_id',
        'transferred_at',
        'checked_in_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Get the user who placed this order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the concert for this order.
     */
    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    /**
     * Get the payment card used for this order.
     */
    public function paymentCard()
    {
        return $this->belongsTo(PaymentCard::class);
    }

    /**
     * Get the line items for this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the seats assigned to this order.
     */
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * The fan who sent these tickets on, if they were transferred.
     */
    public function transferredFrom()
    {
        return $this->belongsTo(User::class, 'transferred_from_id');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function isTransferred(): bool
    {
        return $this->transferred_from_id !== null || $this->transferred_at !== null;
    }

    /**
     * Refunds go to the card that paid, so a transferred ticket cannot be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === self::CONFIRMED
            && $this->concert->isUpcoming()
            && ! $this->isTransferred()
            && ! $this->checked_in_at;
    }

    public function canBeTransferred(): bool
    {
        return $this->status === self::CONFIRMED
            && $this->concert->isUpcoming()
            && ! $this->checked_in_at;
    }

    public function getReferenceAttribute(): string
    {
        return 'CH-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}
