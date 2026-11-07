<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;

abstract class TestCase extends TestbenchTestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mail.default', 'array');
        $app['view']->addNamespace('tests', __DIR__.'/views');
    }
}
