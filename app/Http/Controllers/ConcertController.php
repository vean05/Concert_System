<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Seat;
use App\Models\SeatSection;
use App\Services\SeatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConcertController extends Controller
{
    /**
     * The marketplace landing page.
     */
    public function home()
    {
        $upcoming = Concert::published()->upcoming()->withTicketsSold()->orderBy('date')->get();
        $past = Concert::published()->where('date', '<', today())->withTicketsSold()->orderByDesc('date')->take(8)->get();

        // The carousel always has something to show, even before anything is scheduled.
        $featured = $upcoming->isNotEmpty() ? $upcoming->take(8) : $past->take(6);

        $venues = $upcoming->groupBy('venue')->map(function ($concerts) {
            return (object) ['next' => $concerts->first(), 'shows' => $concerts->count()];
        })->values()->take(4);

        $songs = $upcoming->flatMap(function ($concert) {
            return collect($concert->artistMedia()['songs'] ?? [])->take(2)->map(function ($song) use ($concert) {
                return (object) ($song + ['artist' => $concert->artist, 'concert' => $concert]);
            });
        })->take(8);

        return view('home', [
            'featured' => $featured,
            'upcoming' => $upcoming->take(14),
            'flagship' => $upcoming->first(function ($concert) {
                return $concert->hasSeatMap();
            }),
            'venues' => $venues,
            'past' => $past,
            'songs' => $songs,
            'genres' => $upcoming->whereNotNull('genre')->unique('genre')->values(),
            'artists' => $upcoming->pluck('artist')->merge($past->pluck('artist'))->unique()->values(),
            'hasUpcoming' => $upcoming->isNotEmpty(),
        ]);
    }

    /**
     * Browse concerts, optionally narrowed by search text, venue, genre, or date.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:60',
            'date' => 'nullable|date',
            'when' => 'nullable|in:upcoming,past,all',
        ]);

        $searching = ! empty($filters['q']) || ! empty($filters['date']);
        $when = $filters['when'] ?? ($searching ? 'all' : 'upcoming');
        $today = today()->toDateString();

        $concerts = Concert::published()
            ->withTicketsSold()
            ->when($when === 'upcoming', function ($query) use ($today) {
                $query->whereDate('date', '>=', $today);
            })
            ->when($when === 'past', function ($query) use ($today) {
                $query->whereDate('date', '<', $today);
            })
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('artist', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($filters['venue'] ?? null, function ($query, $venue) {
                $query->where(function ($query) use ($venue) {
                    $query->where('venue', 'like', "%{$venue}%")->orWhere('city', 'like', "%{$venue}%");
                });
            })
            ->when($filters['genre'] ?? null, function ($query, $genre) {
                $query->where('genre', $genre);
            })
            ->when($filters['date'] ?? null, function ($query, $date) {
                $query->whereDate('date', $date);
            })
            ->upcomingFirst()
            ->paginate(12)
            ->withQueryString();

        $genres = Concert::published()->whereNotNull('genre')->distinct()->orderBy('genre')->pluck('genre');

        return view('concerts.index', compact('concerts', 'genres', 'when'));
    }

    /**
     * Display the specified concert.
     */
    public function show(Concert $concert)
    {
        $this->ensureVisible($concert);

        $concert->load(['sections' => function ($sections) {
            $sections->withSold();
        }, 'reviews.user']);

        $userReview = auth()->check() ? $concert->reviews->firstWhere('user_id', auth()->id()) : null;
        $canReview = auth()->check() && ! $userReview && auth()->user()->orders()
            ->where('concert_id', $concert->id)
            ->where('status', 'confirmed')
            ->exists();

        $ratings = collect(range(5, 1))->mapWithKeys(function ($stars) use ($concert) {
            return [$stars => $concert->reviews->where('rating', $stars)->count()];
        });

        $similar = Concert::published()->upcoming()->withTicketsSold()
            ->whereKeyNot($concert->id)
            ->orderByRaw('CASE WHEN genre = ? THEN 0 ELSE 1 END', [(string) $concert->genre])
            ->orderBy('date')
            ->take(4)
            ->get();

        return view('concerts.show', compact('concert', 'userReview', 'canReview', 'ratings', 'similar'));
    }

    /**
     * Download the concert as a calendar entry.
     */
    /**
     * Live sales for the concert page, taken straight from the orders.
     */
    public function pulse(Concert $concert)
    {
        abort_unless($concert->is_published, 404);

        $sections = $concert->sections()->withSold()->get();
        $capacity = $sections->sum('capacity');
        $remaining = $sections->sum(function ($section) {
            return $section->remaining();
        });

        $orders = $concert->orders()->where('status', Order::CONFIRMED);
        $lastSale = (clone $orders)->max('created_at');

        return response()->json([
            'sold_percent' => $capacity ? round(($capacity - $remaining) / $capacity * 100, 1) : 0,
            'remaining' => $remaining,
            'sold_today' => (int) (clone $orders)->where('created_at', '>=', now()->subDay())->sum('quantity'),
            'on_hold' => Seat::whereIn('seat_section_id', $sections->pluck('id'))
                ->whereNull('order_id')
                ->where('held_until', '>', now())
                ->count(),
            'last_sale' => $lastSale ? Carbon::parse($lastSale)->diffForHumans() : null,
            'tiers' => $sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'remaining' => $section->remaining(),
                    'sold_percent' => $section->capacity ? round($section->soldCount() / $section->capacity * 100) : 100,
                ];
            })->values(),
        ]);
    }

    public function calendar(Concert $concert)
    {
        $this->ensureVisible($concert);

        $start = $concert->startsAt();
        $escape = function (string $text) {
            return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $text);
        };

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ConcertHub//Concerts//EN',
            'BEGIN:VEVENT',
            "UID:concert-{$concert->id}@concerthub",
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $start->format('Ymd\THis'),
            'DTEND:' . $start->copy()->addHours(3)->format('Ymd\THis'),
            'SUMMARY:' . $escape($concert->title),
            'LOCATION:' . $escape(trim($concert->venue . ', ' . $concert->city, ', ')),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines) . "\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($concert->title) . '.ics"',
        ]);
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
    public function store(Request $request, SeatingService $seating)
    {
        $this->authorize('create', Concert::class);

        $validated = $request->validate($this->rules() + [
            'date' => 'required|date|after_or_equal:today',
        ]);

        $imagePath = $request->hasFile('image') ? $request->file('image')->store('concerts', 'public') : null;

        $concert = DB::transaction(function () use ($request, $validated, $imagePath, $seating) {
            $concert = Concert::create($this->listing($request, $validated) + [
                'ticket_price' => 0,
                'total_ticket' => 0,
                'created_by' => auth()->id(),
                'image_path' => $imagePath,
            ]);

            $seating->sync($concert, $validated['sections']);

            return $concert;
        });

        return redirect()->route('admin.concerts.show', $concert)->with('success', 'Concert created successfully!');
    }

    /**
     * Show the form for editing the specified concert (Admin only).
     */
    public function edit(Concert $concert)
    {
        $this->authorize('update', $concert);

        $concert->load(['sections' => function ($sections) {
            $sections->withSold();
        }]);

        return view('concerts.edit', compact('concert'));
    }

    /**
     * Update the specified concert in storage (Admin only).
     */
    public function update(Request $request, Concert $concert, SeatingService $seating)
    {
        $this->authorize('update', $concert);

        // A past concert stays editable as long as its date is not being moved.
        $dateUnchanged = $request->input('date') === $concert->date->toDateString();

        $validated = $request->validate($this->rules() + [
            'date' => $dateUnchanged ? 'required|date' : 'required|date|after_or_equal:today',
        ]);

        DB::transaction(function () use ($request, $concert, $validated, $seating) {
            $concert->hasSeatMap()
                ? $seating->updateTiers($concert, $validated['sections'])
                : $seating->sync($concert, $validated['sections']);

            $concert->update($this->listing($request, $validated));
        });

        if ($request->hasFile('image')) {
            $oldImage = $concert->image_path;
            $concert->update(['image_path' => $request->file('image')->store('concerts', 'public')]);

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        return redirect()->route('admin.concerts.show', $concert)->with('success', 'Concert updated successfully!');
    }

    /**
     * Unpublished concerts are only visible to admins.
     */
    private function ensureVisible(Concert $concert): void
    {
        abort_unless($concert->is_published || optional(auth()->user())->is_admin, 404);
    }

    private function listing(Request $request, array $validated): array
    {
        $lineup = array_values(array_filter(array_map('trim', explode(',', (string) ($validated['lineup'] ?? '')))));

        return collect($validated)
            ->only(['title', 'tagline', 'artist', 'genre', 'venue', 'city', 'date', 'doors_time', 'start_time', 'description'])
            ->all() + [
                'lineup' => $lineup ?: null,
                'is_published' => $request->boolean('is_published', true),
            ];
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'artist' => 'required|string|max:255',
            'genre' => 'nullable|string|max:60',
            'venue' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'doors_time' => 'nullable|date_format:H:i',
            'start_time' => 'nullable|date_format:H:i',
            'lineup' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sections' => 'required|array|min:1|max:12',
            'sections.*.id' => 'nullable|integer',
            'sections.*.name' => 'required|string|max:100|distinct',
            'sections.*.type' => ['required', Rule::in([SeatSection::SEATED, SeatSection::STANDING])],
            'sections.*.price' => 'required|numeric|min:0|max:99999999',
            'sections.*.capacity' => 'required|integer|min:1|max:200000',
        ];
    }
}
