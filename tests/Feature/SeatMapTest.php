<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Seat;
use App\Models\User;
use App\Services\SeatHoldService;
use App\Services\VenueLayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatMapTest extends TestCase
{
    use RefreshDatabase;

    private const PRICES = ['floor' => 400, 'lower_prime' => 250, 'lower_end' => 180, 'upper_prime' => 120, 'upper_end' => 80];

    private function mappedConcert(array $attributes = []): Concert
    {
        $concert = Concert::create($attributes + [
            'title' => 'Arena Night',
            'artist' => 'The Testers',
            'venue' => 'Madison Square Garden',
            'date' => now()->addMonth()->toDateString(),
            'description' => 'A test show.',
            'ticket_price' => 0,
            'total_ticket' => 0,
            'created_by' => User::factory()->create(['is_admin' => true])->id,
        ]);

        app(VenueLayoutService::class)->apply($concert, 'arena-bowl', self::PRICES);

        return $concert->fresh();
    }

    private function buyerWithCard(): array
    {
        $user = User::factory()->create();
        $card = PaymentCard::create([
            'user_id' => $user->id,
            'card_type' => 'visa',
            'full_name' => 'Test Buyer',
            'card_number' => '4111111111111111',
            'expiry_date' => '12/35',
            'cvv' => '123',
            'country' => 'Malaysia',
            'address' => '1 Test Street',
        ]);

        return [$user, $card];
    }

    private function seats(Concert $concert, int $count = 2)
    {
        return Seat::whereIn('seat_section_id', $concert->sections()->pluck('id'))->orderBy('id')->take($count)->get();
    }

    public function test_layout_creates_positioned_seats_and_prices()
    {
        $concert = $this->mappedConcert();

        $this->assertSame(5, $concert->sections()->count());
        $this->assertSame((int) $concert->sections()->sum('capacity'), $concert->total_ticket);
        $this->assertGreaterThan(10000, $concert->total_ticket);
        $this->assertEquals(80, $concert->ticket_price);
        $this->assertSame(0, Seat::whereIn('seat_section_id', $concert->sections()->pluck('id'))->where('block', '')->count());
    }

    public function test_seat_labels_include_the_section()
    {
        $seat = $this->seats($this->mappedConcert(), 1)->first();

        $this->assertSame('Section A, Row A, Seat 1', $seat->label);
    }

    public function test_hold_reserves_seats_and_blocks_other_buyers()
    {
        $concert = $this->mappedConcert();
        $seats = $this->seats($concert);
        [$first] = $this->buyerWithCard();
        [$second] = $this->buyerWithCard();

        $this->actingAs($first)
            ->postJson(route('seats.hold', $concert), ['seat_ids' => $seats->pluck('id')->all()])
            ->assertOk()
            ->assertJson(['redirect' => route('seats.checkout', $concert)]);

        $this->actingAs($second)
            ->postJson(route('seats.hold', $concert), ['seat_ids' => [$seats->first()->id]])
            ->assertStatus(409);
    }

    public function test_expired_holds_free_the_seat()
    {
        $concert = $this->mappedConcert();
        $seat = $this->seats($concert, 1)->first();
        [$first] = $this->buyerWithCard();
        [$second] = $this->buyerWithCard();

        $seat->update(['held_by' => $first->id, 'held_until' => now()->subMinute()]);

        $this->actingAs($second)
            ->postJson(route('seats.hold', $concert), ['seat_ids' => [$seat->id]])
            ->assertOk();

        $this->assertSame($second->id, $seat->fresh()->held_by);
    }

    public function test_a_new_hold_replaces_the_previous_one()
    {
        $concert = $this->mappedConcert();
        [$first, $second] = $this->seats($concert)->all();
        [$user] = $this->buyerWithCard();
        $holds = app(SeatHoldService::class);

        $holds->hold($user, $concert, [$first->id]);
        $holds->hold($user, $concert, [$second->id]);

        $this->assertNull($first->fresh()->held_by);
        $this->assertSame($user->id, $second->fresh()->held_by);
    }

    public function test_hold_limit_is_enforced()
    {
        $concert = $this->mappedConcert();
        [$user] = $this->buyerWithCard();

        $this->expectException(BookingException::class);

        app(SeatHoldService::class)->hold($user, $concert, $this->seats($concert, SeatHoldService::MAX_SEATS + 1)->pluck('id')->all());
    }

    public function test_seats_from_another_concert_are_rejected()
    {
        $concert = $this->mappedConcert();
        $other = $this->mappedConcert(['title' => 'Second Night']);
        [$user] = $this->buyerWithCard();

        $this->actingAs($user)
            ->postJson(route('seats.hold', $concert), ['seat_ids' => [$this->seats($other, 1)->first()->id]])
            ->assertStatus(409);
    }

    public function test_checkout_turns_held_seats_into_an_order()
    {
        $concert = $this->mappedConcert();
        $seats = $this->seats($concert);
        [$user, $card] = $this->buyerWithCard();

        app(SeatHoldService::class)->hold($user, $concert, $seats->pluck('id')->all());

        $this->actingAs($user)->get(route('seats.checkout', $concert))->assertOk()->assertSee('Pay and confirm');

        $response = $this->actingAs($user)->post(route('seats.purchase', $concert), ['payment_card_id' => $card->id]);

        $order = Order::where('user_id', $user->id)->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertEquals(800, $order->total_price);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame($order->id, $seats->first()->fresh()->order_id);
        $this->assertNull($seats->first()->fresh()->held_by);
        $this->assertSame(2, $concert->fresh()->ticketsSold());
    }

    public function test_checkout_fails_after_the_hold_expires()
    {
        $concert = $this->mappedConcert();
        $seat = $this->seats($concert, 1)->first();
        [$user, $card] = $this->buyerWithCard();

        $seat->update(['held_by' => $user->id, 'held_until' => now()->subSecond()]);

        $this->actingAs($user)->get(route('seats.checkout', $concert))->assertRedirect(route('seats.show', $concert));

        $this->actingAs($user)
            ->post(route('seats.purchase', $concert), ['payment_card_id' => $card->id])
            ->assertRedirect(route('seats.show', $concert));

        $this->assertSame(0, Order::count());
    }

    public function test_releasing_a_hold_frees_the_seats()
    {
        $concert = $this->mappedConcert();
        $seat = $this->seats($concert, 1)->first();
        [$user] = $this->buyerWithCard();

        app(SeatHoldService::class)->hold($user, $concert, [$seat->id]);

        $this->actingAs($user)->delete(route('seats.release', $concert))->assertRedirect(route('seats.show', $concert));

        $this->assertNull($seat->fresh()->held_by);
    }

    public function test_cancelling_an_order_returns_the_seat_to_the_map()
    {
        $concert = $this->mappedConcert();
        $seat = $this->seats($concert, 1)->first();
        [$user, $card] = $this->buyerWithCard();

        app(SeatHoldService::class)->hold($user, $concert, [$seat->id]);
        $this->actingAs($user)->post(route('seats.purchase', $concert), ['payment_card_id' => $card->id]);
        $this->actingAs($user)->put(route('orders.cancel', Order::firstOrFail()));

        $this->assertNull($seat->fresh()->order_id);
        $this->actingAs($user)->getJson(route('seats.status', $concert))->assertJson(['sold' => []]);
    }

    public function test_status_separates_my_seats_from_other_holds()
    {
        $concert = $this->mappedConcert();
        [$mine, $theirs] = $this->seats($concert)->all();
        [$me] = $this->buyerWithCard();
        [$them] = $this->buyerWithCard();

        app(SeatHoldService::class)->hold($me, $concert, [$mine->id]);
        app(SeatHoldService::class)->hold($them, $concert, [$theirs->id]);

        $this->actingAs($me)->getJson(route('seats.status', $concert))
            ->assertJson(['mine' => [$mine->id], 'held' => [$theirs->id]]);
    }

    public function test_map_data_is_compact_and_describes_every_seat()
    {
        $concert = $this->mappedConcert();
        [$user] = $this->buyerWithCard();

        $response = $this->actingAs($user)->getJson(route('seats.data', $concert))->assertOk();

        $this->assertCount($concert->total_ticket, $response->json('seats.id'));
        $this->assertCount($concert->total_ticket, $response->json('seats.rotation'));
        $this->assertCount(5, $response->json('tiers'));
    }

    public function test_mapped_concert_sends_buyers_to_the_map()
    {
        $concert = $this->mappedConcert();
        [$user, $card] = $this->buyerWithCard();

        $this->actingAs($user)->get(route('orders.create', $concert))->assertRedirect(route('seats.show', $concert));

        $section = $concert->sections()->first();
        $this->actingAs($user)
            ->post(route('orders.store', $concert), ['tickets' => [$section->id => 1], 'payment_card_id' => $card->id]);

        $this->assertSame(0, Order::count());
    }

    public function test_hidden_concert_is_not_public()
    {
        $concert = $this->mappedConcert(['is_published' => false]);
        [$user] = $this->buyerWithCard();

        $this->get(route('concerts.show', $concert))->assertNotFound();
        $this->get(route('concerts.index'))->assertDontSee('Arena Night');
        $this->get(route('home'))->assertDontSee('Arena Night');
        $this->actingAs($user)->get(route('seats.show', $concert))->assertNotFound();
        $this->actingAs($user)->get(route('orders.create', $concert))->assertNotFound();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('concerts.show', $concert))->assertOk();
    }

    public function test_admin_edits_prices_but_not_seats_on_a_mapped_concert()
    {
        $concert = $this->mappedConcert();
        $capacity = $concert->total_ticket;
        $admin = User::factory()->create(['is_admin' => true]);

        $sections = $concert->sections()->get()->map(function ($section, $index) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'type' => $section->type,
                'price' => $index === 0 ? 500 : $section->price,
                'capacity' => 1,
            ];
        })->all();

        $this->actingAs($admin)->put(route('admin.concerts.update', $concert), [
            'title' => $concert->title,
            'artist' => $concert->artist,
            'venue' => $concert->venue,
            'date' => $concert->date->toDateString(),
            'description' => $concert->description,
            'is_published' => '0',
            'sections' => $sections,
        ])->assertRedirect(route('admin.concerts.show', $concert));

        $concert->refresh();
        $this->assertSame($capacity, $concert->total_ticket);
        $this->assertEquals(500, $concert->sections()->first()->price);
        $this->assertFalse($concert->is_published);
    }

    public function test_calendar_download_contains_the_show()
    {
        $concert = $this->mappedConcert(['start_time' => '20:00']);

        $response = $this->get(route('concerts.calendar', $concert))->assertOk();

        $this->assertStringContainsString('text/calendar', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('SUMMARY:Arena Night', $response->getContent());
        $this->assertStringContainsString('T200000', $response->getContent());
    }

    public function test_map_data_carries_the_artists_stage_video()
    {
        $concert = $this->mappedConcert();
        config(["media.artists.{$concert->artist}.video.url" => 'https://example.test/video.m4v']);

        $this->actingAs(User::factory()->create())
            ->getJson(route('seats.data', $concert))
            ->assertOk()
            ->assertJsonPath('stageVideo', 'https://example.test/video.m4v');
    }

    public function test_every_venue_layout_generates_unique_seats_and_a_working_map()
    {
        foreach (array_keys(config('venues')) as $key) {
            $layout = config("venues.{$key}");
            $concert = Concert::create([
                'title' => "Layout {$key}",
                'artist' => 'The Testers',
                'venue' => $layout['name'],
                'date' => now()->addMonth()->toDateString(),
                'description' => 'A test show.',
                'ticket_price' => 0,
                'total_ticket' => 0,
                'created_by' => User::factory()->create(['is_admin' => true])->id,
            ]);

            $prices = array_fill_keys(array_keys($layout['tiers']), 50);
            app(VenueLayoutService::class)->apply($concert, $key, $prices);

            $seats = iterator_to_array(app(VenueLayoutService::class)->seats($layout), false);
            $places = array_map(function ($seat) {
                return "{$seat['block']}|{$seat['row']}|{$seat['number']}";
            }, $seats);

            $this->assertCount(count($seats), array_unique($places), $key);
            $this->assertSame(array_keys($layout['tiers']), array_keys(array_intersect_key($layout['tiers'], array_flip(array_column($seats, 'tier')))), $key);

            $this->actingAs(User::factory()->create())
                ->getJson(route('seats.data', $concert))
                ->assertOk()
                ->assertJsonPath('layout.scene', $layout['scene'])
                ->assertJsonPath('layout.view.stage', $layout['view']['stage']);
        }
    }
}
