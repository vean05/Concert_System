<?php

namespace Tests\Feature;

use App\Http\Middleware\PublicDemo;
use App\Models\PaymentCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDemoTest extends TestCase
{
    use RefreshDatabase;

    private function card(string $number): array
    {
        return [
            'card_type' => 'visa',
            'card_number' => $number,
            'expiry_date' => '12/35',
            'cvv' => '123',
            'full_name' => 'Test Buyer',
            'country' => 'Malaysia',
            'address' => '1 Test Street',
        ];
    }

    public function test_the_admin_area_is_closed_in_public_demo_mode()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        config(['app.public_demo' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.promos.index'))->assertNotFound();
    }

    public function test_only_the_test_card_is_accepted_in_public_demo_mode()
    {
        config(['app.public_demo' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('payment_cards.store'), $this->card('5500000000000004'))
            ->assertSessionHasErrors('card_number');
        $this->assertSame(0, PaymentCard::count());

        $this->actingAs($user)
            ->post(route('payment_cards.store'), $this->card(PublicDemo::TEST_CARD))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, PaymentCard::count());
    }

    public function test_any_valid_card_is_accepted_outside_public_demo_mode()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('payment_cards.store'), $this->card('5500000000000004'))
            ->assertSessionHasNoErrors();
    }

    public function test_links_use_https_behind_the_local_tunnel()
    {
        $this->get(route('home'), ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertSee('https://localhost/css/theme.css', false);
    }

    public function test_the_banner_shows_only_in_public_demo_mode()
    {
        $this->get(route('home'))->assertDontSee('demo-note');

        config(['app.public_demo' => true]);

        $this->get(route('home'))->assertSee('demo-note')->assertSee('No real payments');
    }
}
