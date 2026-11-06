<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Concert cards show a filled heart for the viewer's favourites.
        View::composer(['home', 'concerts.index', 'concerts.show', 'concerts.favourites'], function ($view) {
            $view->with('favouriteIds', auth()->check()
                ? auth()->user()->favouriteConcerts()->pluck('concerts.id')->all()
                : []);
        });
    }
}
