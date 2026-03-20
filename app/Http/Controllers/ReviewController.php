<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Show form to create a new review.
     */
    public function create(Concert $concert)
    {
        return view('reviews.create', compact('concert'));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, Concert $concert)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        // Check if user has purchased a ticket for this concert
        $hasPurchased = auth()->user()->orders()
            ->where('concert_id', $concert->id)
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasPurchased) {
            return redirect()->back()->with('error', 'You must purchase a ticket to review this concert!');
        }

        Review::create([
            'user_id' => auth()->id(),
            'concert_id' => $concert->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('concerts.show', $concert)->with('success', 'Review added successfully!');
    }

    /**
     * Show edit form for a review.
     */
    public function edit(Review $review)
    {
        $this->authorize('update', $review);
        $concert = $review->concert;

        return view('reviews.edit', compact('review', 'concert'));
    }

    /**
     * Update a review.
     */
    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $review->update($validated);

        return redirect()->route('concerts.show', $review->concert)->with('success', 'Review updated successfully!');
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        $concert = $review->concert;
        $review->delete();

        return redirect()->route('concerts.show', $concert)->with('success', 'Review deleted successfully!');
    }
}
