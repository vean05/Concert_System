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
        $orders = $user->orders()->with('concert')->latest()->paginate(10);
        $reviews = $user->reviews()->with('concert')->latest()->paginate(10);

        // Admin-specific data
        $publishedConcerts = null;
        $upcomingConcerts = null;
        $totalPublished = 0;
        $adminReviews = null;
        $totalAdminReviews = 0;

        if ($user->is_admin) {
            $publishedConcerts = Concert::where('created_by', $user->id)->latest()->get();
            $totalPublished = $publishedConcerts->count();
            $upcomingConcerts = Concert::where('created_by', $user->id)
                ->where('date', '>=', now())
                ->where('date', '<=', now()->addMonth())
                ->orderBy('date')
                ->get();

            // Reviews on admin's published concerts
            $concertIds = $publishedConcerts->pluck('id');
            $adminReviews = \App\Models\Review::with(['user', 'concert'])
                ->whereIn('concert_id', $concertIds)
                ->latest()
                ->get();
            $totalAdminReviews = $adminReviews->count();
        }

        return view('profile.show', compact('user', 'orders', 'reviews', 'publishedConcerts', 'upcomingConcerts', 'totalPublished', 'adminReviews', 'totalAdminReviews'));
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

