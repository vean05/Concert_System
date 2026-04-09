<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Dashboard - Show admin overview
     */
    public function dashboard()
    {
        $totalConcerts = Concert::count();
        $totalUsers = User::count();
        $recentConcerts = Concert::with('creator')->latest()->take(10)->get();
        $upcomingConcerts = Concert::where('date', '>=', now())->with('creator')->orderBy('date')->take(5)->get();

        return view('admin.dashboard', compact('totalConcerts', 'totalUsers', 'recentConcerts', 'upcomingConcerts'));
    }

    /**
     * List all concerts for admin management
     */
    public function concerts(Request $request)
    {
        $query = Concert::with('creator', 'orders', 'reviews');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
        }

        // Filter by creator
        if ($request->has('creator_id') && $request->creator_id) {
            $query->where('created_by', $request->creator_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $concerts = $query->paginate(15);
        $creators = User::has('concerts')->get();

        return view('admin.concerts.index', compact('concerts', 'creators'));
    }

    /**
     * Show single concert details for admin
     */
    public function showConcert(Concert $concert)
    {
        $concert->load('creator', 'orders', 'reviews');
        $seatingAreas = json_decode($concert->seating_areas, true) ?? [];

        return view('admin.concerts.show', compact('concert', 'seatingAreas'));
    }

    /**
     * Delete a concert (admin only, authorization in policy)
     */
    public function deleteConcert(Concert $concert)
    {
        $this->authorize('delete', $concert);

        // Delete associated orders and reviews
        $concert->orders()->delete();
        $concert->reviews()->delete();
        $concert->favouritedBy()->detach();

        // Delete image if exists
        if ($concert->image_path && \Storage::disk('public')->exists($concert->image_path)) {
            \Storage::disk('public')->delete($concert->image_path);
        }

        $concert->delete();

        return redirect()->route('admin.concerts.index')->with('success', 'Concert deleted successfully!');
    }

    /**
     * Show users management
     */
    public function users(Request $request)
    {
        $query = User::withCount('concerts', 'orders', 'reviews', 'favouriteConcerts');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function showUser(User $user)
    {
        $user->load('concerts', 'orders', 'reviews', 'favouriteConcerts');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show analytics
     */
    public function analytics()
    {
        $totalConcerts = Concert::count();
        $totalUsers = User::count();
        $totalOrders = \App\Models\Order::count() ?? 0;
        $totalReviews = \App\Models\Review::count() ?? 0;

        // Concerts by month (last 12 months)
        $concertsByMonth = Concert::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Most popular concerts (by order count)
        $popularConcerts = Concert::withCount('orders')
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();

        // Most reviewed concerts
        $reviewedConcerts = Concert::withCount('reviews')
            ->orderByDesc('reviews_count')
            ->take(5)
            ->get();

        return view('admin.analytics', compact(
            'totalConcerts',
            'totalUsers',
            'totalOrders',
            'totalReviews',
            'concertsByMonth',
            'popularConcerts',
            'reviewedConcerts'
        ));
    }
}
