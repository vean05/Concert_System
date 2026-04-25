<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Show booking form for a specific concert.
     */
    public function create(Concert $concert)
    {
        $paymentCards = auth()->user()->paymentCards()->get();
        return view('orders.create', compact('concert', 'paymentCards'));
    }

    /**
     * Store a new booking order.
     */
    public function store(Request $request, Concert $concert)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'payment_card_id' => 'required|exists:payment_cards,id',
        ]);

        // Verify the user owns the card
        $card = auth()->user()->paymentCards()->find($validated['payment_card_id']);
        if (!$card) {
            return redirect()->back()->with('error', 'Invalid payment card selected!');
        }

        // Check if enough tickets are available
        $availableTickets = $concert->total_ticket - Order::where('concert_id', $concert->id)
            ->where('status', 'confirmed')
            ->sum('quantity');

        if ($validated['quantity'] > $availableTickets) {
            return redirect()->back()->with('error', 'Not enough tickets available!');
        }

        $totalPrice = $concert->ticket_price * $validated['quantity'];

        Order::create([
            'user_id' => auth()->id(),
            'concert_id' => $concert->id,
            'quantity' => $validated['quantity'],
            'total_price' => $totalPrice,
            'status' => 'confirmed',
            'payment_card_id' => $card->id,
        ]);

        // Generate dynamic success message
        $lastFour = substr($card->card_number, -4);
        $cardType = ucfirst($card->card_type);
        $formattedPrice = number_format($totalPrice, 2);
        $successMsg = "Booking successful! {$cardType} ending in {$lastFour} was charged \${$formattedPrice}.";

        // Store message in session
        session()->flash('success', $successMsg);

        // Store last viewed concert in cookie
        cookie('last_concert', $concert->id, 60 * 24 * 7); // 7 days

        return redirect()->route('orders.index')->with('success', $successMsg);
    }

    /**
     * Display user's orders.
     */
    public function index()
    {
        $orders = auth()->user()->orders()->with('concert')->paginate(10);
        return view('orders.index', compact('orders'));
    }

    /**
     * Show order details.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('paymentCard');
        return view('orders.show', compact('order'));
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        $this->authorize('update', $order);

        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', 'This order is already cancelled!');
        }

        // Build refund message using the card linked to the order
        $order->load('paymentCard');
        if ($order->paymentCard) {
            $lastFour  = substr($order->paymentCard->card_number, -4);
            $cardType  = ucfirst($order->paymentCard->card_type);
            $amount    = number_format($order->total_price, 2);
            $refundMsg = "Order cancelled. Refund of \${$amount} successfully sent to {$cardType} ending in {$lastFour}.";
        } else {
            $refundMsg = 'Order cancelled successfully!';
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('orders.index')->with('success', $refundMsg);
    }
}

