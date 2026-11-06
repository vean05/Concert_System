<?php

namespace Database\Seeders;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Database\Seeder;

class UserOrderSeeder extends Seeder
{
    /**
     * Create sample customers with cards and orders placed through the real booking flow.
     *
     * @return void
     */
    public function run(BookingService $booking)
    {
        $concerts = Concert::with('sections')->upcoming()->get();

        if ($concerts->isEmpty()) {
            $this->command->warn('No upcoming concerts found. Please run ConcertSeeder first.');
            return;
        }

        $userNames = [
            'John Smith', 'Emma Johnson', 'Michael Chen', 'Sarah Davis',
            'James Wilson', 'Lisa Anderson', 'David Brown', 'Jessica Taylor',
            'Robert Martinez', 'Emily White', 'William Lee', 'Sophia Garcia',
            'Daniel Rodriguez', 'Olivia Martinez', 'Matthew Hernandez', 'Ava Lopez',
            'Joseph Taylor', 'Isabella Harris', 'Thomas Young', 'Charlotte King',
        ];

        foreach ($userNames as $index => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'user' . ($index + 1) . '@example.com',
                'password' => bcrypt('password'),
            ]);

            $card = $user->paymentCards()->create([
                'card_type' => ['visa', 'master'][rand(0, 1)],
                'card_number' => $this->generateFakeCardNumber(),
                'expiry_date' => $this->generateFutureDate(),
                'cvv' => str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'full_name' => $name,
                'country' => 'United States',
                'address' => rand(100, 9999) . ' Main Street, City, State 12345',
            ]);

            foreach ($concerts->random(min(rand(1, 3), $concerts->count())) as $concert) {
                $section = $concert->sections->random();

                try {
                    $booking->place($user, $concert, [$section->id => rand(1, 4)], $card);
                } catch (BookingException $e) {
                    $this->command->warn("Skipped an order for {$name}: {$e->getMessage()}");
                }
            }
        }

        $this->command->info('Successfully created 20 users with orders!');
    }

    /**
     * Generate a fake but realistic-looking credit card number
     */
    private function generateFakeCardNumber(): string
    {
        $cardNumber = '';

        for ($i = 0; $i < 16; $i++) {
            $cardNumber .= rand(0, 9);
        }

        return $cardNumber;
    }

    /**
     * Generate a future expiry date (MM/YY format)
     */
    private function generateFutureDate(): string
    {
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $year = date('y', strtotime('+' . rand(1, 5) . ' years'));

        return $month . '/' . $year;
    }
}
