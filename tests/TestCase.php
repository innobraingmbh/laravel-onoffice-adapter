<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Innobrain\OnOfficeAdapter\OnOfficeAdapterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Innobrain\\OnOfficeAdapter\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            OnOfficeAdapterServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-onoffice-adapter_table.php.stub';
        $migration->up();
        */
    }
}
