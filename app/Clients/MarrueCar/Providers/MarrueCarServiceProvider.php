<?php

namespace App\Clients\MarrueCar\Providers;

use App\Clients\MarrueCar\Services\MarrueCarPricingService;
use App\Clients\MarrueCar\Services\MarrueCarTvaService;
use App\Contracts\PricingServiceContract;
use App\Contracts\TvaServiceContract;
use Illuminate\Support\ServiceProvider;

class MarrueCarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ClientServiceProvider already binds these via the config 'bindings'
        // array; re-binding here gives this client a single home for future
        // boot() logic. The config bindings act as a safety net.
        $this->app->bind(PricingServiceContract::class, MarrueCarPricingService::class);
        $this->app->bind(TvaServiceContract::class, MarrueCarTvaService::class);
    }
}
