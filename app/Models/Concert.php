<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    /** Colour pairs for cards and page backgrounds; all carry white text. */
    private const COLORS = [
        ['#5b2bff', '#9b1fe8'],
        ['#d6127a', '#7a1fd0'],
        ['#cf3a0c', '#c2127a'],
        ['#007e8a', '#0b5fd1'],
        ['#1f4bff', '#0779a8'],
        ['#0a8060', '#0a6fb0'],
        ['#b0129e', '#5b2bff'],
        ['#c8102e', '#8a12a8'],
    ];

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
        'is_published',
        'tagline',
        'genre',
        'city',
        'doors_time',
        'start_time',
        'lineup',
        'venue_layout',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'is_published' => 'boolean',
        'lineup' => 'array',
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
     * Ticket tiers in the order the admin listed them.
     */
    public function sections()
    {
        return $this->hasMany(SeatSection::class)->orderBy('sort_order');
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

    /**
     * Adds `tickets_sold` without one query per concert.
     */
    public function scopeWithTicketsSold($query)
    {
        return $query->withSum(['orders as tickets_sold' => function ($orders) {
            $orders->where('status', Order::CONFIRMED);
        }], 'quantity');
    }

    /**
     * Concerts the public can see.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('date', '>=', today());
    }

    /**
     * Upcoming concerts soonest first, followed by past ones most recent first.
     */
    public function scopeUpcomingFirst($query)
    {
        $today = today()->toDateString();

        return $query
            ->orderByRaw('CASE WHEN date >= ? THEN 0 ELSE 1 END', [$today])
            ->orderByRaw('CASE WHEN date >= ? THEN date END ASC', [$today])
            ->orderBy('date', 'desc');
    }

    public function isUpcoming(): bool
    {
        return $this->date->greaterThanOrEqualTo(today());
    }

    public function ticketsSold(): int
    {
        if (array_key_exists('tickets_sold', $this->attributes)) {
            return (int) $this->attributes['tickets_sold'];
        }

        return (int) $this->orders()->where('status', Order::CONFIRMED)->sum('quantity');
    }

    public function ticketsRemaining(): int
    {
        return max(0, $this->total_ticket - $this->ticketsSold());
    }

    public function isSoldOut(): bool
    {
        return $this->ticketsRemaining() === 0;
    }

    public function canBeBooked(): bool
    {
        return $this->is_published && $this->isUpcoming() && ! $this->isSoldOut();
    }

    public function percentSold(): int
    {
        return $this->total_ticket ? (int) round($this->ticketsSold() / $this->total_ticket * 100) : 0;
    }

    public function hasSeatMap(): bool
    {
        return (bool) $this->venue_layout;
    }

    public function venueDetails(): ?array
    {
        return $this->venue_layout ? config("venues.{$this->venue_layout}") : null;
    }

    /**
     * When the show starts. Concerts without a set time count from 8pm.
     */
    public function startsAt(): \Illuminate\Support\Carbon
    {
        return $this->date->copy()->setTimeFromTimeString($this->start_time ?: '20:00');
    }

    public function colors(): array
    {
        return self::COLORS[$this->id % count(self::COLORS)];
    }

    public function colorStyle(): string
    {
        [$first, $second] = $this->colors();

        return "--c1: {$first}; --c2: {$second};";
    }

    /**
     * Photo, songs and video preview for this concert's headliner.
     */
    public function artistMedia(): array
    {
        return config('media.artists')[$this->artist] ?? [];
    }

    public function venuePhoto(): ?array
    {
        return config('media.venues')[$this->venue] ?? null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * The picture for cards: an uploaded poster, otherwise the artist's photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->image_url ?? $this->artistPhotoUrl();
    }

    /**
     * The picture for full-page backgrounds, where a photo works better than a poster.
     */
    public function getHeroUrlAttribute(): ?string
    {
        return $this->artistPhotoUrl() ?? $this->image_url;
    }

    private function artistPhotoUrl(): ?string
    {
        $photo = $this->artistMedia()['photo'] ?? null;

        return $photo ? asset($photo) : null;
    }
}
