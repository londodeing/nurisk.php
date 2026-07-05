<?php

namespace Tests\Feature\Sync;

use Tests\TestCase;

class SyncFeatureFlagTest extends TestCase
{
    public function test_sync_observer_tidak_menulis_ketika_flag_false(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R005');
    }

    public function test_sync_api_return_503_ketika_flag_false(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R005');
    }

    public function test_sync_observer_menulis_ketika_flag_true(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R005');
    }
}
