<?php

namespace Rashid1125\DeployDoctor\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rashid1125\DeployDoctor\DeployDoctorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [DeployDoctorServiceProvider::class];
    }
}
