# onOffice Adapter for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/innobrain/laravel-onoffice-adapter.svg?style=flat-square)](https://packagist.org/packages/innobrain/laravel-onoffice-adapter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/laravel-onoffice-adapter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/innobraingmbh/laravel-onoffice-adapter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/laravel-onoffice-adapter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/innobraingmbh/laravel-onoffice-adapter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/innobrain/laravel-onoffice-adapter.svg?style=flat-square)](https://packagist.org/packages/innobrain/laravel-onoffice-adapter)

An onOffice adapter for Laravel

## Installation

You can install the package via composer:

```bash
composer require innobrain/laravel-onoffice-adapter
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="onoffice-adapter-config"
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
     * Retry
     */
    'retry' => [
        'count' => 3,
        'delay' => 200,
        'only_on_connection_error' => true,
    ],

    /**
     * The token and secret to be used for authentication with the OnOffice API.
     */
    'token' => env('ON_OFFICE_TOKEN', ''),
    'secret' => env('ON_OFFICE_SECRET', ''),
];
```

## Usage

### Repositories
* ActivityRepository
* AddressRepository
* EstateRepository
* FieldRepository
* FileRepository
* MarketplaceRepository
* RelationRepository
* SearchCriteriaRepository
* SettingRepository

### Syntax for typical queries
```php
$estates = EstateRepository::query()
    ->select('Id')
    ->where('status', 1)
    ->where('kaufpreis', '<', 30_000)
    ->orderBy('kaufpreis')
    ->orderByDesc('warmmiete')
    ->get();

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

### Unusual queries
```php
$success = MarketplaceRepository::query()
    ->unlockProvider($parameterCacheId, $extendedClaim);
```
```php
$tmpUploadId = FileRepository::upload()
    ->save(base64_encode($fileContent));
$success = FileRepository::upload()->link($tmpUploadId, [
    'module' => 'estate',
    'relatedRecordId' => '12345',
]);

// or

$success = FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```
```php
ActivityRepository::query()
    ->addressIds($recordIds)
    ->estateId($estateId)
    ->create([
        'datetime' => $event->getDateFormatted(),
        'actionkind' => 'Newsletter',
        'actiontype' => 'Hard Bounce',
        'note' => $message,
    ]);
```

```php
Config::set('onoffice.token', 'token');
Config::set('onoffice.secret', 'secret');
Config::set('onoffice.api_claim', 'api_claim');
```

### Middlewares
```php
use Illuminate\Support\Facades\Log;
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::query()
    ->before(static function (OnOfficeRequest $request) {
        Log::info('About to send request', [
            'request' => $request->toArray(),
        ]);
    })
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));
```

### Debugging
```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::query()
    ->dd()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));
```
```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::record();

BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

$result = BaseRepository::lastRecorded();

/*
    $result = [
        OnOfficeRequest,
        OnOfficeResponse,
    ];
*/
```

### Default Fields
Sometimes, it can be useful to have default fields for your queries
to quickly glance at data. You can find a selection of default fields
in the OnOfficeService.

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

$estates = EstateRepository::query()
    ->select(OnOfficeService::DEFAULT_ESTATE_INFO_FIELDS)
    ->get();
```

### Helpers
When using default fields, you might find it helpful to hide all
empty fields. To do so, use the `clean_elements` helper.
It will work with both `find` and `get` responses.

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
 
$estates = EstateRepository::query()
    ->select(OnOfficeService::DEFAULT_ESTATE_INFO_FIELDS)
    ->get();
    
// will leave out fields with empty values like "", "0.00", [], or null.
$estates = clean_elements($estates);
```

### Usage in tests
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()
            ->id(1),
    ]),
]));

$response = EstateRepository::query()->get();

expect($response->count())->toBe(1)
    ->and($response->first()['id'])->toBe(1);

EstateRepository::assertSentCount(1);
```
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()
            ->id(1),
    ]),
    EstateRepository::page(recordFactories: [
        EstateFactory::make()
            ->id(2),
    ]),
]));

$response = EstateRepository::query()->get();

expect($response->count())->toBe(2)
    ->and($response->first()['id'])->toBe(1)
    ->and($response->last()['id'])->toBe(2);

EstateRepository::assertSentCount(2);
```
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::preventStrayRequests();
EstateRepository::fake([
    EstateRepository::response([
        EstateRepository::page(recordFactories: [
            EstateFactory::make()
                ->id(1),
        ]),
        EstateRepository::page(recordFactories: [
            EstateFactory::make()
                ->id(2),
        ]),
    ]),
    EstateRepository::response([
        EstateRepository::page(recordFactories: [
            EstateFactory::make()
                ->id(3),
        ]),
        EstateRepository::page(recordFactories: [
            EstateFactory::make()
                ->id(4),
        ]),
    ]),
]);

$response = EstateRepository::query()->get();

expect($response->count())->toBe(2)
    ->and($response->first()['id'])->toBe(1)
    ->and($response->last()['id'])->toBe(2);

$response = EstateRepository::query()->get();

expect($response->count())->toBe(2)
    ->and($response->first()['id'])->toBe(3)
    ->and($response->last()['id'])->toBe(4);

EstateRepository::assertSentCount(4);

$response = EstateRepository::query()->get(); // throws StrayRequestException
```
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::preventStrayRequests();
EstateRepository::fake(EstateRepository::sequence(
    EstateRepository::response([
        EstateRepository::page(recordFactories: EstateFactory::times(20)),
    ]),
    times: 30,
));

for ($i = 0; $i < 30; ++$i) {
    $response = EstateRepository::query()->get();

    expect($response->count())->toBe(20);
}
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

- [Bruno Görß](https://github.com/Innobrain)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
