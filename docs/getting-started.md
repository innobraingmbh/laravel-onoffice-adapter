# Getting Started with onOffice Adapter for Laravel

This guide will help you get started with the onOffice Adapter for Laravel, a package that provides an easy-to-use interface for interacting with the onOffice API.

## Installation

To install the package, run the following command in your Laravel project:

```bash
composer require innobrain/laravel-onoffice-adapter
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-onoffice-adapter-config"
```

This will create a `config/onoffice.php` file in your project. Open this file and configure your onOffice API credentials:

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

Make sure to set your `ON_OFFICE_TOKEN` and `ON_OFFICE_SECRET` in your `.env` file.

## Basic Usage

The package provides several repositories for interacting with different aspects of the onOffice API:

- ActivityRepository
- AddressRepository
- EstateRepository
- FieldRepository
- FileRepository
- MarketplaceRepository
- RelationRepository
- SearchCriteriaRepository
- SettingRepository

### Querying Data

Here's an example of how to query estates:

```php
$estates = EstateRepository::query()
    ->select('Id')
    ->where('status', 1)
    ->where('kaufpreis', '<', 30_000)
    ->orderBy('kaufpreis')
    ->orderByDesc('warmmiete')
    ->get();
```

And here's how to query users:

```php
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

### Specialized Operations

The package also supports more specialized operations:

1. Unlocking a provider:

```php
$success = MarketplaceRepository::query()
    ->unlockProvider($parameterCacheId, $extendedClaim);
```

2. Uploading and linking files:

```php
$success = FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```

3. Creating activities:

```php
ActivityRepository::query()
    ->recordIds($recordIds)
    ->recordIdsAsAddress()
    ->create([
        'datetime' => $event->getDateFormatted(),
        'actionkind' => 'Newsletter',
        'actiontype' => 'Hard Bounce',
        'note' => $message,
    ]);
```

## Advanced Features

### Middlewares

You can use middlewares to intercept and modify requests:

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

The package provides debugging tools:

1. Dumping and dying:

```php
BaseRepository::query()
    ->dd()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));
```

2. Recording requests and responses:

```php
BaseRepository::record();

BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

$result = BaseRepository::lastRecorded();
```

### Default Fields

You can use predefined sets of fields for quick queries:

```php
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

$estates = EstateRepository::query()
    ->select(OnOfficeService::DEFAULT_ESTATE_INFO_FIELDS)
    ->get();
```

### Helpers

The `clean_elements` helper can be used to remove empty fields from responses:

```php
$estates = EstateRepository::query()
    ->select(OnOfficeService::DEFAULT_ESTATE_INFO_FIELDS)
    ->get();
    
$estates = clean_elements($estates);
```

## Testing

The package provides tools for mocking API responses in your tests:

```php
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

For more detailed information and advanced usage, please refer to the full documentation.

