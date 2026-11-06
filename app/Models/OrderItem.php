<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'seat_section_id', 'seat_id', 'section_name', 'seat_label', 'quantity', 'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function section()
    {
        return $this->belongsTo(SeatSection::class, 'seat_section_id');
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
