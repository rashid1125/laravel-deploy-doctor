<?php

namespace Rashid1125\DeployDoctor\Commands;

use Illuminate\Console\Command;

class DeployCheckCommand extends Command
{
    protected $signature = 'deploy:check';

    protected $description = 'Check common Laravel deployment requirements';

    public function handle(): int
    {
        $checks = [
            [
                'APP_KEY is configured',
                filled(config('app.key')) ? 'PASS' : 'FAIL',
            ],
            [
                'APP_DEBUG is disabled in production',
                app()->environment('production') && (bool) config('app.debug')
                    ? 'FAIL'
                    : 'PASS',
            ],
            [
                'storage directory is writable',
                is_writable(storage_path()) ? 'PASS' : 'FAIL',
            ],
            [
                'bootstrap/cache directory is writable',
                is_writable(base_path('bootstrap/cache')) ? 'PASS' : 'FAIL',
            ],
            [
                'public/storage symlink exists',
                is_link(public_path('storage')) ? 'PASS' : 'WARN',
            ],
        ];

        $failures = 0;

        foreach ($checks as [$label, $status]) {
            $this->line(sprintf('[%s] %s', $status, $label));

            if ($status === 'FAIL') {
                $failures++;
            }
        }

        if ($failures > 0) {
            $this->error("Deployment check found {$failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('Deployment checks passed. Review any warnings above.');

        return self::SUCCESS;
    }
}
