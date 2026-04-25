<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show user profile with their orders and reviews.
     */
    public function show()
    {
        $user = auth()->user();
        
        // Redirect admin users to admin panel
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        $orders = $user->orders()->with('concert')->latest()->paginate(10);
        $reviews = $user->reviews()->with('concert')->latest()->paginate(10);

        return view('profile.show', compact('user', 'orders', 'reviews'));
    }

    /**
     * Show admin's published concerts (full page).
     */
    public function publishedConcerts()
    {
        $user = auth()->user();
        $concerts = Concert::where('created_by', $user->id)->latest()->paginate(15);
        return view('profile.published_concerts', compact('concerts'));
    }

    /**
     * Show all reviews on admin's published concerts (full page).
     */
    public function adminReviews()
    {
        $user = auth()->user();
        $concertIds = Concert::where('created_by', $user->id)->pluck('id');
        $reviews = \App\Models\Review::with(['user', 'concert'])
            ->whereIn('concert_id', $concertIds)
            ->latest()
            ->paginate(15);
        return view('profile.admin_reviews', compact('reviews'));
    }

    /**
     * Show user's order history.
     */
    public function orders()
    {
        $user = auth()->user();
        
        // Redirect admin users to admin panel
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        $orders = $user->orders()->with('concert')->latest()->paginate(15);
        return view('profile.orders', compact('orders'));
    }

    /**
     * Show user's reviews.
     */
    public function reviews()
    {
        $user = auth()->user();
        
        // Redirect admin users to admin panel
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        $reviews = $user->reviews()->with('concert')->latest()->paginate(15);
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
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        // Update name and email
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }
}

