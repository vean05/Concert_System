<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    public $timestamps = false;

    protected $fillable = ['seat_section_id', 'block', 'row_label', 'number', 'order_id', 'held_by', 'held_until'];

    protected $casts = [
        'held_by' => 'integer',
        'held_until' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(SeatSection::class, 'seat_section_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->where(function ($seats) {
            $seats->whereNull('held_until')->orWhere('held_until', '<', now());
        });
    }

    public function getLabelAttribute(): string
    {
        $place = "Row {$this->row_label}, Seat {$this->number}";

        return $this->block === '' ? $place : "Section {$this->block}, {$place}";
    }
}
