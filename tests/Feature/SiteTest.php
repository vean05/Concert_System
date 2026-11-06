<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Services\BookingService;
use App\Services\SeatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    private function concert(User $admin, array $attributes = []): Concert
    {
        $concert = Concert::create($attributes + [
            'title' => 'Test Tour',
            'artist' => 'Test Artist',
            'venue' => 'Test Arena',
            'date' => now()->addMonth()->toDateString(),
            'description' => 'A show.',
            'ticket_price' => 0,
            'total_ticket' => 0,
            'created_by' => $admin->id,
        ]);

        app(SeatingService::class)->sync($concert, [
            ['name' => 'VIP', 'type' => 'seated', 'price' => 300, 'capacity' => 20],
            ['name' => 'Floor', 'type' => 'standing', 'price' => 100, 'capacity' => 50],
        ]);

        return $concert->fresh();
    }

    private function customerWithOrder(Concert $concert): array
    {
        $user = User::factory()->create();
        $card = $user->paymentCards()->create([
            'card_type' => 'visa', 'card_number' => '4111111111111111', 'expiry_date' => '12/40', 'cvv' => '123',
            'full_name' => $user->name, 'country' => 'Malaysia', 'address' => '1 Test Street',
        ]);
        $order = app(BookingService::class)->place($user, $concert, [$concert->sections()->first()->id => 2], $card);

        return [$user, $order];
    }

    public function test_every_public_and_account_page_renders()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $concert = $this->concert($admin);
        $past = $this->concert($admin, ['title' => 'Old Show', 'date' => now()->subMonth()->toDateString()]);
        [$user, $order] = $this->customerWithOrder($concert);
        $user->favouriteConcerts()->attach([$concert->id, $past->id]);
        $review = Review::create(['user_id' => $user->id, 'concert_id' => $concert->id, 'rating' => 5, 'comment' => 'Great']);

        foreach (['/', '/concerts', '/concerts?q=test&venue=arena&date=' . now()->toDateString(), "/concerts/{$concert->id}", "/concerts/{$past->id}", '/login', '/register', '/forgot-password', '/reset-password/token'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs($user);
        foreach (['/', '/orders', "/orders/{$order->id}", "/concerts/{$concert->id}", "/concerts/{$concert->id}/orders/create", '/favourites', '/payment-cards', '/payment-cards/create', '/profile', '/profile/edit', '/profile/reviews', "/reviews/{$review->id}/edit"] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs($admin);
        foreach (['/admin', '/admin/concerts', '/admin/concerts?search=test&sort_by=title&sort_order=desc', '/admin/concerts/create', "/admin/concerts/{$concert->id}", "/admin/concerts/{$concert->id}/edit", '/admin/users', '/admin/users?search=a', "/admin/users/{$user->id}", '/admin/analytics', '/admin/concert-reviews', '/profile/edit'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_browse_filters_and_upcoming_concerts_come_first()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->concert($admin, ['title' => 'Past Night', 'artist' => 'Old Band', 'date' => now()->subWeek()->toDateString()]);
        $this->concert($admin, ['title' => 'Later Night', 'artist' => 'Late Band', 'date' => now()->addMonths(2)->toDateString()]);
        $this->concert($admin, ['title' => 'Soon Night', 'artist' => 'Soon Band', 'venue' => 'Blue Hall', 'date' => now()->addDay()->toDateString()]);

        $this->get('/concerts')->assertSeeInOrder(['Soon Night', 'Later Night'])->assertDontSee('Past Night');
        $this->get('/concerts?when=all')->assertSeeInOrder(['Soon Night', 'Later Night', 'Past Night']);
        $this->get('/concerts?q=old')->assertSee('Past Night');
        $this->get('/concerts?q=late')->assertSee('Later Night')->assertDontSee('Soon Night');
        $this->get('/concerts?venue=blue')->assertSee('Soon Night')->assertDontSee('Later Night');
    }

    public function test_admin_can_create_and_edit_a_concert_with_ticket_types()
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.concerts.store'), [
            'title' => 'New Show', 'artist' => 'Band', 'venue' => 'Hall', 'description' => 'Loud.',
            'date' => now()->addWeek()->toDateString(),
            'image' => UploadedFile::fake()->image('poster.jpg'),
            'sections' => [
                ['name' => 'Seats', 'type' => 'seated', 'price' => 50, 'capacity' => 40],
                ['name' => 'Pit', 'type' => 'standing', 'price' => 30, 'capacity' => 100],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $concert = Concert::with('sections')->first();
        $this->assertSame(140, $concert->total_ticket);
        $this->assertEquals(30, $concert->ticket_price);
        Storage::disk('public')->assertExists($concert->image_path);

        // A past concert stays editable while its date is unchanged.
        $concert->update(['date' => now()->subDay()->toDateString()]);
        $this->actingAs($admin)->put(route('admin.concerts.update', $concert), [
            'title' => 'Renamed', 'artist' => 'Band', 'venue' => 'Hall', 'description' => 'Loud.',
            'date' => $concert->fresh()->date->toDateString(),
            'sections' => $concert->sections->map->only(['id', 'name', 'type', 'price', 'capacity'])->all(),
        ])->assertSessionHasNoErrors();

        $this->assertSame('Renamed', $concert->fresh()->title);
    }

    public function test_any_admin_can_delete_a_concert_and_its_orders_go_with_it()
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);
        $concert = $this->concert($creator);
        $this->customerWithOrder($concert);

        $this->actingAs($otherAdmin)->delete(route('admin.concerts.delete', $concert))->assertRedirect();

        $this->assertSame(0, Concert::count());
        $this->assertSame(0, Order::count());
    }

    public function test_customers_cannot_reach_admin_or_manage_concerts()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $concert = $this->concert($admin);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertRedirect('/');
        $this->actingAs($user)->get('/admin/concerts/create')->assertRedirect('/');
        $this->actingAs($user)->delete(route('admin.concerts.delete', $concert))->assertRedirect('/');
        $this->assertSame(1, Concert::count());
    }

    public function test_unsafe_admin_sort_column_is_ignored()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->concert($admin);

        $this->actingAs($admin)->get('/admin/concerts?sort_by=password,(select+1)&sort_order=nope')->assertOk();
    }

    public function test_buyers_get_one_review_per_concert()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $concert = $this->concert($admin);
        [$user] = $this->customerWithOrder($concert);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('reviews.create', $concert))->assertRedirect(route('concerts.show', $concert));
        $this->actingAs($stranger)->post(route('reviews.store', $concert), ['rating' => 5, 'comment' => 'Hi'])->assertSessionHas('error');

        $this->actingAs($user)->post(route('reviews.store', $concert), ['rating' => 5, 'comment' => 'Hi'])->assertSessionHas('success');
        $review = Review::first();
        $this->actingAs($user)->post(route('reviews.store', $concert), ['rating' => 1, 'comment' => 'Again'])->assertRedirect(route('reviews.edit', $review));

        $this->assertSame(1, Review::count());
    }

    public function test_favourites_toggle()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $concert = $this->concert($admin);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('concerts.toggle-favourite', $concert))->assertJson(['status' => 'favourited']);
        $this->assertTrue($user->hasFavourited($concert->id));
        $this->actingAs($user)->postJson(route('concerts.toggle-favourite', $concert))->assertJson(['status' => 'unfavourited']);
        $this->assertFalse($user->hasFavourited($concert->id));
    }

    public function test_payment_card_rules()
    {
        $user = User::factory()->create();
        $card = ['card_type' => 'visa', 'card_number' => '4111111111111111', 'expiry_date' => '12/40', 'cvv' => '123',
            'full_name' => 'A', 'country' => 'Malaysia', 'address' => 'Street'];

        $this->actingAs($user)->post('/payment-cards', ['card_number' => '12'] + $card)->assertSessionHasErrors('card_number');
        $this->actingAs($user)->post('/payment-cards', ['expiry_date' => '01/20'] + $card)->assertSessionHasErrors('expiry_date');
        $this->actingAs($user)->post('/payment-cards', ['cvv' => '1'] + $card)->assertSessionHasErrors('cvv');
        $this->assertSame(0, $user->paymentCards()->count());

        $this->actingAs($user)->post('/payment-cards', $card)->assertSessionHasNoErrors();
        $saved = $user->paymentCards()->first();
        $this->assertSame('1111', $saved->last_four);

        $this->actingAs(User::factory()->create())->delete("/payment-cards/{$saved->id}")->assertForbidden();
    }

    public function test_changing_password_requires_the_current_one()
    {
        $user = User::factory()->create();
        $base = ['name' => $user->name, 'email' => $user->email];

        $this->actingAs($user)->put('/profile', $base + ['password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/profile', $base + ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHasNoErrors();
    }

    public function test_concert_page_shows_the_artists_photo_songs_and_video()
    {
        config(['media.artists.Test Artist' => [
            'photo' => 'images/artists/test.jpg',
            'credit' => ['author' => 'A Photographer', 'licence' => 'CC BY 4.0', 'source' => 'https://example.test/photo'],
            'songs' => [['title' => 'Test Song', 'album' => 'Test Album', 'preview' => 'https://example.test/song.m4a', 'artwork' => 'https://example.test/art.jpg', 'link' => 'https://example.test/song']],
            'video' => ['title' => 'Test Video', 'url' => 'https://example.test/video.m4v', 'link' => 'https://example.test/video', 'poster' => 'https://example.test/poster.jpg'],
        ]]);
        $concert = $this->concert(User::factory()->create(['is_admin' => true]));

        $this->assertSame(asset('images/artists/test.jpg'), $concert->photo_url);

        $this->get(route('concerts.show', $concert))
            ->assertOk()
            ->assertSee('images/artists/test.jpg')
            ->assertSee('Test Song')
            ->assertSee('https://example.test/song.m4a')
            ->assertSee('https://example.test/video.m4v')
            ->assertSee('A Photographer');
    }

    public function test_uploaded_poster_wins_on_cards_but_not_on_page_backgrounds()
    {
        config(['media.artists.Test Artist.photo' => 'images/artists/test.jpg']);
        $concert = $this->concert(User::factory()->create(['is_admin' => true]), ['image_path' => 'concerts/poster.jpg']);

        $this->assertSame(asset('storage/concerts/poster.jpg'), $concert->photo_url);
        $this->assertSame(asset('images/artists/test.jpg'), $concert->hero_url);
    }

    public function test_concert_without_media_still_renders()
    {
        $concert = $this->concert(User::factory()->create(['is_admin' => true]));

        $this->assertNull($concert->photo_url);
        $this->get(route('concerts.show', $concert))->assertOk()->assertDontSee('Top songs');
        $this->get('/')->assertOk();
    }
}
