# onOffice Adapter for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/katalam/laravel-onoffice-adapter.svg?style=flat-square)](https://packagist.org/packages/katalam/laravel-onoffice-adapter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/katalam/laravel-onoffice-adapter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/katalam/laravel-onoffice-adapter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/katalam/laravel-onoffice-adapter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/katalam/laravel-onoffice-adapter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/katalam/laravel-onoffice-adapter.svg?style=flat-square)](https://packagist.org/packages/katalam/laravel-onoffice-adapter)

An onOffice adapter for Laravel

## Installation

You can install the package via composer:

```bash
composer require katalam/laravel-onoffice-adapter
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-onoffice-adapter-config"
```

This is the contents of the published config file:

```php
return [
    /**
     * The base URL of the OnOffice API.
     * Change that if you are using a different version of the API.
     */
    'base_url' => 'https://api.onoffice.de/api/stable/api.php',

    /**
     * The headers to be sent with the request.
     * Override this if you need to send additional headers.
     */
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],

    /**
     * The token and secret to be used for authentication with the OnOffice API.
     */
    'token' => env('ON_OFFICE_TOKEN', ''),
    'secret' => env('ON_OFFICE_SECRET', ''),
];
```

## Usage

```php
$estates = EstateRepository::query()
    ->select('Id')
    ->where('status', 1)
    ->where('kaufpreis', '<', 30_000)
    ->orderBy('kaufpreis')
    ->orderByDesc('warmmiete')
    ->get();

$success = MarketplaceRepository::query()
    ->unlockProvider($parameterCacheId, $extendedClaim);

$users = UserRepository::query()
    ->select([
        'Anrede',
        'Vorname',
        'Nachname',
        'Mobil',
    ])
    ->where('Nr', $this->userId)
    ->get();
```
```php
$tmpUploadId = FileRepository::upload()
    ->save(base64_encode($fileContent));

$success = FileRepository::upload()->link($tmpUploadId, [
    'module' => 'estate',
    'relatedRecordId' => '12345',
]);
```
### Usage in tests
```php
EstateRepository::fake([ // First request
    [ // First page of first request
        EstateFactory::make() // First record of first page of first request
            ->id(1)
            ->set('foo', 'bar'),
    ],
], [ // Second request
    [ // First page of second request
        EstateFactory::make() // First record of first page of second request
            ->id(2)
            ->set('foo', 'baz'),
        EstateFactory::make() // Second record of first page of second request
            ->id(3),
    ],
]);

// request as normal
$estates = EstateRepository::query()->get();

expect($estates)->toHaveCount(1)
    ->and($estates->first()['id'])->toBe(1);

$estates = EstateRepository::query()->get();

expect($estates)->toHaveCount(2)
    ->and($estates->first()['id'])->toBe(2)
    ->and($estates->last()['id'])->toBe(3);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Bruno Görß](https://github.com/Katalam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
