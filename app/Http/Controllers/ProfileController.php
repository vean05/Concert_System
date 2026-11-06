<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show user profile with their recent orders and reviews.
     */
    public function show(\App\Services\ConcertPassportService $passports)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        $orders = $user->orders()->with('concert')->latest()->take(4)->get();
        $reviews = $user->reviews()->with('concert')->latest()->take(4)->get();
        $stats = [
            'orders' => $user->orders()->count(),
            'tickets' => (int) $user->orders()->where('status', 'confirmed')->sum('quantity'),
            'reviews' => $user->reviews()->count(),
            'favourites' => $user->favouriteConcerts()->count(),
        ];

        return view('profile.show', ['passport' => $passports->for($user)] + compact('user', 'orders', 'reviews', 'stats'));
    }

    /**
     * Show user's reviews.
     */
    public function reviews()
    {
        $reviews = auth()->user()->reviews()->with('concert')->latest()->paginate(15);

        return view('profile.reviews', compact('reviews'));
    }

    /**
     * Show edit profile form.
     */
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    /**
     * Update user profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }
}
