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
        'image_path',
        'seating_areas',
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

    /**
     * Get all users who have favourited this concert.
     */
    public function favouritedBy()
    {
        return $this->belongsToMany(User::class, 'concert_user')->withTimestamps();
    }

    /**
     * Check if a concert is favourited by a user.
     */
    public function isFavouredByUser($userId)
    {
        return $this->favouritedBy()->where('user_id', $userId)->exists();
    }
}
