<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'venue',
        'date',
        'description',
        'ticket_price',
        'total_ticket',
        'created_by',
    ];

    /**
     * Get the user who created this concert.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all orders for this concert.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all reviews for this concert.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
