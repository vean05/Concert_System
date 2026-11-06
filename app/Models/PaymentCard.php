<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_type',
        'card_number',
        'expiry_date',
        'cvv',
        'full_name',
        'country',
        'address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'card_number' => 'encrypted',
        'cvv' => 'encrypted',
    ];

    /**
     * Get the user that owns the payment card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLastFourAttribute(): string
    {
        return substr($this->card_number, -4);
    }

    /**
     * Cards added through the form store "visa"/"master"; older rows hold full names.
     */
    public function getBrandAttribute(): string
    {
        $brands = ['visa' => 'Visa', 'master' => 'Mastercard', 'mastercard' => 'Mastercard'];

        return $brands[strtolower($this->card_type)] ?? $this->card_type;
    }
}
