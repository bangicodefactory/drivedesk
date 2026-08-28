<?php

namespace Tests\Concerns;

trait WithClient
{
    /**
     * Switch the active client for the rest of the test.
     *
     * Real clients resolve from config/clients/<client>.php. A name with no
     * committed config falls back to tests/Fixtures/clients/<client>.php, which
     * is how suites get a neutral tenant without borrowing a live client's flags.
     */
    protected function asClient(string $client): static
    {
        if (! config()->has("clients.{$client}")) {
            $fixture = base_path("tests/Fixtures/clients/{$client}.php");
            if (is_file($fixture)) {
                config(["clients.{$client}" => require $fixture]);
            }
        }

        config(['app.client' => $client]);

        // Re-run the ClientServiceProvider merge so config('client.*') reflects the new client.
        $this->app->register(\App\Providers\ClientServiceProvider::class, true);

        return $this;
    }
}
