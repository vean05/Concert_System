<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeatHoldService
{
    public const MINUTES = 8;
    public const MAX_SEATS = 8;

    /**
     * Reserve seats for one buyer while they pay. The buyer's previous hold
     * for this concert is replaced. An expired hold simply stops counting.
     *
     * @param  array<int, int>  $seatIds
     */
    public function hold(User $user, Concert $concert, array $seatIds): Carbon
    {
        $seatIds = array_values(array_unique(array_map('intval', $seatIds)));

        if (! $seatIds) {
            throw new BookingException('Choose at least one seat.');
        }

        if (count($seatIds) > self::MAX_SEATS) {
            throw new BookingException('You can hold up to ' . self::MAX_SEATS . ' seats at a time.');
        }

        if (! $concert->canBeBooked()) {
            throw new BookingException('Booking is closed for this concert.');
        }

        return DB::transaction(function () use ($user, $concert, $seatIds) {
            $sectionIds = $concert->sections()->pluck('id');

            // Locking in id order stops two overlapping holds from deadlocking.
            $seats = Seat::whereIn('seat_section_id', $sectionIds)
                ->whereIn('id', $seatIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($seatIds)) {
                throw new BookingException('One of those seats does not belong to this concert.');
            }

            foreach ($seats as $seat) {
                $heldByOther = $seat->held_until && $seat->held_until->isFuture() && $seat->held_by !== $user->id;

                if ($seat->order_id || $heldByOther) {
                    throw new BookingException("{$seat->label} was just taken. Please choose another seat.");
                }
            }

            Seat::whereIn('seat_section_id', $sectionIds)
                ->where('held_by', $user->id)
                ->whereNotIn('id', $seatIds)
                ->update(['held_by' => null, 'held_until' => null]);

            $until = now()->addMinutes(self::MINUTES);
            Seat::whereIn('id', $seatIds)->update(['held_by' => $user->id, 'held_until' => $until]);

            return $until;
        });
    }

    public function release(User $user, Concert $concert): void
    {
        Seat::whereIn('seat_section_id', $concert->sections()->pluck('id'))
            ->where('held_by', $user->id)
            ->update(['held_by' => null, 'held_until' => null]);
    }

    /**
     * The buyer's seats that are still reserved for them.
     */
    public function heldSeats(User $user, Concert $concert): Collection
    {
        return Seat::with('section')
            ->whereIn('seat_section_id', $concert->sections()->pluck('id'))
            ->where('held_by', $user->id)
            ->where('held_until', '>', now())
            ->whereNull('order_id')
            ->orderBy('id')
            ->get();
    }

    /**
     * Seat ids grouped by what the map should show right now.
     */
    public function status(Concert $concert, ?User $user): array
    {
        $sectionIds = $concert->sections()->pluck('id');

        $sold = Seat::whereIn('seat_section_id', $sectionIds)->whereNotNull('order_id')->pluck('id');
        $held = Seat::whereIn('seat_section_id', $sectionIds)
            ->whereNull('order_id')
            ->where('held_until', '>', now())
            ->get(['id', 'held_by']);

        return [
            'sold' => $sold->values(),
            'held' => $held->where('held_by', '!==', optional($user)->id)->pluck('id')->values(),
            'mine' => $held->where('held_by', optional($user)->id)->pluck('id')->values(),
        ];
    }
}
