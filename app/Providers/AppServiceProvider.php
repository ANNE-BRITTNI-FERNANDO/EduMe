<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Product;
use App\Models\Bundle;

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
        Relation::morphMap([
            'product' => Product::class,
            'bundle' => Bundle::class,
        ]);

        // Set default timezone for Carbon
        date_default_timezone_set('Asia/Colombo');
        
        // Format timestamps in Sri Lankan time
        \Illuminate\Support\Facades\Date::macro('sriLankaFormat', function () {
            return $this->setTimezone('Asia/Colombo')->format('Y-m-d h:i A');
        });
    }
}
