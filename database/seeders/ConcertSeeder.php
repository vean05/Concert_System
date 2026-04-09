<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Concert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create an admin user if not exists
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]);
        }

        // Create sample concerts
        $concerts = [
            [
                'title' => 'Taylor Swift: The Eras Tour',
                'artist' => 'Taylor Swift',
                'venue' => 'Madison Square Garden',
                'date' => Carbon::now()->addDays(15),
                'description' => 'Experience the ultimate concert event of the year! Taylor Swift\'s The Eras Tour brings together all the most iconic moments from her legendary career. Featuring hits from all her albums, stunning visuals, and unforgettable performances. This is a must-see event for all Swifties!',
                'ticket_price' => 150.00,
                'total_ticket' => 20000,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'The Weeknd: After Hours Tour',
                'artist' => 'The Weeknd',
                'venue' => 'Crypto.com Arena',
                'date' => Carbon::now()->addDays(25),
                'description' => 'Join The Weeknd on an extraordinary journey through his darkest dreams and wildest fantasies. The After Hours Tour features incredible visual effects, stunning choreography, and all your favorite hits. Limited availability - book your tickets now!',
                'ticket_price' => 120.00,
                'total_ticket' => 18000,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Beyoncé: Renaissance Tour',
                'artist' => 'Beyoncé',
                'venue' => 'MetLife Stadium',
                'date' => Carbon::now()->addDays(30),
                'description' => 'Experience a visual and musical masterpiece as Beyoncé brings the Renaissance to life. This groundbreaking tour celebrates the album that dominated the world. Prepare to be amazed by the production, the sound, and the presence of the most incredible performer alive.',
                'ticket_price' => 200.00,
                'total_ticket' => 82500,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Coldplay: Music of the Spheres Tour',
                'artist' => 'Coldplay',
                'venue' => 'SoFi Stadium',
                'date' => Carbon::now()->addDays(20),
                'description' => 'Coldplay returns with their most ambitious tour ever! The Music of the Spheres Tour is a celebration of their incredible catalog, combined with cutting-edge production and breathtaking visuals. Join thousands of fans for an unforgettable night of music.',
                'ticket_price' => 110.00,
                'total_ticket' => 70000,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Drake: OVO Sound Fest',
                'artist' => 'Drake',
                'venue' => 'Scotiabank Arena',
                'date' => Carbon::now()->addDays(35),
                'description' => 'The largest hip-hop festival in Canada! Drake brings together the hottest artists in the industry for an incredible day of music, entertainment, and pure energy. From Toronto, to the world!',
                'ticket_price' => 95.00,
                'total_ticket' => 20000,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Ed Sheeran: + − × ÷ Tour',
                'artist' => 'Ed Sheeran',
                'venue' => 'Wembley Stadium',
                'date' => Carbon::now()->addDays(40),
                'description' => 'Ed Sheeran brings his massive mathematical tour to legendary Wembley Stadium. Experience all the biggest hits from his career, featuring elaborate stage design and intimate storytelling moments. One of the most anticipated tours of the year!',
                'ticket_price' => 130.00,
                'total_ticket' => 65000,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Billie Eilish: Happier Than Ever Tour',
                'artist' => 'Billie Eilish',
                'venue' => 'Staples Center',
                'date' => Carbon::now()->addDays(22),
                'description' => 'The dark pop sensation takes over! Billie Eilish performs in an immersive experience unlike anything you\'ve ever seen. Watch as she commands the stage with raw energy and authenticity. A generational talent at her finest!',
                'ticket_price' => 85.00,
                'total_ticket' => 20000,
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Ariana Grande: Eternal Sunshine Tour',
                'artist' => 'Ariana Grande',
                'venue' => 'T-Mobile Arena',
                'date' => Carbon::now()->addDays(28),
                'description' => 'Ariana Grande returns with her most spectacular tour yet! The Eternal Sunshine Tour features amazing production, incredible vocal performances, and all the greatest hits. Don\'t miss this magical experience!',
                'ticket_price' => 140.00,
                'total_ticket' => 20000,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($concerts as $concert) {
            Concert::create($concert);
        }
    }
}

