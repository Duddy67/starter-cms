<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cookie;

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
        // Set variables related to the cookie info dialog (gdpr).
        $cookieInfoConfig = config('cookie-info');
        $cookieInfoAlreadyChecked = Cookie::has($cookieInfoConfig['cookie_name']);

        view()->share(compact('cookieInfoAlreadyChecked', 'cookieInfoConfig'));
    }
}
