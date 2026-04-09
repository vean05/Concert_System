<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    /**
     * Toggle favourite status for a concert
     */
    public function toggle(Request $request, Concert $concert)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if already favourited
        if ($user->hasFavourited($concert->id)) {
            // Remove from favourites
            $user->favouriteConcerts()->detach($concert->id);
            return response()->json(['status' => 'unfavourited', 'message' => 'Removed from favourites']);
        } else {
            // Add to favourites
            $user->favouriteConcerts()->attach($concert->id);
            return response()->json(['status' => 'favourited', 'message' => 'Added to favourites']);
        }
    }

    /**
     * Get user's favourite concerts
     */
    public function getFavourites()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $favourites = $user->favouriteConcerts()->get();
        return response()->json($favourites);
    }
}
