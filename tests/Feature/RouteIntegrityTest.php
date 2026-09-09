<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteIntegrityTest extends TestCase
{
    /**
     * Guards perf-audit F-22: two routes sharing a name make
     * `php artisan route:cache` throw, which breaks the production deploy
     * (deploy.yml caches routes under `set -e`). This is a copy-paste-prone
     * mistake (it's how F-22 happened), and CI doesn't run route:cache — so
     * assert the invariant here instead. No DB needed.
     */
    public function test_no_two_routes_share_a_name(): void
    {
        $duplicates = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->duplicates()
            ->unique()
            ->values();

        $this->assertTrue(
            $duplicates->isEmpty(),
            'Duplicate route names break `route:cache` (and the deploy): '
                .$duplicates->implode(', ')
        );
    }

    /**
     * BAN-334. `booking_payment` is now true for drivedesk, which changes what
     * a `feature:booking_payment` route would mean: the flag used to be off, so
     * such a route 404'd and a half-finished gateway could sit on main
     * harmlessly. With the flag on it is **live in production the moment it
     * merges** -- and a payment callback is by definition unauthenticated, has
     * to be excluded from CSRF, and must verify a hash before it believes
     * anything.
     *
     * So: no route may hide behind that flag until the callback is real. When
     * one is added deliberately, this test is the place to record that decision
     * -- delete the assertion in the same PR that ships the verification, never
     * before.
     *
     * Deliberately not a check for the *absence* of CMI code in general. The
     * intent value 'cmi' is legitimately accepted by
     * RequestBookingController::storeBooking today; what must not exist yet is
     * an endpoint that acts on it.
     */
    public function test_no_route_hides_behind_the_booking_payment_flag(): void
    {
        // Resolved through the router, not read off the route.
        // Route::gatherMiddleware() returns what the route was *declared* with,
        // so a group -- middlewareGroup('cmi', ['feature:booking_payment', ...])
        // -- comes back as the bare string "cmi" and an exact-match filter sails
        // past it. Verified: a probe route behind such a group passed this
        // assertion before this change. gatherRouteMiddleware() expands groups
        // the way the kernel does at request time.
        $router = app('router');

        $guarded = collect(Route::getRoutes()->getRoutes())
            ->filter(function ($route) use ($router) {
                foreach ($router->gatherRouteMiddleware($route) as $middleware) {
                    // Lowercased contains, not equality. gatherRouteMiddleware()
                    // resolves the `feature` alias to its class, so the entry
                    // arrives as "App\Http\Middleware\RequireFeature:booking_payment"
                    // -- a capital F that a case-sensitive match walks straight
                    // past, which is how the first version of this guard still
                    // let the group probe through.
                    if (is_string($middleware) && str_contains(strtolower($middleware), 'feature:booking_payment')) {
                        return true;
                    }
                }

                return false;
            })
            ->map(fn ($route) => $route->methods()[0].' /'.ltrim($route->uri(), '/'))
            ->values();

        $this->assertTrue(
            $guarded->isEmpty(),
            "booking_payment is on for at least one client, so these routes are live in production "
                ."with no gateway behind them: ".$guarded->implode(', ')
        );
    }
}
