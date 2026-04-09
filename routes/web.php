<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\AdminController;

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

// Home page with concert list
Route::get('/', function () {
    $concerts = \App\Models\Concert::with('creator')->paginate(12);
    return view('home', compact('concerts'));
})->name('home');

// Concert Routes (Public - List and Show)
Route::get('/concerts', [ConcertController::class, 'index'])->name('concerts.index');
Route::get('/concerts/search', [ConcertController::class, 'search'])->name('concerts.search');
Route::get('/concerts/filter', [ConcertController::class, 'filter'])->name('concerts.filter');
Route::get('/concerts/{concert}', [ConcertController::class, 'show'])->name('concerts.show');

// Admin Concert Management Routes (Create, Edit, Delete)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/concerts', [ConcertController::class, 'store'])->name('concerts.store');
    Route::get('/concerts/{concert}/edit', [ConcertController::class, 'edit'])->name('concerts.edit');
    Route::put('/concerts/{concert}', [ConcertController::class, 'update'])->name('concerts.update');
    Route::delete('/concerts/{concert}', [ConcertController::class, 'destroy'])->name('concerts.destroy');
    Route::get('/concerts/create', [ConcertController::class, 'create'])->name('concerts.create');
});

// Order Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/concerts/{concert}/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/concerts/{concert}/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
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
    Route::get('/profile/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    Route::get('/profile/reviews', [ProfileController::class, 'reviews'])->name('profile.reviews');
});

// Favourite Routes (Authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/concerts/{concert}/toggle-favourite', [FavouriteController::class, 'toggle'])->name('concerts.toggle-favourite');
    Route::get('/favourites', [FavouriteController::class, 'getFavourites'])->name('concerts.favourites');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes (Super Admin Only)
Route::middleware(['auth', 'verified', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Concerts Management
    Route::get('/concerts', [AdminController::class, 'concerts'])->name('concerts.index');
    Route::get('/concerts/{concert}', [AdminController::class, 'showConcert'])->name('concerts.show');
    Route::delete('/concerts/{concert}', [AdminController::class, 'deleteConcert'])->name('concerts.delete');
    
    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    
    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
});

require __DIR__.'/auth.php';
