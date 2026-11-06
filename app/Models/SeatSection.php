<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatSection extends Model
{
    public const SEATED = 'seated';
    public const STANDING = 'standing';

    protected $fillable = ['concert_id', 'name', 'type', 'price', 'capacity', 'color', 'sort_order'];

    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
    ];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Adds `sold_count` without one query per section. */
    public function scopeWithSold($query)
    {
        return $query->withSum(['orderItems as sold_count' => function ($items) {
            $items->whereHas('order', function ($orders) {
                $orders->where('status', Order::CONFIRMED);
            });
        }], 'quantity');
    }

    public function soldCount(): int
    {
        if (array_key_exists('sold_count', $this->attributes)) {
            return (int) $this->attributes['sold_count'];
        }

        return (int) $this->orderItems()
            ->whereHas('order', function ($orders) {
                $orders->where('status', Order::CONFIRMED);
            })
            ->sum('quantity');
    }

    public function remaining(): int
    {
        return max(0, $this->capacity - $this->soldCount());
    }

    public function isSeated(): bool
    {
        return $this->type === self::SEATED;
    }
}
