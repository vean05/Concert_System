<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seat;
use App\Models\SeatSection;
use App\Models\User;
use App\Services\BookingService;
use App\Services\VenueLayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class VenueConversionTest extends TestCase
{
    use RefreshDatabase;

    private const PRICES = ['arena' => 165, 'stalls' => 130, 'loggia' => 150, 'grand' => 120, 'second' => 90, 'balcony' => 65];

    private function generalAdmission(): Concert
    {
        $concert = Concert::create([
            'title' => 'Old Show',
            'artist' => 'The Testers',
            'venue' => 'Royal Albert Hall',
            'date' => now()->addMonth()->toDateString(),
            'description' => 'A test show.',
            'ticket_price' => 130,
            'total_ticket' => 1000,
            'is_published' => true,
            'created_by' => User::factory()->create(['is_admin' => true])->id,
        ]);

        SeatSection::create([
            'concert_id' => $concert->id,
            'name' => 'General Admission',
            'type' => SeatSection::STANDING,
            'price' => 130,
            'capacity' => 1000,
        ]);

        return $concert->fresh();
    }

    private function order(Concert $concert, int $quantity, string $status = Order::CONFIRMED): Order
    {
        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'concert_id' => $concert->id,
            'quantity' => $quantity,
            'total_price' => $quantity * 130,
            'status' => $status,
        ]);

        $order->items()->create([
            'seat_section_id' => $concert->sections()->first()->id,
            'section_name' => 'General Admission',
            'quantity' => $quantity,
            'unit_price' => 130,
        ]);

        return $order;
    }

    public function test_fans_get_seats_together_at_the_price_they_paid()
    {
        $concert = $this->generalAdmission();
        $order = $this->order($concert, 4);

        app(VenueLayoutService::class)->convert($concert, 'concert-hall', self::PRICES);

        $order = $order->fresh(['items', 'seats']);

        $this->assertCount(4, $order->seats);
        $this->assertCount(4, $order->items);
        $this->assertSame(1, $order->seats->map(function ($seat) {
            return "{$seat->block}|{$seat->row_label}";
        })->unique()->count());
        $this->assertSame([130.0], $order->items->map(function ($item) {
            return (float) $item->unit_price;
        })->unique()->values()->all());
        $this->assertSame('Stalls', $order->items->first()->section_name);
        $this->assertNotNull($order->items->first()->seat_label);
        $this->assertSame(520.0, (float) $order->total_price);
    }

    public function test_the_show_becomes_a_seat_map_and_sold_counts_stay_right()
    {
        $concert = $this->generalAdmission();
        $this->order($concert, 3);

        app(VenueLayoutService::class)->convert($concert, 'concert-hall', self::PRICES);

        $concert = $concert->fresh();

        $this->assertTrue($concert->hasSeatMap());
        $this->assertSame(0, SeatSection::where('name', 'General Admission')->count());
        $this->assertSame(Seat::count(), $concert->total_ticket);
        $this->assertSame(3, $concert->ticketsSold());
        $this->assertSame(3, Seat::whereNotNull('order_id')->count());
        $this->assertSame(3, $concert->sections()->withSold()->get()->sum('sold_count'));
    }

    public function test_cancelling_after_the_move_frees_the_seats()
    {
        $concert = $this->generalAdmission();
        $order = $this->order($concert, 2);

        app(VenueLayoutService::class)->convert($concert, 'concert-hall', self::PRICES);
        app(BookingService::class)->cancel($order->fresh());

        $this->assertSame(0, Seat::whereNotNull('order_id')->count());
    }

    public function test_cancelled_orders_keep_their_record_and_take_no_seat()
    {
        $concert = $this->generalAdmission();
        $order = $this->order($concert, 2, Order::CANCELLED);

        app(VenueLayoutService::class)->convert($concert, 'concert-hall', self::PRICES);

        $item = OrderItem::where('order_id', $order->id)->sole();

        $this->assertSame('General Admission', $item->section_name);
        $this->assertSame(2, $item->quantity);
        $this->assertNull($item->seat_id);
        $this->assertSame(0, Seat::whereNotNull('order_id')->count());
    }

    public function test_a_show_with_a_seat_map_is_refused_and_left_alone()
    {
        $concert = $this->generalAdmission();
        $layouts = app(VenueLayoutService::class);
        $layouts->convert($concert, 'concert-hall', self::PRICES);
        $seats = Seat::count();

        $this->expectException(InvalidArgumentException::class);

        try {
            $layouts->convert($concert->fresh(), 'concert-hall', self::PRICES);
        } finally {
            $this->assertSame($seats, Seat::count());
        }
    }
}
