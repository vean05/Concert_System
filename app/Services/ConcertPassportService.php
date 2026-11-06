<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class ConcertPassportService
{
    /** One stamp per show the fan holds a confirmed ticket for, newest date first. */
    public function for(User $user): array
    {
        $orders = $user->orders()
            ->with('concert')
            ->where('status', Order::CONFIRMED)
            ->get()
            ->filter(function ($order) {
                return $order->concert !== null;
            });

        $stamps = $orders->groupBy('concert_id')->map(function ($group) {
            $concert = $group->first()->concert;

            return [
                'concert' => $concert,
                'tickets' => (int) $group->sum('quantity'),
                'upcoming' => ! $concert->date->isPast(),
            ];
        })->sortByDesc(function ($stamp) {
            return $stamp['concert']->date->timestamp;
        })->values();

        return [
            'shows' => $stamps->count(),
            'artists' => $stamps->pluck('concert.artist')->unique()->count(),
            'venues' => $stamps->pluck('concert.venue')->unique()->count(),
            'next' => $stamps->where('upcoming', true)->last(),
            'stamps' => $stamps,
        ];
    }
}
