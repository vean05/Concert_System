<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentCardController extends Controller
{
    public function index()
    {
        $cards = auth()->user()->paymentCards()->latest()->get();
        return view('payment_cards.index', compact('cards'));
    }

    public function create()
    {
        return view('payment_cards.create');
    }

    public function store(Request $request)
    {
        // Server-side validation to ensure data integrity
        $validated = $request->validate([
            'card_type' => 'required|in:visa,master',
            'card_number' => ['required', 'regex:/^[0-9]+$/'],
            'expiry_date' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required', 'regex:/^[0-9]+$/'],
            'full_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        auth()->user()->paymentCards()->create($validated);

        return redirect()->route('payment_cards.index')->with('success', 'Payment card added successfully.');
    }

    public function storeAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'card_type' => 'required|in:visa,master',
                'card_number' => ['required', 'regex:/^[0-9]+$/'],
                'expiry_date' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
                'cvv' => ['required', 'regex:/^[0-9]+$/'],
                'full_name' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'address' => 'required|string',
            ]);

            $card = auth()->user()->paymentCards()->create($validated);

            return response()->json([
                'success' => true,
                'card' => [
                    'id' => $card->id,
                    'card_type' => $card->card_type,
                    'last_four' => substr($request->card_number, -4),
                    'expiry_date' => $card->expiry_date
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the card.'
            ], 500);
        }
    }

    public function destroy(\App\Models\PaymentCard $paymentCard)
    {
        // Check if the user owns the card
        if ($paymentCard->user_id !== auth()->id()) {
            abort(403);
        }

        $paymentCard->delete();

        return redirect()->route('payment_cards.index')->with('success', 'Payment card deleted successfully.');
    }
}
