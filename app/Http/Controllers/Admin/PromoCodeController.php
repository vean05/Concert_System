<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        return view('admin.promos', ['promos' => PromoCode::latest()->get()]);
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $validated = $request->validate([
            'code' => 'required|alpha_dash|max:32|unique:promo_codes,code',
            'percent_off' => 'required|integer|min:1|max:90',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        PromoCode::create($validated);

        return back()->with('success', "Code {$validated['code']} is live.");
    }

    public function toggle(PromoCode $promo)
    {
        $promo->update(['is_active' => ! $promo->is_active]);

        return back()->with('success', "Code {$promo->code} is now " . ($promo->is_active ? 'on' : 'off') . '.');
    }
}
