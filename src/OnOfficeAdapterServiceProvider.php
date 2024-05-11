<?php

namespace Katalam\OnOfficeAdapter;

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
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
            ->hasConfigFile('onoffice')
            ->hasViews()
            ->hasCommand(OnOfficeAdapterCommand::class);
    }


    public function bootingPackage(): void
    {
        Http::macro('onOffice', function () {
            return Http::withHeaders(config('onoffice.headers'))
                ->baseUrl(config('onoffice.base_url'));
        });

        $this->app->scoped(OnOfficeService::class, function () {
            return new OnOfficeService();
        });
    }
}
