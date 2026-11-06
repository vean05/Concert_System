<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\User;
use App\Services\BookingService;
use App\Services\TicketPassService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Show booking form for a specific concert.
     */
    public function create(Concert $concert)
    {
        abort_unless($concert->is_published, 404);

        if (! $concert->canBeBooked()) {
            return redirect()->route('concerts.show', $concert)->with('error', $concert->isUpcoming()
                ? 'This concert is sold out.'
                : 'Booking is closed: this concert has already taken place.');
        }

        if ($concert->hasSeatMap()) {
            return redirect()->route('seats.show', $concert);
        }

        $concert->load(['sections' => function ($sections) {
            $sections->withSold();
        }]);
        $paymentCards = auth()->user()->paymentCards()->latest()->get();

        return view('orders.create', compact('concert', 'paymentCards'));
    }

    /**
     * Store a new booking order.
     */
    public function store(Request $request, Concert $concert, BookingService $booking)
    {
        abort_unless($concert->is_published, 404);

        $validated = $request->validate([
            'tickets' => 'required|array',
            'tickets.*' => 'integer|min:0|max:100',
            'payment_card_id' => 'required|integer',
        ], [
            'payment_card_id.required' => 'Choose a payment card.',
        ]);

        $card = auth()->user()->paymentCards()->find($validated['payment_card_id']);

        if (! $card) {
            return back()->withInput()->with('error', 'Invalid payment card selected!');
        }

        try {
            $order = $booking->place(auth()->user(), $concert, $validated['tickets'], $card);
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $charged = number_format($order->total_price, 2);

        return redirect()->route('orders.show', $order)
            ->with('success', "Booking confirmed! {$card->brand} ending in {$card->last_four} was charged \${$charged}.");
    }

    /**
     * Display user's orders.
     */
    public function index()
    {
        $orders = auth()->user()->orders()->with('concert')->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show order details.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('paymentCard', 'concert', 'items');

        return view('orders.show', compact('order'));
    }

    /**
     * The ticket's current door code. It changes every half minute.
     */
    public function pass(Order $order, TicketPassService $passes)
    {
        $this->authorize('view', $order);
        abort_if($order->status === Order::CANCELLED, 410);

        return response()->json([
            'code' => $passes->code($order),
            'seconds_left' => $passes->secondsLeft(),
            'period' => TicketPassService::SECONDS,
            'checked_in_at' => optional($order->checked_in_at)->format('H:i, d M'),
        ]);
    }

    /**
     * Send the tickets to another ConcertHub account.
     */
    public function transfer(Request $request, Order $order, BookingService $booking)
    {
        $this->authorize('update', $order);

        $validated = $request->validate(['email' => 'required|email']);
        $recipient = User::where('email', $validated['email'])->first();

        if (! $recipient) {
            return back()->with('error', 'There is no ConcertHub account with that email.');
        }

        try {
            $booking->transfer($order, $recipient);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', "Tickets sent to {$recipient->name}.");
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order, BookingService $booking)
    {
        $this->authorize('update', $order);

        try {
            $booking->cancel($order);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        $amount = number_format($order->total_price, 2);
        $card = $order->paymentCard;

        return redirect()->route('orders.index')->with('success', $card
            ? "Order cancelled. Refund of \${$amount} sent to {$card->brand} ending in {$card->last_four}."
            : 'Order cancelled successfully!');
    }
}
