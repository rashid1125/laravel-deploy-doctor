<?php

namespace Rashid1125\DeployDoctor\Commands;

use Illuminate\Console\Command;

class DeployCheckCommand extends Command
{
    protected $signature = 'deploy:check
        {--json : Output machine-readable JSON}
        {--strict : Treat warnings as failures}';

    protected $description = 'Check common Laravel deployment requirements';

    public function handle(): int
    {
        $checks = $this->checks();
        $failures = count(array_filter($checks, static fn (array $check): bool => $check['status'] === 'FAIL'));
        $warnings = count(array_filter($checks, static fn (array $check): bool => $check['status'] === 'WARN'));
        $failed = $failures > 0 || ($this->option('strict') && $warnings > 0);

        if ($this->option('json')) {
            $this->line(json_encode([
                'status' => $failed ? 'FAIL' : 'PASS',
                'summary' => [
                    'passed' => count(array_filter($checks, static fn (array $check): bool => $check['status'] === 'PASS')),
                    'warnings' => $warnings,
                    'failures' => $failures,
                ],
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->table(
                ['Status', 'Check', 'Details', 'Recommendation'],
                array_map(static fn (array $check): array => [
                    $check['status'],
                    $check['name'],
                    $check['details'],
                    $check['recommendation'] ?? '—',
                ], $checks)
            );

            $summary = sprintf('%d passed, %d warning(s), %d failure(s).', count($checks) - $warnings - $failures, $warnings, $failures);
            $failed ? $this->error($summary) : $this->info($summary);

            if ($this->option('strict') && $warnings > 0 && $failures === 0) {
                $this->error('Strict mode treats warnings as failures.');
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @return array<int, array{name: string, status: string, details: string, recommendation: ?string}> */
    private function checks(): array
    {
        $production = app()->environment('production');
        $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);

        return [
            $this->check(
                'Application key',
                filled(config('app.key')) ? 'PASS' : 'FAIL',
                filled(config('app.key')) ? 'APP_KEY is configured (value hidden).' : 'APP_KEY is missing.',
                filled(config('app.key')) ? null : 'Run php artisan key:generate and configure APP_KEY securely.'
            ),
            $this->check(
                'Debug mode',
                $production && (bool) config('app.debug') ? 'FAIL' : 'PASS',
                $production && (bool) config('app.debug') ? 'Debug mode is enabled in production.' : 'Debug mode is safe for the current environment.',
                $production && (bool) config('app.debug') ? 'Set APP_DEBUG=false in production.' : null
            ),
            $this->check(
                'Application URL',
                $production && strtolower((string) $appUrlScheme) !== 'https' ? 'WARN' : 'PASS',
                $production && strtolower((string) $appUrlScheme) !== 'https' ? 'Production APP_URL does not use HTTPS.' : 'APP_URL scheme is acceptable for the current environment.',
                $production && strtolower((string) $appUrlScheme) !== 'https' ? 'Set APP_URL to the HTTPS URL used by your production site.' : null
            ),
            $this->directoryCheck('Storage directory', storage_path()),
            $this->directoryCheck('Bootstrap cache directory', base_path('bootstrap/cache')),
            $this->check(
                'Public storage link',
                is_link(public_path('storage')) ? 'PASS' : 'WARN',
                is_link(public_path('storage')) ? 'public/storage is a symlink.' : 'public/storage symlink was not found.',
                is_link(public_path('storage')) ? null : 'Run php artisan storage:link if this application serves public storage files.'
            ),
            $this->check(
                'Configuration cache',
                app()->configurationIsCached() ? 'PASS' : 'WARN',
                app()->configurationIsCached() ? 'Configuration is cached.' : 'Configuration is not cached.',
                app()->configurationIsCached() ? null : 'Consider running php artisan config:cache during deployment.'
            ),
            $this->check(
                'Public environment file',
                file_exists(public_path('.env')) ? 'FAIL' : 'PASS',
                file_exists(public_path('.env')) ? 'An .env file exists in the public directory.' : 'No .env file was found in the public directory.',
                file_exists(public_path('.env')) ? 'Remove public/.env and ensure secrets are not web-accessible.' : null
            ),
        ];
    }

    /** @return array{name: string, status: string, details: string, recommendation: ?string} */
    private function directoryCheck(string $name, string $path): array
    {
        if (! is_dir($path)) {
            return $this->check($name, 'FAIL', 'Directory does not exist.', 'Create the directory and ensure the deployment includes it.');
        }

        if (! is_writable($path)) {
            return $this->check($name, 'FAIL', 'Directory is not writable by the application process.', 'Adjust ownership or permissions for the web server user.');
        }

        return $this->check($name, 'PASS', 'Directory exists and is writable.');
    }

    /** @return array{name: string, status: string, details: string, recommendation: ?string} */
    private function check(string $name, string $status, string $details, ?string $recommendation = null): array
    {
        return compact('name', 'status', 'details', 'recommendation');
    }
}
