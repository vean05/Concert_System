<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Seat;
use App\Models\User;
use App\Services\SeatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function concert(array $attributes = [], ?array $sections = null): Concert
    {
        $concert = Concert::create($attributes + [
            'title' => 'Test Tour',
            'artist' => 'Test Artist',
            'venue' => 'Test Arena',
            'date' => now()->addMonth()->toDateString(),
            'description' => 'A show.',
            'ticket_price' => 0,
            'total_ticket' => 0,
            'created_by' => User::factory()->create(['is_admin' => true])->id,
        ]);

        app(SeatingService::class)->sync($concert, $sections ?? [
            ['name' => 'VIP', 'type' => 'seated', 'price' => 300, 'capacity' => 25],
            ['name' => 'Floor', 'type' => 'standing', 'price' => 100, 'capacity' => 5],
        ]);

        return $concert->fresh();
    }

    private function buyer(): array
    {
        $user = User::factory()->create();
        $card = $user->paymentCards()->create([
            'card_type' => 'visa',
            'card_number' => '4111111111111111',
            'expiry_date' => '12/40',
            'cvv' => '123',
            'full_name' => $user->name,
            'country' => 'Malaysia',
            'address' => '1 Test Street',
        ]);

        return [$user, $card];
    }

    private function book(User $user, Concert $concert, array $tickets, PaymentCard $card)
    {
        return $this->actingAs($user)->post(route('orders.store', $concert), [
            'tickets' => $tickets,
            'payment_card_id' => $card->id,
        ]);
    }

    public function test_seating_sync_derives_price_and_capacity_and_generates_seats()
    {
        $concert = $this->concert();

        $this->assertSame(30, $concert->total_ticket);
        $this->assertEquals(100, $concert->ticket_price);

        $seats = Seat::orderBy('id')->get();
        $this->assertCount(25, $seats);
        $this->assertSame('Row A, Seat 1', $seats->first()->label);
        $this->assertSame('Row B, Seat 5', $seats->last()->label);
    }

    public function test_order_assigns_seats_and_totals_every_tier()
    {
        $concert = $this->concert();
        [$vip, $floor] = $concert->sections()->get()->all();
        [$user, $card] = $this->buyer();

        $this->book($user, $concert, [$vip->id => 2, $floor->id => 3], $card)
            ->assertRedirect(route('orders.show', Order::first()));

        $order = Order::with('items')->first();
        $this->assertSame(5, $order->quantity);
        $this->assertEquals(900, $order->total_price);
        $this->assertCount(3, $order->items);
        $this->assertSame(['Row A, Seat 1', 'Row A, Seat 2'], $order->items->whereNotNull('seat_label')->pluck('seat_label')->all());
        $this->assertSame(2, Seat::where('order_id', $order->id)->count());
        $this->assertSame(3, $concert->fresh()->sections()->get()->last()->soldCount());
    }

    public function test_cannot_buy_more_than_a_tier_has_left()
    {
        $concert = $this->concert();
        $floor = $concert->sections()->get()->last();
        [$user, $card] = $this->buyer();

        $this->book($user, $concert, [$floor->id => 4], $card);
        $this->book($user, $concert, [$floor->id => 2], $card)->assertSessionHas('error', 'Only 1 ticket(s) left in Floor.');

        $this->assertSame(1, Order::count());
    }

    public function test_cannot_book_tiers_of_another_concert_or_with_someone_elses_card()
    {
        $concert = $this->concert();
        $other = $this->concert(['title' => 'Other']);
        [$user, $card] = $this->buyer();
        [, $strangersCard] = $this->buyer();

        $this->book($user, $concert, [$other->sections()->first()->id => 1], $card)->assertSessionHas('error');
        $this->book($user, $concert, [$concert->sections()->first()->id => 1], $strangersCard)->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_past_concerts_cannot_be_booked()
    {
        $concert = $this->concert(['date' => now()->subDay()->toDateString()]);
        [$user, $card] = $this->buyer();

        $this->actingAs($user)->get(route('orders.create', $concert))->assertRedirect(route('concerts.show', $concert));
        $this->book($user, $concert, [$concert->sections()->first()->id => 1], $card)->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_cancelling_releases_seats_and_tickets_exactly_once()
    {
        $concert = $this->concert();
        $vip = $concert->sections()->first();
        [$user, $card] = $this->buyer();

        $this->book($user, $concert, [$vip->id => 2], $card);
        $order = Order::first();

        $this->actingAs($user)->put(route('orders.cancel', $order))->assertSessionHas('success');
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, Seat::whereNotNull('order_id')->count());
        $this->assertSame(0, $vip->fresh()->soldCount());

        $this->actingAs($user)->put(route('orders.cancel', $order))->assertSessionHas('error');
    }

    public function test_orders_for_past_concerts_cannot_be_cancelled()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();

        $this->book($user, $concert, [$concert->sections()->first()->id => 1], $card);
        $concert->update(['date' => now()->subDay()->toDateString()]);

        $this->actingAs($user)->put(route('orders.cancel', Order::first()))->assertSessionHas('error');
        $this->assertSame('confirmed', Order::first()->status);
    }

    public function test_only_the_owner_can_see_or_cancel_an_order()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$stranger] = $this->buyer();

        $this->book($user, $concert, [$concert->sections()->first()->id => 1], $card);

        $this->actingAs($stranger)->get(route('orders.show', Order::first()))->assertForbidden();
        $this->actingAs($stranger)->put(route('orders.cancel', Order::first()))->assertForbidden();
    }

    public function test_sold_tier_is_protected_when_editing_seating()
    {
        $concert = $this->concert();
        [$vip, $floor] = $concert->sections()->get()->all();
        [$user, $card] = $this->buyer();
        $this->book($user, $concert, [$vip->id => 21], $card);

        $sync = function (array $rows) use ($concert) {
            app(SeatingService::class)->sync($concert, $rows);
        };
        $vipRow = ['id' => $vip->id, 'name' => 'VIP', 'type' => 'seated', 'price' => 300, 'capacity' => 25];
        $floorRow = ['id' => $floor->id, 'name' => 'Floor', 'type' => 'standing', 'price' => 100, 'capacity' => 5];

        foreach ([[$floorRow], [['capacity' => 20] + $vipRow, $floorRow], [['type' => 'standing'] + $vipRow, $floorRow]] as $rows) {
            try {
                $sync($rows);
                $this->fail('Protected change was accepted.');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertArrayHasKey('sections', $e->errors());
            }
        }

        $sync([['capacity' => 21] + $vipRow, ['capacity' => 45] + ['type' => 'seated'] + $floorRow]);

        $this->assertSame(21, $vip->seats()->count());
        $this->assertSame(21, $vip->seats()->whereNotNull('order_id')->count());
        $this->assertSame(45, $floor->seats()->count());
        $this->assertSame(66, $concert->fresh()->total_ticket);
    }
}
