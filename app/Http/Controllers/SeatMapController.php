<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Seat;
use App\Services\BookingService;
use App\Services\PromoService;
use App\Services\SeatHoldService;
use App\Services\SeatPartyService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SeatMapController extends Controller
{
    /**
     * The 3D seat picker.
     */
    public function show(Request $request, Concert $concert, SeatPartyService $parties)
    {
        abort_unless($concert->hasSeatMap() && $concert->is_published, 404);

        if (! $concert->canBeBooked()) {
            return redirect()->route('concerts.show', $concert)->with('error', 'Booking is closed for this concert.');
        }

        $party = null;

        if ($request->filled('party')) {
            try {
                $party = $parties->join(auth()->user(), $concert, (string) $request->query('party'));
            } catch (BookingException $e) {
                return redirect()->route('seats.show', $concert)->with('error', $e->getMessage());
            }
        }

        return view('seat_map.show', [
            'concert' => $concert,
            'venue' => $concert->venueDetails(),
            'partyCode' => optional($party)->code,
        ]);
    }

    /**
     * Everything the 3D scene needs, as parallel arrays to keep the payload small.
     */
    public function data(Concert $concert, SeatHoldService $holds)
    {
        abort_unless($concert->hasSeatMap(), 404);

        $tiers = $concert->sections()->withSold()->get();
        $tierIndex = $tiers->pluck('id')->flip();

        $seats = Seat::whereIn('seat_section_id', $tiers->pluck('id'))
            ->orderBy('id')
            ->toBase()
            ->get(['id', 'seat_section_id', 'block', 'row_label', 'number', 'x', 'y', 'z', 'rotation']);

        return response()->json([
            'stageName' => $concert->artist,
            'stageVideo' => $concert->artistMedia()['video']['url'] ?? null,
            'songs' => collect($concert->artistMedia()['songs'] ?? [])->map(function ($song) {
                return Arr::only($song, ['title', 'preview']);
            })->filter(function ($song) {
                return ! empty($song['preview']);
            })->values(),
            'layout' => Arr::only($concert->venueDetails(), ['scene', 'view', 'centre', 'bowls', 'sweep_degrees', 'corner_power']),
            'tiers' => $tiers->map(function ($tier) {
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'price' => (float) $tier->price,
                    'color' => $tier->color,
                    'remaining' => $tier->remaining(),
                ];
            })->values(),
            'seats' => [
                'id' => $seats->pluck('id'),
                'tier' => $seats->map(function ($seat) use ($tierIndex) {
                    return $tierIndex[$seat->seat_section_id];
                }),
                'block' => $seats->pluck('block'),
                'row' => $seats->pluck('row_label'),
                'number' => $seats->pluck('number'),
                'x' => $seats->pluck('x'),
                'y' => $seats->pluck('y'),
                'z' => $seats->pluck('z'),
                'rotation' => $seats->pluck('rotation'),
            ],
            'status' => $holds->status($concert, auth()->user()),
        ]);
    }

    /**
     * Polled by the seat picker so the map stays live.
     */
    public function status(Concert $concert, SeatHoldService $holds)
    {
        return response()->json($holds->status($concert, auth()->user()));
    }

    /**
     * Reserve the chosen seats and send the buyer to checkout.
     */
    public function hold(Request $request, Concert $concert, SeatHoldService $holds)
    {
        $validated = $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer',
        ]);

        try {
            $holds->hold(auth()->user(), $concert, $validated['seat_ids']);
        } catch (BookingException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['redirect' => route('seats.checkout', $concert)]);
    }

    /**
     * Give the seats back and return to the map.
     */
    public function release(Concert $concert, SeatHoldService $holds)
    {
        $holds->release(auth()->user(), $concert);

        return redirect()->route('seats.show', $concert)->with('success', 'Your seats have been released.');
    }

    /**
     * Pay for held seats.
     */
    public function checkout(Concert $concert, SeatHoldService $holds)
    {
        $seats = $holds->heldSeats(auth()->user(), $concert);

        if ($seats->isEmpty()) {
            return redirect()->route('seats.show', $concert)->with('error', 'Your seat hold has expired. Please choose your seats again.');
        }

        return view('orders.checkout', [
            'concert' => $concert,
            'seats' => $seats,
            'heldUntil' => $seats->min('held_until'),
            'paymentCards' => auth()->user()->paymentCards()->latest()->get(),
        ]);
    }

    /**
     * Price the held seats with a promo code, without spending it.
     */
    public function promo(Request $request, Concert $concert, SeatHoldService $holds, PromoService $promos)
    {
        $validated = $request->validate(['code' => 'required|string|max:32']);
        $subtotal = (float) $holds->heldSeats(auth()->user(), $concert)->sum(function ($seat) {
            return $seat->section->price;
        });

        try {
            $promo = $promos->usable(auth()->user(), $validated['code']);
        } catch (BookingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $discount = $promo->discountOn($subtotal);

        return response()->json([
            'code' => $promo->code,
            'label' => "{$promo->percent_off}% off",
            'discount' => $discount,
            'total' => $subtotal - $discount,
        ]);
    }

    public function purchase(Request $request, Concert $concert, BookingService $booking)
    {
        $validated = $request->validate(
            ['payment_card_id' => 'required|integer', 'promo_code' => 'nullable|string|max:32'],
            ['payment_card_id.required' => 'Choose a payment card.']
        );

        $card = auth()->user()->paymentCards()->find($validated['payment_card_id']);

        if (! $card) {
            return back()->with('error', 'Invalid payment card selected!');
        }

        try {
            $order = $booking->purchaseHeldSeats(auth()->user(), $concert, $card, $validated['promo_code'] ?? null);
        } catch (BookingException $e) {
            return redirect()->route('seats.show', $concert)->with('error', $e->getMessage());
        }

        $charged = number_format($order->total_price, 2);

        return redirect()->route('orders.show', $order)
            ->with('success', "Booking confirmed! {$card->brand} ending in {$card->last_four} was charged \${$charged}.");
    }
}
