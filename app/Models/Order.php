<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'concert_id',
        'quantity',
        'total_price',
        'status',
        'payment_card_id',
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
}
