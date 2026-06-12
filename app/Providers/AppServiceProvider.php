<?php

namespace App\Providers;

use App\Contracts\PricingServiceContract;
use App\Contracts\TvaServiceContract;
use App\Services\DefaultPricingService;
use App\Services\DefaultTvaService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Fallback bindings — overridden by ClientServiceProvider when a
        // client config supplies its own concrete class.
        $this->app->bind(PricingServiceContract::class, DefaultPricingService::class);
        $this->app->bind(TvaServiceContract::class, DefaultTvaService::class);

        // Telescope is a require-dev package: production installs run
        // composer --no-dev, so its classes don't exist there and nothing
        // may reference them unconditionally (config/app.php used to, which
        // broke `artisan package:discover` on the production host).
        if ($this->app->environment('local', 'testing')
            && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
