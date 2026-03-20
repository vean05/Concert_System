<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show user profile with their orders and reviews.
     */
    public function show()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('concert')->latest()->paginate(10);
        $reviews = $user->reviews()->with('concert')->latest()->paginate(10);

        return view('profile.show', compact('user', 'orders', 'reviews'));
    }

    /**
     * Show user's order history.
     */
    public function orders()
    {
        $orders = auth()->user()->orders()->with('concert')->latest()->paginate(15);
        return view('profile.orders', compact('orders'));
    }

    /**
     * Show user's reviews.
     */
    public function reviews()
    {
        $reviews = auth()->user()->reviews()->with('concert')->latest()->paginate(15);
        return view('profile.reviews', compact('reviews'));
    }
}
