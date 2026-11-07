<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentCardController;
use App\Http\Controllers\SeatMapController;
use App\Http\Controllers\SeatPartyController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\TicketCheckController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ConcertController::class, 'home'])->name('home');
Route::view('/credits', 'credits')->name('credits');

// Concert Routes (Public - List and Show)
Route::get('/concerts', [ConcertController::class, 'index'])->name('concerts.index');
Route::get('/concerts/{concert}', [ConcertController::class, 'show'])->whereNumber('concert')->name('concerts.show');
Route::get('/concerts/{concert}/pulse', [ConcertController::class, 'pulse'])->whereNumber('concert')->name('concerts.pulse');
Route::get('/concerts/{concert}/calendar.ics', [ConcertController::class, 'calendar'])->whereNumber('concert')->name('concerts.calendar');

// 3D Seat Picker (Authenticated users only)
Route::middleware(['auth', 'verified'])->prefix('concerts/{concert}')->name('seats.')->group(function () {
    Route::get('/seats', [SeatMapController::class, 'show'])->name('show');
    Route::get('/seats/data', [SeatMapController::class, 'data'])->name('data');
    Route::get('/seats/status', [SeatMapController::class, 'status'])->name('status');
    Route::post('/seats/hold', [SeatMapController::class, 'hold'])->name('hold');
    Route::delete('/seats/hold', [SeatMapController::class, 'release'])->name('release');
    Route::get('/checkout', [SeatMapController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [SeatMapController::class, 'purchase'])->name('purchase');
    Route::post('/checkout/promo', [SeatMapController::class, 'promo'])->name('promo');
    Route::post('/party', [SeatPartyController::class, 'start'])->name('party.start');
    Route::post('/party/{code}/sync', [SeatPartyController::class, 'sync'])->name('party.sync');
    Route::delete('/party/{code}', [SeatPartyController::class, 'leave'])->name('party.leave');
});

// Order Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/concerts/{concert}/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/concerts/{concert}/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/pass', [OrderController::class, 'pass'])->name('orders.pass');
    Route::post('/orders/{order}/transfer', [OrderController::class, 'transfer'])->name('orders.transfer');
});

// Review Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/concerts/{concert}/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/concerts/{concert}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// Profile Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/reviews', [ProfileController::class, 'reviews'])->name('profile.reviews');
});

// Favourite Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/concerts/{concert}/toggle-favourite', [FavouriteController::class, 'toggle'])->name('concerts.toggle-favourite');
    Route::get('/favourites', [FavouriteController::class, 'index'])->name('concerts.favourites');
});

// Payment Card Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/payment-cards', [PaymentCardController::class, 'index'])->name('payment_cards.index');
    Route::get('/payment-cards/create', [PaymentCardController::class, 'create'])->name('payment_cards.create');
    Route::post('/payment-cards', [PaymentCardController::class, 'store'])->name('payment_cards.store');
    Route::post('/payment-cards/ajax', [PaymentCardController::class, 'storeAjax'])->name('payment_cards.storeAjax');
    Route::delete('/payment-cards/{paymentCard}', [PaymentCardController::class, 'destroy'])->name('payment_cards.destroy');
});

// Post-login landing for anything that still links to the old dashboard
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->is_admin ? 'admin.dashboard' : 'profile.show');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes (Super Admin Only)
Route::middleware(['auth', 'verified', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Concerts Management
    Route::get('/concerts', [AdminController::class, 'concerts'])->name('concerts.index');
    Route::get('/concerts/create', [ConcertController::class, 'create'])->name('concerts.create');
    Route::post('/concerts', [ConcertController::class, 'store'])->name('concerts.store');
    Route::get('/concerts/{concert}', [AdminController::class, 'showConcert'])->name('concerts.show');
    Route::get('/concerts/{concert}/edit', [ConcertController::class, 'edit'])->name('concerts.edit');
    Route::get('/concerts/{concert}/sales-map', [AdminController::class, 'salesMap'])->name('concerts.sales-map');
    Route::put('/concerts/{concert}', [ConcertController::class, 'update'])->name('concerts.update');
    Route::delete('/concerts/{concert}', [AdminController::class, 'deleteConcert'])->name('concerts.delete');

    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');

    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');

    // Door
    Route::get('/check-in', [TicketCheckController::class, 'index'])->name('check-in');
    Route::post('/check-in', [TicketCheckController::class, 'store'])->name('check-in.store');

    // Promo Codes
    Route::get('/promos', [PromoCodeController::class, 'index'])->name('promos.index');
    Route::post('/promos', [PromoCodeController::class, 'store'])->name('promos.store');
    Route::put('/promos/{promo}/toggle', [PromoCodeController::class, 'toggle'])->name('promos.toggle');

    // Concert Reviews
    Route::get('/concert-reviews', [AdminController::class, 'concertReviews'])->name('concert_reviews');
});

require __DIR__.'/auth.php';
