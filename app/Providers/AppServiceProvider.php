<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Str;

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
        // Modify Livewire endpoint URL to include /public prefix
        if (request()->is('public/*') || Str::startsWith(request()->path(), 'public/')) {
            // Override Livewire's update endpoint
            Livewire::setUpdateRoute(function ($handle) {
                return \Illuminate\Support\Facades\Route::post('/public/livewire/update', $handle)
                    ->middleware(['web']);
            });
        }
    }
}
