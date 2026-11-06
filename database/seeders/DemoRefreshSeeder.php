<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\SeatSection;
use App\Services\SeatingService;
use Illuminate\Database\Seeder;

/**
 * The sample concerts were dated relative to when they were first seeded, so
 * they eventually fall into the past and can no longer be booked. This moves
 * them into the future and gives single-tier ones VIP and reserved seating,
 * without touching existing orders. Safe to run again.
 *
 *     php artisan db:seed --class=DemoRefreshSeeder
 */
class DemoRefreshSeeder extends Seeder
{
    private const SAMPLE_TITLES = [
        'Taylor Swift: The Eras Tour' => 15,
        'The Weeknd: After Hours Tour' => 25,
        'Beyoncé: Renaissance Tour' => 30,
        'Coldplay: Music of the Spheres Tour' => 20,
        'Drake: OVO Sound Fest' => 35,
        'Ed Sheeran: + − × ÷ Tour' => 40,
        'Billie Eilish: Happier Than Ever Tour' => 22,
        'Ariana Grande: Eternal Sunshine Tour' => 28,
    ];

    public function run(SeatingService $seating)
    {
        foreach (self::SAMPLE_TITLES as $title => $daysAhead) {
            $concert = Concert::where('title', $title)->first();

            if (! $concert) {
                continue;
            }

            if (! $concert->isUpcoming()) {
                $concert->update(['date' => now()->addDays($daysAhead)->toDateString()]);
            }

            $sections = $concert->sections()->get();

            if ($sections->count() === 1) {
                $standing = $sections->first();

                $seating->sync($concert, [
                    ['name' => 'VIP Front Row', 'type' => SeatSection::SEATED, 'price' => $standing->price * 3, 'capacity' => 200],
                    ['name' => 'Reserved Seating', 'type' => SeatSection::SEATED, 'price' => $standing->price * 1.5, 'capacity' => 1000],
                    [
                        'id' => $standing->id,
                        'name' => $standing->name,
                        'type' => $standing->type,
                        'price' => $standing->price,
                        'capacity' => $standing->capacity - 1200,
                    ],
                ]);
            }
        }

        $this->command->info('Sample concerts refreshed.');
    }
}
