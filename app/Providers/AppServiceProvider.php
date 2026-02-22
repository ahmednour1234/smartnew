<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // Override Livewire's update endpoint to always use /public prefix
        Livewire::setUpdateRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::post('/public/livewire/update', $handle)
                ->middleware(['web']);
        });

        // Override Livewire's script route to use /public prefix
        Livewire::setScriptRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::get('/public/livewire/livewire.js', $handle)
                ->middleware(['web']);
        });
    }
}
