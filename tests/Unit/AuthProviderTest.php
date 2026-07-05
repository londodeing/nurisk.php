<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuthUser;

class AuthProviderTest extends TestCase
{
    /**
     * Test bahwa provider user di config/auth.php telah diarahkan ke AuthUser model.
     */
    public function test_auth_provider_is_configured_correctly(): void
    {
        $configuredModel = config('auth.providers.users.model');

        $this->assertEquals(AuthUser::class, $configuredModel);
    }
}
