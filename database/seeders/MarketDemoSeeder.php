<?php

namespace Database\Seeders;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\PromoCode;
use App\Models\Seat;
use App\Models\SeatSection;
use App\Models\User;
use App\Services\BookingService;
use App\Services\SeatHoldService;
use App\Services\SeatingService;
use App\Services\VenueLayoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fills the marketplace: adds demo upcoming concerts (one on the 3D arena map),
 * labels the existing sample concerts, and hides leftover test concerts
 * without deleting them. Existing concert dates are not touched. Safe to run again.
 *
 *     php artisan db:seed --class=MarketDemoSeeder
 */
class MarketDemoSeeder extends Seeder
{
    private const TEST_CONCERT_TITLES = ['ddddddd', 'junhao', 'gdgd'];

    private const SAMPLE_DETAILS = [
        'Taylor Swift: The Eras Tour' => ['Pop', 'New York'],
        'The Weeknd: After Hours Tour' => ['R&B', 'Los Angeles'],
        'Beyoncé: Renaissance Tour' => ['Pop', 'New Jersey'],
        'Coldplay: Music of the Spheres Tour' => ['Rock', 'Los Angeles'],
        'Drake: OVO Sound Fest' => ['Hip-Hop', 'Toronto'],
        'Ed Sheeran: + − × ÷ Tour' => ['Pop', 'London'],
        'Billie Eilish: Happier Than Ever Tour' => ['Alternative', 'Los Angeles'],
        'Ariana Grande: Eternal Sunshine Tour' => ['Pop', 'Las Vegas'],
    ];

    private const NEW_CONCERTS = [
        [
            'title' => 'Dua Lipa: Live at Madison Square Garden', 'artist' => 'Dua Lipa', 'genre' => 'Pop',
            'tagline' => 'A full-production pop night, wall to wall with dance floor anthems',
            'venue' => 'Madison Square Garden', 'city' => 'New York', 'days' => 24, 'doors' => '18:30', 'start' => '20:00',
            'layout' => 'arena-bowl',
            'prices' => ['floor' => 420, 'lower_prime' => 260, 'lower_end' => 190, 'upper_prime' => 130, 'upper_end' => 85],
        ],
        [
            'title' => 'Jay Chou: Live at Axiata Arena', 'artist' => 'Jay Chou', 'genre' => 'Mandopop',
            'tagline' => 'One night, every song, and the biggest singalong of the year',
            'venue' => 'Axiata Arena', 'city' => 'Kuala Lumpur', 'days' => 16, 'doors' => '18:00', 'start' => '19:30',
            'layout' => 'round-arena',
            'prices' => ['runway' => 368, 'floor' => 268, 'lower_side' => 188, 'lower_end' => 138, 'upper' => 88],
        ],
        [
            'title' => 'Calvin Harris: Live at Tokyo Dome', 'artist' => 'Calvin Harris', 'genre' => 'Electronic',
            'tagline' => 'A live electronic set built around 40 metres of LED',
            'venue' => 'Tokyo Dome', 'city' => 'Tokyo', 'days' => 33, 'doors' => '17:30', 'start' => '19:00',
            'layout' => 'dome-stadium',
            'prices' => ['field_inner' => 320, 'field_outer' => 220, 'lower' => 160, 'upper' => 110],
        ],
        [
            'title' => 'Arctic Monkeys: Live at Wembley Arena', 'artist' => 'Arctic Monkeys', 'genre' => 'Indie Rock',
            'tagline' => 'Guitars, swagger and a chorus from every album',
            'venue' => 'Wembley Arena', 'city' => 'London', 'days' => 41, 'doors' => '18:30', 'start' => '20:00',
            'layout' => 'shoebox-arena',
            'prices' => ['floor' => 195, 'lower_side' => 145, 'lower_end' => 110, 'upper' => 72],
        ],
        [
            'title' => 'Tame Impala: Live at Hollywood Bowl', 'artist' => 'Tame Impala', 'genre' => 'Alternative',
            'tagline' => 'An open-air evening of psychedelic light and sound',
            'venue' => 'Hollywood Bowl', 'city' => 'Los Angeles', 'days' => 52, 'doors' => '17:30', 'start' => '19:30',
            'layout' => 'amphitheatre',
            'prices' => ['garden' => 280, 'terrace' => 185, 'bench' => 125, 'upper' => 95],
        ],
        [
            'title' => 'Norah Jones: Live at Royal Albert Hall', 'artist' => 'Norah Jones', 'genre' => 'Jazz',
            'tagline' => 'Piano, voice and the songs that never left',
            'venue' => 'Royal Albert Hall', 'city' => 'London', 'days' => 63, 'doors' => '18:45', 'start' => '19:30',
            'layout' => 'concert-hall',
            'prices' => ['arena' => 165, 'stalls' => 130, 'loggia' => 150, 'grand' => 120, 'second' => 90, 'balcony' => 65],
        ],
    ];

    public function run(SeatingService $seating, VenueLayoutService $layouts, SeatHoldService $holds, BookingService $booking)
    {
        PromoCode::firstOrCreate(['code' => 'WELCOME10'], ['percent_off' => 10]);
        PromoCode::firstOrCreate(['code' => 'ENCORE20'], ['percent_off' => 20, 'max_uses' => 100]);

        Concert::whereIn('title', self::TEST_CONCERT_TITLES)->update(['is_published' => false]);

        foreach (self::SAMPLE_DETAILS as $title => [$genre, $city]) {
            Concert::where('title', $title)->whereNull('genre')->update(['genre' => $genre, 'city' => $city]);
        }

        $admin = User::where('is_admin', true)->orderBy('id')->firstOrFail();

        foreach (self::NEW_CONCERTS as $data) {
            if (Concert::where('title', $data['title'])->exists()) {
                continue;
            }

            $concert = Concert::create([
                'title' => $data['title'],
                'tagline' => $data['tagline'],
                'artist' => $data['artist'],
                'genre' => $data['genre'],
                'venue' => $data['venue'],
                'city' => $data['city'],
                'date' => now()->addDays($data['days'])->toDateString(),
                'doors_time' => $data['doors'],
                'start_time' => $data['start'],
                'description' => "{$data['tagline']}. {$data['artist']} at {$data['venue']} in {$data['city']}.\n\nDemo listing for this project, not a real tour date.",
                'lineup' => [$data['artist']],
                'ticket_price' => 0,
                'total_ticket' => 0,
                'created_by' => $admin->id,
            ]);

            if (isset($data['layout'])) {
                $layouts->apply($concert, $data['layout'], $data['prices']);

                if ($data['layout'] === 'arena-bowl') {
                    $this->sellSomeSeats($concert->fresh(), $holds, $booking);
                }
            } else {
                $seating->sync($concert, [
                    ['name' => 'Premium Seats', 'type' => SeatSection::SEATED, 'price' => $data['price'] * 2.4, 'capacity' => 300],
                    ['name' => 'Reserved Seats', 'type' => SeatSection::SEATED, 'price' => $data['price'] * 1.5, 'capacity' => 1200],
                    ['name' => 'General Admission', 'type' => SeatSection::STANDING, 'price' => $data['price'], 'capacity' => $data['capacity'] - 1500],
                ]);
            }
        }

        $this->command->info('Marketplace demo content is ready.');
    }

    /**
     * Sell scattered groups of seats through the real checkout, so the map
     * opens with a realistic spread of taken seats.
     */
    private function sellSomeSeats(Concert $concert, SeatHoldService $holds, BookingService $booking): void
    {
        $fan = User::firstOrCreate(
            ['email' => 'demo.fans@concerthub.test'],
            ['name' => 'Demo Fans', 'password' => bcrypt(Str::random(40))]
        );

        $card = $fan->paymentCards()->firstOrCreate(['full_name' => 'Demo Fans'], [
            'card_type' => 'visa',
            'card_number' => '4111111111111111',
            'expiry_date' => '12/35',
            'cvv' => '123',
            'country' => 'United States',
            'address' => '1 Demo Street',
        ]);

        $seatIds = Seat::whereIn('seat_section_id', $concert->sections()->pluck('id'))->orderBy('id')->pluck('id');

        foreach ($seatIds->shuffle()->take(220) as $firstSeat) {
            $group = $seatIds->filter(function ($id) use ($firstSeat) {
                return $id >= $firstSeat && $id < $firstSeat + 4;
            })->all();

            try {
                $holds->hold($fan, $concert, $group);
                $booking->purchaseHeldSeats($fan, $concert, $card);
            } catch (BookingException $e) {
                // Two groups landed on the same seats; the map is still realistic.
                $holds->release($fan, $concert);
            }
        }
    }
}
