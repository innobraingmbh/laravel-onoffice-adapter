<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter;

use Illuminate\Support\Facades\Http;
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
        Http::macro('onOffice', fn () => Http::withHeaders(config('onoffice.headers'))
            ->baseUrl(config('onoffice.base_url')));

        $this->app->scoped(OnOfficeService::class, fn () => new OnOfficeService);
    }
}
