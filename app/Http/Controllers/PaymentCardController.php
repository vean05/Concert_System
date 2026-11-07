<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PublicDemo;
use App\Models\PaymentCard;
use Carbon\Carbon;
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
        auth()->user()->paymentCards()->create($request->validate($this->rules()));

        return redirect()->route('payment_cards.index')->with('success', 'Payment card added successfully.');
    }

    /**
     * Add a card from the checkout page without leaving it.
     * Validation failures return Laravel's standard 422 JSON for fetch requests.
     */
    public function storeAjax(Request $request)
    {
        $card = auth()->user()->paymentCards()->create($request->validate($this->rules()));

        return response()->json([
            'success' => true,
            'card' => [
                'id' => $card->id,
                'brand' => $card->brand,
                'last_four' => $card->last_four,
                'expiry_date' => $card->expiry_date,
            ],
        ]);
    }

    public function destroy(PaymentCard $paymentCard)
    {
        abort_unless($paymentCard->user_id === auth()->id(), 403);

        $paymentCard->delete();

        return redirect()->route('payment_cards.index')->with('success', 'Payment card deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'card_type' => 'required|in:visa,master',
            'card_number' => ['required', 'regex:/^[0-9]{13,19}$/', function ($attribute, $value, $fail) {
                if (config('app.public_demo') && $value !== PublicDemo::TEST_CARD) {
                    $fail('This is a demo site. Use the test card ' . PublicDemo::TEST_CARD . ', not a real card.');
                }
            }],
            'expiry_date' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/', function ($attribute, $value, $fail) {
                if (preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $value)
                    && Carbon::createFromFormat('!m/y', $value)->endOfMonth()->isPast()) {
                    $fail('This card has expired.');
                }
            }],
            'cvv' => ['required', 'regex:/^[0-9]{3,4}$/'],
            'full_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'address' => 'required|string|max:500',
        ];
    }
}
