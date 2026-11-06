<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private const SORTABLE_COLUMNS = ['date', 'title', 'artist', 'venue', 'created_at'];

    /**
     * Dashboard - Show admin overview
     */
    public function dashboard()
    {
        $stats = [
            'concerts' => Concert::count(),
            'upcoming' => Concert::upcoming()->count(),
            'users' => User::count(),
            'tickets' => (int) Order::where('status', Order::CONFIRMED)->sum('quantity'),
            'revenue' => (float) Order::where('status', Order::CONFIRMED)->sum('total_price'),
        ];
        $upcomingConcerts = Concert::upcoming()->withTicketsSold()->orderBy('date')->take(5)->get();
        $recentOrders = Order::with('user', 'concert')->latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'upcomingConcerts', 'recentOrders'));
    }

    /**
     * List all concerts for admin management
     */
    public function concerts(Request $request)
    {
        $sortBy = in_array($request->get('sort_by'), self::SORTABLE_COLUMNS, true) ? $request->get('sort_by') : 'date';
        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';

        $concerts = Concert::with('creator')
            ->withTicketsSold()
            ->withCount('reviews')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('artist', 'like', "%{$search}%")
                        ->orWhere('venue', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate(15)
            ->withQueryString();

        return view('admin.concerts.index', compact('concerts'));
    }

    /**
     * Show single concert details for admin
     */
    public function showConcert(Concert $concert)
    {
        $concert->load(['creator', 'reviews.user', 'sections' => function ($sections) {
            $sections->withSold();
        }]);
        $orders = $concert->orders()->with('user')->latest()->paginate(10);

        return view('admin.concerts.show', compact('concert', 'orders'));
    }

    /**
     * Delete a concert; its tickets, orders, reviews and favourites go with it.
     */
    public function deleteConcert(Concert $concert)
    {
        $this->authorize('delete', $concert);

        if ($concert->image_path) {
            Storage::disk('public')->delete($concert->image_path);
        }

        // Foreign keys cascade to orders, reviews, favourites, sections and seats.
        $concert->delete();

        return redirect()->route('admin.concerts.index')->with('success', 'Concert deleted successfully!');
    }

    /**
     * Show users management
     */
    public function users(Request $request)
    {
        $users = User::withCount('orders', 'reviews', 'favouriteConcerts')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function showUser(User $user)
    {
        $user->load(['orders.concert', 'reviews.concert', 'favouriteConcerts']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show analytics
     */
    public function analytics()
    {
        $confirmedOrders = Order::where('status', Order::CONFIRMED);

        $stats = [
            'concerts' => Concert::count(),
            'users' => User::count(),
            'tickets' => (int) (clone $confirmedOrders)->sum('quantity'),
            'revenue' => (float) (clone $confirmedOrders)->sum('total_price'),
            'reviews' => Review::count(),
            'average_rating' => round((float) Review::avg('rating'), 1),
        ];

        // Grouped in PHP so this does not depend on MySQL's DATE_FORMAT.
        $salesByMonth = (clone $confirmedOrders)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['total_price', 'quantity', 'created_at'])
            ->groupBy(function ($order) {
                return $order->created_at->format('Y-m');
            });

        $months = collect(range(11, 0))->map(function ($monthsAgo) use ($salesByMonth) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $orders = $salesByMonth->get($month->format('Y-m'), collect());

            return [
                'label' => $month->format('M y'),
                'tickets' => (int) $orders->sum('quantity'),
                'revenue' => (float) $orders->sum('total_price'),
            ];
        });

        $popularConcerts = Concert::withTicketsSold()->orderByDesc('tickets_sold')->take(5)->get();
        $reviewedConcerts = Concert::withCount('reviews')->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_count')->take(5)->get();

        return view('admin.analytics', compact('stats', 'months', 'popularConcerts', 'reviewedConcerts'));
    }

    /**
     * Show concert reviews
     */
    public function concertReviews()
    {
        $reviews = Review::with('user', 'concert')->latest()->paginate(15);

        return view('admin.concert-reviews', compact('reviews'));
    }
}
