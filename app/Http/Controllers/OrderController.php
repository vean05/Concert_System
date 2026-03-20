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
        return view('orders.create', compact('concert'));
    }

    /**
     * Store a new booking order.
     */
    public function store(Request $request, Concert $concert)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

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
        ]);

        // Store message in session
        session()->flash('success', 'Booking successful! Your order has been confirmed.');
        
        // Store last viewed concert in cookie
        cookie('last_concert', $concert->id, 60 * 24 * 7); // 7 days

        return redirect()->route('orders.index')->with('success', 'Booking successful!');
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

        $order->update(['status' => 'cancelled']);

        return redirect()->route('orders.index')->with('success', 'Order cancelled successfully!');
    }
}
