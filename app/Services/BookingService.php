<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\PromoCode;
use App\Models\Seat;
use App\Models\SeatSection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingService
{
    private PromoService $promos;

    public function __construct(PromoService $promos)
    {
        $this->promos = $promos;
    }

    /**
     * Place an order inside one transaction. Section rows stay locked until it
     * commits, so two buyers can never both take the last tickets.
     *
     * @param  array<int, int>  $tickets  section id => quantity
     */
    public function place(User $user, Concert $concert, array $tickets, PaymentCard $card): Order
    {
        $tickets = array_filter(array_map('intval', $tickets));

        if (! $tickets) {
            throw new BookingException('Choose at least one ticket.');
        }

        if (! $concert->isUpcoming()) {
            throw new BookingException('Booking is closed: this concert has already taken place.');
        }

        if ($concert->hasSeatMap()) {
            throw new BookingException('Please choose your seats on the seat map.');
        }

        return DB::transaction(function () use ($user, $concert, $tickets, $card) {
            $sections = SeatSection::where('concert_id', $concert->id)
                ->whereIn('id', array_keys($tickets))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($sections->count() !== count($tickets)) {
                throw new BookingException('One of the selected ticket types is no longer available.');
            }

            foreach ($sections as $section) {
                $remaining = $section->remaining();

                if ($tickets[$section->id] > $remaining) {
                    throw new BookingException($remaining === 0
                        ? "{$section->name} is sold out."
                        : "Only {$remaining} ticket(s) left in {$section->name}.");
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'concert_id' => $concert->id,
                'quantity' => array_sum($tickets),
                'total_price' => $sections->sum(function ($section) use ($tickets) {
                    return $section->price * $tickets[$section->id];
                }),
                'status' => Order::CONFIRMED,
                'payment_card_id' => $card->id,
            ]);

            foreach ($sections->sortBy('sort_order') as $section) {
                $section->isSeated()
                    ? $this->assignSeats($order, $section, $tickets[$section->id])
                    : $this->addStandingTickets($order, $section, $tickets[$section->id]);
            }

            return $order;
        });
    }

    /**
     * Turn the buyer's held seats into an order.
     */
    public function purchaseHeldSeats(User $user, Concert $concert, PaymentCard $card, ?string $promoCode = null): Order
    {
        if (! $concert->isUpcoming()) {
            throw new BookingException('Booking is closed: this concert has already taken place.');
        }

        return DB::transaction(function () use ($user, $concert, $card, $promoCode) {
            $seats = Seat::with('section')
                ->whereIn('seat_section_id', $concert->sections()->pluck('id'))
                ->where('held_by', $user->id)
                ->where('held_until', '>', now())
                ->whereNull('order_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($seats->isEmpty()) {
                throw new BookingException('Your seat hold has expired. Please choose your seats again.');
            }

            $subtotal = (float) $seats->sum(function ($seat) {
                return $seat->section->price;
            });
            $promo = $promoCode ? $this->promos->usable($user, $promoCode, true) : null;
            $discount = $promo ? $promo->discountOn($subtotal) : 0;

            if ($promo) {
                $promo->increment('used_count');
            }

            $order = Order::create([
                'user_id' => $user->id,
                'concert_id' => $concert->id,
                'quantity' => $seats->count(),
                'total_price' => $subtotal - $discount,
                'discount' => $discount,
                'promo_code_id' => optional($promo)->id,
                'status' => Order::CONFIRMED,
                'payment_card_id' => $card->id,
            ]);

            foreach ($seats as $seat) {
                $order->items()->create([
                    'seat_section_id' => $seat->seat_section_id,
                    'seat_id' => $seat->id,
                    'section_name' => $seat->section->name,
                    'seat_label' => $seat->label,
                    'quantity' => 1,
                    'unit_price' => $seat->section->price,
                ]);

                $seat->update(['order_id' => $order->id, 'held_by' => null, 'held_until' => null]);
            }

            return $order;
        });
    }

    /**
     * Cancel an order and release any seats it held.
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::with('concert')->lockForUpdate()->findOrFail($order->id);

            if ($order->status === Order::CANCELLED) {
                throw new BookingException('This order is already cancelled.');
            }

            if (! $order->concert->isUpcoming()) {
                throw new BookingException('This concert has already taken place, so the order can no longer be cancelled.');
            }

            if ($order->isTransferred()) {
                throw new BookingException('Transferred tickets cannot be cancelled.');
            }

            if ($order->checked_in_at) {
                throw new BookingException('These tickets have already been used at the door.');
            }

            $order->update(['status' => Order::CANCELLED]);
            $order->seats()->update(['order_id' => null]);

            if ($order->promo_code_id) {
                PromoCode::whereKey($order->promo_code_id)->where('used_count', '>', 0)->decrement('used_count');
            }
        });
    }

    /**
     * Hand an order to another fan. The sender loses it and the old code stops
     * working, because the code is signed for the owner.
     */
    public function transfer(Order $order, User $recipient): Order
    {
        return DB::transaction(function () use ($order, $recipient) {
            $order = Order::with('concert')->lockForUpdate()->findOrFail($order->id);

            if (! $order->canBeTransferred()) {
                throw new BookingException('These tickets can no longer be transferred.');
            }

            if ($recipient->id === $order->user_id) {
                throw new BookingException('These tickets are already yours.');
            }

            $order->update([
                'transferred_from_id' => $order->user_id,
                'transferred_at' => now(),
                'user_id' => $recipient->id,
            ]);

            return $order;
        });
    }

    private function assignSeats(Order $order, SeatSection $section, int $quantity): void
    {
        $seats = $section->seats()->available()->orderBy('id')->limit($quantity)->lockForUpdate()->get();

        if ($seats->count() < $quantity) {
            throw new BookingException("Only {$seats->count()} seat(s) left in {$section->name}.");
        }

        foreach ($seats as $seat) {
            $seat->update(['order_id' => $order->id]);

            $order->items()->create([
                'seat_section_id' => $section->id,
                'seat_id' => $seat->id,
                'section_name' => $section->name,
                'seat_label' => $seat->label,
                'quantity' => 1,
                'unit_price' => $section->price,
            ]);
        }
    }

    private function addStandingTickets(Order $order, SeatSection $section, int $quantity): void
    {
        $order->items()->create([
            'seat_section_id' => $section->id,
            'section_name' => $section->name,
            'quantity' => $quantity,
            'unit_price' => $section->price,
        ]);
    }
}
