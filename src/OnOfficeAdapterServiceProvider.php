<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter;

use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasConfigFile('onoffice');
    }

    public function bootingPackage(): void
    {
        $this->app->scoped(OnOfficeService::class, fn () => new OnOfficeService);
    }
}
