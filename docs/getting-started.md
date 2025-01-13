# Getting Started

Welcome to the **onOffice Adapter for Laravel**, a package that offers an Eloquent-like API for querying onOffice.

## Installation
```bash
composer require innobrain/laravel-onoffice-adapter
```

Then publish the config file:
```bash
php artisan vendor:publish --tag="laravel-onoffice-adapter-config"
```
This creates `config/onoffice.php`. Set up your credentials:
```php
return [
    'base_url' => 'https://api.onoffice.de/api/stable/api.php',
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
    'retry' => [
        'count' => 3,
        'delay' => 200,
        'only_on_connection_error' => true,
    ],
    'token' => env('ON_OFFICE_TOKEN', ''),
    'secret' => env('ON_OFFICE_SECRET', ''),
];
```

## Basic Usage
The adapter provides repositories for different onOffice resources (e.g., `EstateRepository`, `AddressRepository`). Here’s a quick example:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

$estates = EstateRepository::query()
    ->where('status', 1)
    ->where('kaufpreis', '<', 30000)
    ->orderBy('kaufpreis')
    ->orderByDesc('warmmiete')
    ->get();
```

## Specialized Operations
```php
// Unlock a provider
MarketplaceRepository::query()
    ->unlockProvider($parameterCacheId, $extendedClaim);

// Upload and link a file
FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```

## Debugging & Testing
For deeper insight:
```php
BaseRepository::query()->dd()->call(...);
// → Dump request and die
```
For test mocks, you can stub repository calls:
```php
EstateRepository::fake([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1)
    ])
]);

$estates = EstateRepository::query()->get();
expect($estates->count())->toBe(1);
```

::: tip
Check out [Testing](./testing/factories.md) for more on mocking and factories.
:::

Congratulations! You can now easily query onOffice using Laravel-style syntax. Refer to the [Repositories](./repositories/index.md) for deeper details.