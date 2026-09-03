<?php

namespace App\Clients\MarrueCar\Services;

use App\Services\DefaultPricingService;

class MarrueCarPricingService extends DefaultPricingService
{
    // MarrueCar uses the platform-default pricing. Override when its pricing
    // logic diverges from the core default.
}
