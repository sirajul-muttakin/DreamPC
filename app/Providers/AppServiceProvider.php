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
        \Illuminate\Support\Facades\View::composer(['layouts.navigation', 'layouts.app'], function ($view) {
            try {
                $cart = app(\App\Services\CartService::class)->getOrCreateCart();
                $cartCount = $cart->items()->sum('quantity');
                $view->with('navCartCount', $cartCount);
            } catch (\Throwable $e) {
                $view->with('navCartCount', 0);
            }
        });
    }
}
