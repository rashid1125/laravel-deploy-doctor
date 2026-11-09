<?php

namespace Rashid1125\DeployDoctor\Tests;

class DeployCheckCommandTest extends TestCase
{
    public function test_it_reports_a_missing_app_key_as_a_failure(): void
    {
        config(['app.key' => null]);

        $this->artisan('deploy:check')
            ->expectsOutputToContain('Application key')
            ->expectsOutputToContain('APP_KEY is missing.')
            ->assertExitCode(1);
    }

    public function test_it_passes_when_app_key_is_configured_outside_production(): void
    {
        config([
            'app.key' => 'base64:test-secret-that-must-not-be-printed',
            'app.env' => 'testing',
            'app.debug' => true,
            'app.url' => 'http://localhost',
        ]);

        $this->artisan('deploy:check')
            ->expectsOutputToContain('APP_KEY is configured (value hidden).')
            ->assertExitCode(0);
    }

    public function test_it_fails_when_debug_is_enabled_in_production(): void
    {
        config([
            'app.key' => 'base64:test-key',
            'app.env' => 'production',
            'app.debug' => true,
            'app.url' => 'https://example.test',
        ]);

        $this->artisan('deploy:check')
            ->expectsOutputToContain('Debug mode is enabled in production.')
            ->assertExitCode(1);
    }

    public function test_strict_mode_treats_warnings_as_failures(): void
    {
        config([
            'app.key' => 'base64:test-key',
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'http://example.test',
        ]);

        $this->artisan('deploy:check', ['--strict' => true])
            ->assertExitCode(1);
    }

    public function test_json_mode_outputs_a_structured_report(): void
    {
        config([
            'app.key' => 'base64:do-not-leak-this-value',
            'app.env' => 'testing',
            'app.debug' => false,
        ]);

        $this->artisan('deploy:check', ['--json' => true])
            ->expectsOutputToContain('"checks"')
            ->expectsOutputToContain('"Application key"')
            ->assertExitCode(0);
    }
}
