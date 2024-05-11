<?php

namespace Katalam\OnOfficeAdapter;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Katalam\OnOfficeAdapter\Commands\OnOfficeAdapterCommand;

class OnOfficeAdapterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-onoffice-adapter')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-onoffice-adapter_table')
            ->hasCommand(OnOfficeAdapterCommand::class);
    }
}
