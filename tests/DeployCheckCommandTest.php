<?php

namespace Rashid1125\DeployDoctor\Tests;

class DeployCheckCommandTest extends TestCase
{
    public function test_it_reports_a_missing_app_key_as_a_failure(): void
    {
        config(['app.key' => null]);

        $this->artisan('deploy:check')
            ->expectsOutput('[FAIL] APP_KEY is configured')
            ->assertExitCode(1);
    }

    public function test_it_passes_when_app_key_is_configured_outside_production(): void
    {
        config([
            'app.key' => 'base64:test-key',
            'app.env' => 'testing',
            'app.debug' => true,
        ]);

        $this->artisan('deploy:check')
            ->expectsOutput('[PASS] APP_KEY is configured')
            ->expectsOutput('[PASS] APP_DEBUG is disabled in production')
            ->assertExitCode(0);
    }

    public function test_it_fails_when_debug_is_enabled_in_production(): void
    {
        config([
            'app.key' => 'base64:test-key',
            'app.env' => 'production',
            'app.debug' => true,
        ]);

        $this->artisan('deploy:check')
            ->expectsOutput('[FAIL] APP_DEBUG is disabled in production')
            ->assertExitCode(1);
    }
}
