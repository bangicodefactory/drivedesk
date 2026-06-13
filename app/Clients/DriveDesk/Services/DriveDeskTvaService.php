<?php

namespace App\Clients\DriveDesk\Services;

use App\Services\DefaultTvaService;

class DriveDeskTvaService extends DefaultTvaService
{
    // DriveDesk uses the platform-default TVA logic. Override when it diverges.
}
