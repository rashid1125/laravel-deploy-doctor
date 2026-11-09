<?php

namespace Rashid1125\DeployDoctor;

use Illuminate\Support\ServiceProvider;
use Rashid1125\DeployDoctor\Commands\DeployCheckCommand;

class DeployDoctorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DeployCheckCommand::class,
            ]);
        }
    }
}
