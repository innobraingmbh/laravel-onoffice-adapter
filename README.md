# onOffice Adapter for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/innobrain/laravel-onoffice-adapter.svg?style=flat-square)](https://packagist.org/packages/innobrain/laravel-onoffice-adapter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/laravel-onoffice-adapter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/innobraingmbh/laravel-onoffice-adapter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/laravel-onoffice-adapter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/innobraingmbh/laravel-onoffice-adapter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/innobrain/laravel-onoffice-adapter.svg?style=flat-square)](https://packagist.org/packages/innobrain/laravel-onoffice-adapter)

A fluent query builder for the onOffice API, designed to feel like Eloquent.

**[View Full Documentation](https://innobraingmbh.github.io/laravel-onoffice-adapter/)**

## Features

- **ORM-like Querying** - Use familiar Laravel Eloquent-style methods like `where()`, `select()`, `orderBy()`
- **Comprehensive Repositories** - Access estates, addresses, activities, search criteria, files, and more
- **Pagination & Chunking** - Handle large datasets with `each()`, `limit()`, and automatic pagination
- **Middlewares** - Insert custom logic before requests for logging, modification, or validation
- **Testing Support** - Built-in fake system with factories for easy unit testing
- **File Management** - Upload, chunk, and link files with onOffice

## Installation

```bash
composer require innobrain/laravel-onoffice-adapter
```

Add your onOffice API credentials to `.env`:

```
ON_OFFICE_TOKEN=your-token
ON_OFFICE_SECRET=your-secret
```

For advanced configuration (retry settings, custom headers), publish the config file:

```bash
php artisan vendor:publish --tag="laravel-onoffice-adapter-config"
```

## Usage

### Basic Queries

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

// Get all estates
$estates = EstateRepository::query()->get();

// Find by ID
$estate = EstateRepository::query()->find(123);

// Get first result
$estate = EstateRepository::query()->first();

// Count results
$count = EstateRepository::query()->count();
```

### Building Queries

Chain methods to filter, sort, and limit results:

```php
$estates = EstateRepository::query()
    ->select(['Id', 'kaufpreis', 'lage'])
    ->where('status', 1)
    ->where('kaufpreis', '<', 500000)
    ->orderByDesc('kaufpreis')
    ->limit(10)
    ->get();
```

### Available Methods

| Method | Description |
|--------|-------------|
| `select($fields)` | Fields to retrieve |
| `where($field, $op, $value)` | Filter by condition |
| `whereIn($field, $values)` | Filter by array of values |
| `whereLike($field, $pattern)` | Pattern matching |
| `whereBetween($field, $min, $max)` | Range filter |
| `orderBy($field)` | Sort ascending |
| `orderByDesc($field)` | Sort descending |
| `limit($n)` | Max results |
| `offset($n)` | Skip results |

### Large Datasets

Handle large datasets without memory issues using chunked processing:

```php
EstateRepository::query()
    ->each(function (array $estates) {
        foreach ($estates as $estate) {
            // Process chunk
        }
    });
```

### Available Repositories

| Repository | Description |
|------------|-------------|
| `EstateRepository` | Real estate properties |
| `AddressRepository` | Contacts and addresses |
| `ActivityRepository` | Activity logs |
| `SearchCriteriaRepository` | Buyer search profiles |
| `FieldRepository` | Field metadata |
| `FileRepository` | File uploads and downloads |
| `FilterRepository` | Saved filters |
| `RelationRepository` | Record relationships |
| `SettingRepository` | System settings |
| `MarketplaceRepository` | Marketplace integration |
| `LinkRepository` | URL links |
| `LogRepository` | Log entries |
| `MacroRepository` | Macros |
| `LastSeenRepository` | Recently viewed records |

### File Uploads

```php
$tmpUploadId = FileRepository::upload()
    ->save(base64_encode($fileContent));

FileRepository::upload()->link($tmpUploadId, [
    'module' => 'estate',
    'relatedRecordId' => '12345',
]);

// Or upload in blocks and link in one call
FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => '12345',
    ]);
```

### Creating Activities

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

## Middlewares

Inject custom logic before each request:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;

BaseRepository::query()
    ->before(function (OnOfficeRequest $request) {
        Log::info('Sending request', ['request' => $request->toArray()]);
    })
    ->call(new OnOfficeRequest(/* ... */));
```

## Debugging

```php
// Dump and die
BaseRepository::query()->dd()->call(/* ... */);

// Dump without stopping
BaseRepository::query()->dump()->call(/* ... */);

// Record requests and responses
BaseRepository::record();
BaseRepository::query()->call(/* ... */);
$lastPair = BaseRepository::lastRecorded(); // [OnOfficeRequest, OnOfficeResponse]
```

## Helpers

Use default fields and clean empty values:

```php
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

$estates = EstateRepository::query()
    ->select(OnOfficeService::DEFAULT_ESTATE_INFO_FIELDS)
    ->get();

// Remove fields with empty values ("", "0.00", [], null)
$estates = clean_elements($estates);
```

## Testing

The package includes a built-in fake system for testing:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1)->set('kaufpreis', 250000),
        EstateFactory::make()->id(2)->set('kaufpreis', 300000),
    ]),
]));

$estates = EstateRepository::query()->get();

expect($estates)->toHaveCount(2);
EstateRepository::assertSentCount(1);
```

### Prevent Unstubbed Requests

```php
EstateRepository::preventStrayRequests();
EstateRepository::fake(/* ... */);

// Any unstubbed request will throw StrayRequestException
```

### Multiple Pages

```php
EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1),
    ]),
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(2),
    ]),
]));

$estates = EstateRepository::query()->get();
expect($estates)->toHaveCount(2);
```

### Sequences (Multiple Calls)

```php
EstateRepository::fake([
    EstateRepository::response([/* first call */]),
    EstateRepository::response([/* second call */]),
]);

// Or repeat the same response
EstateRepository::fake(EstateRepository::sequence(
    EstateRepository::response([/* ... */]),
    times: 30,
));
```

See the [Testing Documentation](https://innobraingmbh.github.io/laravel-onoffice-adapter/testing/factories) for factories, assertions, and advanced patterns.

## Development

```bash
composer test       # Run tests
composer analyse    # Static analysis (PHPStan)
composer format     # Code formatting (Laravel Pint)
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
