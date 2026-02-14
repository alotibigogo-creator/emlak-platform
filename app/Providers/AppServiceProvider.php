<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local') && !app()->runningInConsole()) {
            set_time_limit(120);
        }

        // تسجيل الـ Observers
        \App\Models\Contract::observe(\App\Observers\ContractObserver::class);
    }
}
