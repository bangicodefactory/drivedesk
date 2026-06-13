<?php

namespace App\Clients\DriveDesk\Providers;

use App\Clients\DriveDesk\Services\DriveDeskPricingService;
use App\Clients\DriveDesk\Services\DriveDeskTvaService;
use App\Contracts\PricingServiceContract;
use App\Contracts\TvaServiceContract;
use Illuminate\Support\ServiceProvider;

class DriveDeskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ClientServiceProvider already binds these via the config 'bindings'
        // array; re-binding here gives this client a single home for future
        // boot() logic. The config bindings act as a safety net.
        $this->app->bind(PricingServiceContract::class, DriveDeskPricingService::class);
        $this->app->bind(TvaServiceContract::class, DriveDeskTvaService::class);
    }
}
