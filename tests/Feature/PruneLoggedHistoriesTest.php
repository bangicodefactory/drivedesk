<?php

namespace Tests\Feature;

use App\Models\LoggedHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneLoggedHistoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_deletes_rows_older_than_retention_and_keeps_recent(): void
    {
        config(['audit.logged_history_retention_days' => 180]);

        $old    = LoggedHistory::factory()->create(['created_at' => now()->subDays(200)]);
        $recent = LoggedHistory::factory()->create(['created_at' => now()->subDays(30)]);

        $this->artisan('model:prune', ['--model' => [LoggedHistory::class]])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('logged_histories', ['id' => $old->id]);
        $this->assertDatabaseHas('logged_histories', ['id' => $recent->id]);
    }

    public function test_prune_respects_configured_retention_window(): void
    {
        // A 7-day window prunes a 10-day-old row but keeps a 5-day-old one.
        config(['audit.logged_history_retention_days' => 7]);

        $stale = LoggedHistory::factory()->create(['created_at' => now()->subDays(10)]);
        $fresh = LoggedHistory::factory()->create(['created_at' => now()->subDays(5)]);

        $this->artisan('model:prune', ['--model' => [LoggedHistory::class]])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('logged_histories', ['id' => $stale->id]);
        $this->assertDatabaseHas('logged_histories', ['id' => $fresh->id]);
    }
}
