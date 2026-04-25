<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Concert;
use App\Models\Order;
use App\Models\PaymentCard;
use Illuminate\Database\Seeder;

class UserOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all concerts
        $concerts = Concert::all();

        if ($concerts->isEmpty()) {
            $this->command->warn('No concerts found. Please run ConcertSeeder first.');
            return;
        }

        // Define realistic user data
        $userNames = [
            'John Smith', 'Emma Johnson', 'Michael Chen', 'Sarah Davis',
            'James Wilson', 'Lisa Anderson', 'David Brown', 'Jessica Taylor',
            'Robert Martinez', 'Emily White', 'William Lee', 'Sophia Garcia',
            'Daniel Rodriguez', 'Olivia Martinez', 'Matthew Hernandez', 'Ava Lopez',
            'Joseph Taylor', 'Isabella Harris', 'Thomas Young', 'Charlotte King'
        ];

        // Define payment card types
        $cardTypes = ['Visa', 'Mastercard', 'American Express'];

        // Create 20 users with orders
        for ($i = 0; $i < 20; $i++) {
            $user = User::create([
                'name' => $userNames[$i],
                'email' => 'user' . ($i + 1) . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'user'
            ]);

            // Create 1-2 payment cards for the user
            $cardCount = rand(1, 2);
            for ($c = 0; $c < $cardCount; $c++) {
                PaymentCard::create([
                    'user_id' => $user->id,
                    'card_type' => $cardTypes[array_rand($cardTypes)],
                    'card_number' => $this->generateFakeCardNumber(),
                    'expiry_date' => $this->generateFutureDate(),
                    'cvv' => str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'full_name' => $user->name,
                    'country' => 'United States',
                    'address' => rand(100, 9999) . ' Main Street, City, State 12345'
                ]);
            }

            // Create 1-3 orders for this user
            $orderCount = rand(1, 3);
            $selectedConcerts = $concerts->random(min($orderCount, $concerts->count()));

            foreach ($selectedConcerts as $concert) {
                // Check available tickets
                $totalOrdered = Order::where('concert_id', $concert->id)
                    ->where('status', 'confirmed')
                    ->sum('quantity');
                $availableTickets = $concert->total_ticket - $totalOrdered;

                // Only create order if tickets are available
                if ($availableTickets > 0) {
                    // Random quantity: 1-4 tickets (but not more than available)
                    $quantity = rand(1, min(4, $availableTickets));
                    
                    Order::create([
                        'user_id' => $user->id,
                        'concert_id' => $concert->id,
                        'quantity' => $quantity,
                        'total_price' => $concert->ticket_price * $quantity,
                        'status' => 'confirmed'
                    ]);
                }
            }

            $this->command->line("Created user: {$user->name} with orders");
        }

        $this->command->info('Successfully created 20 users with orders!');
    }

    /**
     * Generate a fake but realistic-looking credit card number
     */
    private function generateFakeCardNumber(): string
    {
        // Generate 16-digit card number for Visa/Mastercard
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
