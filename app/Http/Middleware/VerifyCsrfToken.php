<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // store-signature now posts via Inertia (CSRF token sent automatically);
        // update-/delete-signature routes are commented out in web.php. No
        // endpoint needs a CSRF exemption.
    ];
}
