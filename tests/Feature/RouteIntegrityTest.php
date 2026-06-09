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
}
