<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Seat;
use App\Models\SeatParty;
use App\Models\User;
use App\Services\BookingService;
use App\Services\SalesMapService;
use App\Services\SeatHoldService;
use App\Services\SeatPartyService;
use App\Services\VenueLayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatPartyTest extends TestCase
{
    use RefreshDatabase;

    private const PRICES = ['arena' => 165, 'stalls' => 130, 'loggia' => 150, 'grand' => 120, 'second' => 90, 'balcony' => 65];

    private function concert(string $title = 'Group Night'): Concert
    {
        $concert = Concert::create([
            'title' => $title,
            'artist' => 'The Testers',
            'venue' => 'Royal Albert Hall',
            'date' => now()->addMonth()->toDateString(),
            'description' => 'A test show.',
            'ticket_price' => 0,
            'total_ticket' => 0,
            'is_published' => true,
            'created_by' => User::factory()->create(['is_admin' => true])->id,
        ]);

        app(VenueLayoutService::class)->apply($concert, 'concert-hall', self::PRICES);

        return $concert->fresh();
    }

    private function seatIds(Concert $concert, int $count = 2): array
    {
        return Seat::whereIn('seat_section_id', $concert->sections()->pluck('id'))->orderBy('id')->take($count)->pluck('id')->all();
    }

    private function buy(Concert $concert, array $seatIds): Order
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

        app(SeatHoldService::class)->hold($user, $concert, $seatIds);

        return app(BookingService::class)->purchaseHeldSeats($user, $concert, $card);
    }

    public function test_friends_share_picks_through_a_link()
    {
        $concert = $this->concert();
        $owner = User::factory()->create(['name' => 'Alex Tan']);
        $friend = User::factory()->create(['name' => 'Sam Lee']);
        [$first, $second] = $this->seatIds($concert);

        $state = $this->actingAs($owner)->postJson(route('seats.party.start', $concert))->assertOk()->json();
        $this->assertSame(1, SeatParty::count());

        // Pressing invite twice keeps the same group.
        $this->actingAs($owner)->postJson(route('seats.party.start', $concert))->assertJsonPath('code', $state['code']);
        $this->assertSame(1, SeatParty::count());

        $this->actingAs($friend)->get($state['link'])->assertOk()->assertSee('data-party-code="' . $state['code'] . '"', false);

        $this->actingAs($owner)
            ->postJson(route('seats.party.sync', [$concert, $state['code']]), ['seat_ids' => [$first]])
            ->assertOk();

        $this->actingAs($friend)
            ->postJson(route('seats.party.sync', [$concert, $state['code']]), ['seat_ids' => [$second]])
            ->assertOk()
            ->assertJsonPath('members.0.name', 'Alex')
            ->assertJsonPath('members.0.me', false)
            ->assertJsonPath('members.0.picks', [$first])
            ->assertJsonPath('members.1.me', true);
    }

    public function test_a_pick_reserves_nothing()
    {
        $concert = $this->concert();
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $seatIds = $this->seatIds($concert);
        $party = app(SeatPartyService::class)->start($owner, $concert);

        app(SeatPartyService::class)->sync($party, $owner, $seatIds);

        $this->assertSame(0, Seat::whereIn('id', $seatIds)->whereNotNull('held_by')->count());
        app(SeatHoldService::class)->hold($stranger, $concert, $seatIds);
        $this->assertSame(2, Seat::whereIn('id', $seatIds)->where('held_by', $stranger->id)->count());
    }

    public function test_only_members_can_read_a_group()
    {
        $concert = $this->concert();
        $party = app(SeatPartyService::class)->start(User::factory()->create(), $concert);

        $this->actingAs(User::factory()->create())
            ->postJson(route('seats.party.sync', [$concert, $party->code]), ['seat_ids' => []])
            ->assertForbidden();
    }

    public function test_seats_from_another_concert_are_dropped()
    {
        $concert = $this->concert();
        $other = $this->concert('Other Night');
        $owner = User::factory()->create();
        $party = app(SeatPartyService::class)->start($owner, $concert);
        [$mine] = $this->seatIds($concert, 1);
        [$foreign] = $this->seatIds($other, 1);

        $state = app(SeatPartyService::class)->sync($party, $owner, [$mine, $foreign]);

        $this->assertSame([$mine], $state['members'][0]['picks']);
    }

    public function test_an_ended_link_sends_the_friend_back_to_the_map()
    {
        $concert = $this->concert();
        $party = app(SeatPartyService::class)->start(User::factory()->create(), $concert);
        $party->update(['expires_at' => now()->subMinute()]);

        $this->actingAs(User::factory()->create())
            ->get(route('seats.show', [$concert, 'party' => $party->code]))
            ->assertRedirect(route('seats.show', $concert))
            ->assertSessionHas('error');
    }

    public function test_a_group_has_a_size_limit()
    {
        $concert = $this->concert();
        $parties = app(SeatPartyService::class);
        $party = $parties->start(User::factory()->create(), $concert);

        for ($i = 1; $i < SeatPartyService::MAX_MEMBERS; $i++) {
            $parties->join(User::factory()->create(), $concert, $party->code);
        }

        $this->expectException(BookingException::class);
        $parties->join(User::factory()->create(), $concert, $party->code);
    }

    public function test_a_group_ends_when_its_last_member_leaves()
    {
        $concert = $this->concert();
        $owner = User::factory()->create();
        $party = app(SeatPartyService::class)->start($owner, $concert);

        $this->actingAs($owner)->deleteJson(route('seats.party.leave', [$concert, $party->code]))->assertOk();

        $this->assertSame(0, SeatParty::count());
    }

    public function test_sales_map_counts_confirmed_orders_only()
    {
        $concert = $this->concert();
        $seatIds = $this->seatIds($concert, 4);
        $block = Seat::find($seatIds[0])->block;
        $kept = array_values(array_filter($seatIds, function ($id) use ($block) {
            return Seat::find($id)->block === $block;
        }));

        $this->buy($concert, array_slice($kept, 0, 2));
        $cancelled = $this->buy($concert, array_slice($kept, 2, 2));
        $cancelled->update(['status' => Order::CANCELLED]);

        $row = collect(app(SalesMapService::class)->blocks($concert))->firstWhere('block', (string) $block);
        $price = (float) Seat::find($kept[0])->section->price;

        $this->assertSame(2, $row['sold']);
        $this->assertSame(2, $row['recent']);
        $this->assertSame($price * 2, $row['revenue']);
    }

    public function test_sales_are_for_admins_only()
    {
        $concert = $this->concert();
        $fan = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($fan)->getJson(route('admin.concerts.sales-map', $concert))->assertStatus(302);
        $this->actingAs($fan)->get(route('seats.show', $concert))->assertDontSee('data-mode="sales"', false);

        $this->actingAs($admin)->getJson(route('admin.concerts.sales-map', $concert))->assertOk()->assertJsonStructure(['blocks' => [['block', 'seats', 'sold', 'percent', 'revenue', 'recent']]]);
        $this->actingAs($admin)->get(route('seats.show', $concert))->assertSee('data-mode="sales"', false);
    }
}
