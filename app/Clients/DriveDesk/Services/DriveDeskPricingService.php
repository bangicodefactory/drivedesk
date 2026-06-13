<?php

namespace App\Clients\DriveDesk\Services;

use App\Services\DefaultPricingService;

class DriveDeskPricingService extends DefaultPricingService
{
    // DriveDesk uses the platform-default pricing. Override when its pricing
    // logic diverges from the core default.
}
