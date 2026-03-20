<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;

class ConcertController extends Controller
{
    /**
     * Display a listing of all concerts.
     */
    public function index()
    {
        $concerts = Concert::with('creator')->paginate(15);
        return view('concerts.index', compact('concerts'));
    }

    /**
     * Show the form for creating a new concert (Admin only).
     */
    public function create()
    {
        $this->authorize('create', Concert::class);
        return view('concerts.create');
    }

    /**
     * Store a newly created concert in storage (Admin only).
     */
    public function store(Request $request)
    {
        $this->authorize('create', Concert::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'date' => 'required|date|after:today',
            'description' => 'required|string',
            'ticket_price' => 'required|numeric|min:0',
            'total_ticket' => 'required|integer|min:1',
        ]);

        Concert::create([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'venue' => $validated['venue'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'ticket_price' => $validated['ticket_price'],
            'total_ticket' => $validated['total_ticket'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('concerts.index')->with('success', 'Concert created successfully!');
    }

    /**
     * Display the specified concert.
     */
    public function show(Concert $concert)
    {
        return view('concerts.show', compact('concert'));
    }

    /**
     * Show the form for editing the specified concert (Admin only).
     */
    public function edit(Concert $concert)
    {
        $this->authorize('update', $concert);
        return view('concerts.edit', compact('concert'));
    }

    /**
     * Update the specified concert in storage (Admin only).
     */
    public function update(Request $request, Concert $concert)
    {
        $this->authorize('update', $concert);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'date' => 'required|date|after:today',
            'description' => 'required|string',
            'ticket_price' => 'required|numeric|min:0',
            'total_ticket' => 'required|integer|min:1',
        ]);

        $concert->update($validated);

        return redirect()->route('concerts.show', $concert)->with('success', 'Concert updated successfully!');
    }

    /**
     * Remove the specified concert from storage (Admin only).
     */
    public function destroy(Concert $concert)
    {
        $this->authorize('delete', $concert);
        $concert->delete();

        return redirect()->route('concerts.index')->with('success', 'Concert deleted successfully!');
    }

    /**
     * Search concerts by artist.
     */
    public function search(Request $request)
    {
        $artist = $request->input('artist');
        $concerts = Concert::where('artist', 'like', "%{$artist}%")
            ->orWhere('title', 'like', "%{$artist}%")
            ->paginate(15);

        return view('concerts.index', compact('concerts'));
    }

    /**
     * Filter concerts by date or venue.
     */
    public function filter(Request $request)
    {
        $query = Concert::query();

        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        if ($request->has('venue') && $request->venue) {
            $query->where('venue', 'like', "%{$request->venue}%");
        }

        $concerts = $query->paginate(15);

        return view('concerts.index', compact('concerts'));
    }
}
