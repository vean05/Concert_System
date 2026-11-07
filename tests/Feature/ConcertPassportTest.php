<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\User;
use App\Services\ConcertPassportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertPassportTest extends TestCase
{
    use RefreshDatabase;

    private function concert(string $artist, string $venue, string $date): Concert
    {
        return Concert::create([
            'title' => "{$artist} Live",
            'artist' => $artist,
            'venue' => $venue,
            'date' => $date,
            'description' => 'A test show.',
            'ticket_price' => 50,
            'total_ticket' => 100,
            'is_published' => true,
            'created_by' => User::factory()->create(['is_admin' => true])->id,
        ]);
    }

    private function order(User $user, Concert $concert, int $quantity, string $status = Order::CONFIRMED): Order
    {
        $order = new Order(['user_id' => $user->id, 'concert_id' => $concert->id, 'quantity' => $quantity, 'total_price' => 50 * $quantity]);
        $order->status = $status;
        $order->save();

        return $order;
    }

    public function test_passport_counts_confirmed_shows_only()
    {
        $user = User::factory()->create();
        $past = $this->concert('Old Band', 'Hall One', now()->subMonth()->toDateString());
        $soon = $this->concert('Next Band', 'Hall One', now()->addDays(3)->toDateString());
        $later = $this->concert('Late Band', 'Hall Two', now()->addMonths(2)->toDateString());
        $cancelled = $this->concert('Gone Band', 'Hall Three', now()->addMonth()->toDateString());

        $this->order($user, $past, 2);
        $this->order($user, $soon, 1);
        $this->order($user, $soon, 1);
        $this->order($user, $later, 1);
        $this->order($user, $cancelled, 2, Order::CANCELLED);

        $passport = app(ConcertPassportService::class)->for($user);

        $this->assertSame(3, $passport['shows']);
        $this->assertSame(3, $passport['artists']);
        $this->assertSame(2, $passport['venues']);
        $this->assertSame($soon->id, $passport['next']['concert']->id);
        $this->assertSame(2, $passport['next']['tickets']);
    }

    public function test_profile_shows_the_passport_only_after_a_booking()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.show'))->assertOk()->assertDontSee('Concert passport');

        $this->order($user, $this->concert('Next Band', 'Hall One', now()->addDays(3)->toDateString()), 1);

        $this->actingAs($user)->get(route('profile.show'))->assertOk()->assertSee('Concert passport')->assertSee('until Next Band');
    }
}
