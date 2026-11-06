<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\PromoCode;
use App\Models\Seat;
use App\Models\User;
use App\Services\BookingService;
use App\Services\SeatHoldService;
use App\Services\TicketPassService;
use App\Services\VenueLayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function concert(array $attributes = []): Concert
    {
        $concert = Concert::create($attributes + [
            'title' => 'Arena Night',
            'artist' => 'The Testers',
            'venue' => 'Madison Square Garden',
            'date' => now()->addMonth()->toDateString(),
            'description' => 'A test show.',
            'ticket_price' => 0,
            'total_ticket' => 0,
            'is_published' => true,
            'created_by' => User::factory()->create(['is_admin' => true])->id,
        ]);

        app(VenueLayoutService::class)->apply($concert, 'concert-hall', [
            'arena' => 165, 'stalls' => 130, 'loggia' => 150, 'grand' => 120, 'second' => 90, 'balcony' => 65,
        ]);

        return $concert->fresh();
    }

    private function buyer(): array
    {
        $user = User::factory()->create();

        return [$user, PaymentCard::create([
            'user_id' => $user->id,
            'card_type' => 'visa',
            'full_name' => 'Test Buyer',
            'card_number' => '4111111111111111',
            'expiry_date' => '12/35',
            'cvv' => '123',
            'country' => 'Malaysia',
            'address' => '1 Test Street',
        ])];
    }

    private function holdTwo(User $user, Concert $concert): float
    {
        $seats = Seat::with('section')
            ->whereIn('seat_section_id', $concert->sections()->pluck('id'))
            ->whereNull('order_id')
            ->where(function ($query) {
                $query->whereNull('held_until')->orWhere('held_until', '<', now());
            })
            ->orderBy('id')
            ->limit(2)
            ->get();

        app(SeatHoldService::class)->hold($user, $concert, $seats->pluck('id')->all());

        return (float) $seats->sum(function ($seat) {
            return $seat->section->price;
        });
    }

    private function buy(User $user, PaymentCard $card, Concert $concert, ?string $code = null): Order
    {
        $this->holdTwo($user, $concert);

        return app(BookingService::class)->purchaseHeldSeats($user, $concert, $card, $code);
    }

    public function test_promo_preview_prices_the_cart_without_spending_the_code()
    {
        $promo = PromoCode::create(['code' => 'TEN', 'percent_off' => 10]);
        $concert = $this->concert();
        [$user] = $this->buyer();
        $subtotal = $this->holdTwo($user, $concert);

        $this->actingAs($user)->postJson(route('seats.promo', $concert), ['code' => ' ten '])
            ->assertOk()
            ->assertJson(['code' => 'TEN', 'discount' => round($subtotal * .1, 2), 'total' => $subtotal - round($subtotal * .1, 2)]);

        $this->assertSame(0, (int) $promo->fresh()->used_count);
    }

    public function test_purchase_applies_the_code_and_cancelling_gives_the_use_back()
    {
        $promo = PromoCode::create(['code' => 'TEN', 'percent_off' => 10]);
        $concert = $this->concert();
        [$user, $card] = $this->buyer();

        $order = $this->buy($user, $card, $concert, 'TEN');

        $this->assertGreaterThan(0, (float) $order->discount);
        $this->assertSame(1, (int) $promo->fresh()->used_count);

        app(BookingService::class)->cancel($order);

        $this->assertSame(0, (int) $promo->fresh()->used_count);
    }

    public function test_a_fan_can_use_a_code_once_even_after_sending_the_tickets_on()
    {
        PromoCode::create(['code' => 'TEN', 'percent_off' => 10]);
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$friend] = $this->buyer();

        $order = $this->buy($user, $card, $concert, 'TEN');
        app(BookingService::class)->transfer($order, $friend);
        $this->holdTwo($user, $concert);

        $this->expectException(BookingException::class);
        app(BookingService::class)->purchaseHeldSeats($user, $concert, $card, 'TEN');
    }

    public function test_used_up_expired_and_switched_off_codes_are_refused_and_seats_stay_held()
    {
        PromoCode::create(['code' => 'FULL', 'percent_off' => 10, 'max_uses' => 1])->increment('used_count');
        PromoCode::create(['code' => 'OLD', 'percent_off' => 10, 'expires_at' => now()->subDay()]);
        PromoCode::create(['code' => 'OFF', 'percent_off' => 10, 'is_active' => false]);
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        $this->holdTwo($user, $concert);

        foreach (['FULL', 'OLD', 'OFF', 'NOPE'] as $code) {
            $this->actingAs($user)->postJson(route('seats.promo', $concert), ['code' => $code])->assertStatus(422);

            try {
                app(BookingService::class)->purchaseHeldSeats($user, $concert, $card, $code);
                $this->fail("{$code} should have been refused");
            } catch (BookingException $e) {
                $this->assertSame(0, Order::count());
            }
        }

        $this->assertSame(2, Seat::where('held_by', $user->id)->count());
    }

    public function test_door_admits_a_fresh_code_once()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        $order = $this->buy($user, $card, $concert);
        $passes = app(TicketPassService::class);

        $this->assertSame(TicketPassService::ADMITTED, $passes->admit($passes->code($order))['status']);
        $this->assertSame(TicketPassService::ALREADY_IN, $passes->admit($passes->code($order))['status']);
        $this->assertNotNull($order->fresh()->checked_in_at);
        $this->assertFalse($order->fresh()->canBeCancelled());
    }

    public function test_door_refuses_old_forged_and_cancelled_codes()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        $order = $this->buy($user, $card, $concert);
        $passes = app(TicketPassService::class);
        $now = time();

        $this->assertSame(TicketPassService::EXPIRED, $passes->admit($passes->code($order, $now - 120), $now)['status']);
        $this->assertSame(TicketPassService::INVALID, $passes->admit($passes->code($order) . 'x')['status']);
        $this->assertSame(TicketPassService::INVALID, $passes->admit('not a code')['status']);
        $this->assertNull($order->fresh()->checked_in_at);

        $code = $passes->code($order);
        app(BookingService::class)->cancel($order);
        $this->assertSame(TicketPassService::CANCELLED, $passes->admit($code)['status']);
    }

    public function test_the_previous_window_still_scans()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        $order = $this->buy($user, $card, $concert);
        $passes = app(TicketPassService::class);
        $now = time();

        $this->assertSame(
            TicketPassService::ADMITTED,
            $passes->admit($passes->code($order, $now - TicketPassService::SECONDS), $now)['status']
        );
    }

    public function test_pass_endpoint_is_private_and_closed_for_cancelled_orders()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$stranger] = $this->buyer();
        $order = $this->buy($user, $card, $concert);

        $this->actingAs($user)->getJson(route('orders.pass', $order))
            ->assertOk()
            ->assertJsonStructure(['code', 'seconds_left', 'period']);
        $this->actingAs($stranger)->getJson(route('orders.pass', $order))->assertForbidden();

        app(BookingService::class)->cancel($order);
        $this->actingAs($user)->getJson(route('orders.pass', $order))->assertStatus(410);
    }

    public function test_transfer_moves_the_order_and_kills_the_senders_code()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$friend] = $this->buyer();
        $order = $this->buy($user, $card, $concert);
        $passes = app(TicketPassService::class);
        $oldCode = $passes->code($order);

        $this->actingAs($user)->post(route('orders.transfer', $order), ['email' => $friend->email])
            ->assertRedirect(route('orders.index'));

        $order = $order->fresh();
        $this->assertSame($friend->id, (int) $order->user_id);
        $this->assertSame($user->id, (int) $order->transferred_from_id);
        $this->assertFalse($order->canBeCancelled());
        $this->assertSame(TicketPassService::INVALID, $passes->admit($oldCode)['status']);
        $this->actingAs($user)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_recipient_does_not_see_the_senders_card_or_price()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$friend] = $this->buyer();
        $order = $this->buy($user, $card, $concert);
        app(BookingService::class)->transfer($order, $friend);

        $this->actingAs($friend)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sent to you by')
            ->assertDontSee('ending in')
            ->assertDontSee('Total paid');
    }

    public function test_transfer_refuses_unknown_accounts_self_strangers_and_used_tickets()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$friend] = $this->buyer();
        $order = $this->buy($user, $card, $concert);

        $this->actingAs($user)->post(route('orders.transfer', $order), ['email' => 'nobody@example.com'])->assertSessionHas('error');
        $this->actingAs($user)->post(route('orders.transfer', $order), ['email' => $user->email])->assertSessionHas('error');
        $this->actingAs($friend)->post(route('orders.transfer', $order), ['email' => $friend->email])->assertForbidden();

        $order->update(['checked_in_at' => now()]);
        $this->actingAs($user)->post(route('orders.transfer', $order), ['email' => $friend->email])->assertSessionHas('error');

        $this->assertSame($user->id, (int) $order->fresh()->user_id);
    }

    public function test_pulse_reports_real_sales_and_holds()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        [$other] = $this->buyer();
        $this->buy($user, $card, $concert);
        $this->holdTwo($other, $concert);

        $this->getJson(route('concerts.pulse', $concert))
            ->assertOk()
            ->assertJson(['sold_today' => 2, 'on_hold' => 2]);

        $concert->update(['is_published' => false]);
        $this->getJson(route('concerts.pulse', $concert))->assertNotFound();
    }

    public function test_door_and_promo_pages_are_admin_only()
    {
        [$user] = $this->buyer();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)->get(route('admin.check-in'))->assertRedirect();
        $this->actingAs($user)->post(route('admin.promos.store'), ['code' => 'X', 'percent_off' => 10])->assertRedirect();
        $this->assertSame(0, PromoCode::count());

        $this->actingAs($admin)->get(route('admin.check-in'))->assertOk();
        $this->actingAs($admin)->post(route('admin.promos.store'), ['code' => 'summer', 'percent_off' => 15])->assertRedirect();
        $this->assertDatabaseHas('promo_codes', ['code' => 'SUMMER', 'percent_off' => 15]);

        $promo = PromoCode::first();
        $this->actingAs($admin)->put(route('admin.promos.toggle', $promo))->assertRedirect();
        $this->assertFalse($promo->fresh()->is_active);
        $this->actingAs($admin)->get(route('admin.promos.index'))->assertOk()->assertSee('SUMMER');
    }

    public function test_the_door_page_tells_staff_the_result()
    {
        $concert = $this->concert();
        [$user, $card] = $this->buyer();
        $order = $this->buy($user, $card, $concert);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->followingRedirects()
            ->post(route('admin.check-in.store'), ['code' => app(TicketPassService::class)->code($order)])
            ->assertOk()
            ->assertSee('Let them in')
            ->assertSee($order->reference);
    }
}
