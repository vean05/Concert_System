<?php

namespace App\Http\Controllers;

use App\Models\Concert;

class FavouriteController extends Controller
{
    /**
     * Show the user's favourite concerts.
     */
    public function index()
    {
        $concerts = auth()->user()->favouriteConcerts()->published()->withTicketsSold()->upcomingFirst()->paginate(12);

        return view('concerts.favourites', compact('concerts'));
    }

    /**
     * Toggle favourite status for a concert.
     */
    public function toggle(Concert $concert)
    {
        $changes = auth()->user()->favouriteConcerts()->toggle($concert->id);
        $favourited = ! empty($changes['attached']);

        return response()->json([
            'status' => $favourited ? 'favourited' : 'unfavourited',
            'message' => $favourited ? 'Added to favourites' : 'Removed from favourites',
        ]);
    }
}
