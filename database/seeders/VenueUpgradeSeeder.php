<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Services\VenueLayoutService;
use Illuminate\Database\Seeder;

/**
 * Moves the general-admission shows onto 3D venues. Safe to run again: shows that
 * already have a seat map are skipped. Prices include each show's old ticket price
 * as one tier, so existing fans land on seats at what they paid.
 */
class VenueUpgradeSeeder extends Seeder
{
    private const UPGRADES = [
        'Taylor Swift: The Eras Tour' => ['arena-bowl', ['floor' => 320, 'lower_prime' => 220, 'lower_end' => 150, 'upper_prime' => 110, 'upper_end' => 75]],
        'The Weeknd: After Hours Tour' => ['monolith-arena', ['catwalk' => 280, 'floor' => 200, 'lower_side' => 160, 'lower_end' => 120, 'upper' => 80]],
        'Billie Eilish: Happier Than Ever Tour' => ['monolith-arena', ['catwalk' => 210, 'floor' => 150, 'lower_side' => 115, 'lower_end' => 85, 'upper' => 60]],
        'Beyoncé: Renaissance Tour' => ['open-stadium', ['floor_gold' => 450, 'floor' => 320, 'lower' => 200, 'mezzanine' => 150, 'upper' => 95]],
        'Coldplay: Music of the Spheres Tour' => ['canopy-stadium', ['floor_front' => 260, 'floor' => 180, 'lower' => 110, 'upper' => 70]],
        'Drake: OVO Sound Fest' => ['cube-arena', ['court_inner' => 240, 'court_outer' => 170, 'lower' => 95, 'upper' => 65]],
        'Ed Sheeran: + − × ÷ Tour' => ['arch-stadium', ['pitch_inner' => 260, 'pitch_outer' => 190, 'lower' => 130, 'upper' => 85]],
        'Ariana Grande: Eternal Sunshine Tour' => ['neon-arena', ['runway' => 340, 'floor' => 240, 'lower' => 180, 'club' => 140, 'upper' => 90]],
    ];

    public function run(VenueLayoutService $layouts)
    {
        foreach (self::UPGRADES as $title => [$layout, $prices]) {
            $concert = Concert::where('title', $title)->first();

            if ($concert && ! $concert->hasSeatMap()) {
                $layouts->convert($concert, $layout, $prices);
                $this->command->info("{$title}: {$concert->fresh()->total_ticket} seats");
            }
        }

        // The building's current name, which the seat page also uses.
        Concert::where('venue', 'Staples Center')->update(['venue' => 'Crypto.com Arena']);
    }
}
