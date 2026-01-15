# Getting Started

A fluent query builder for the onOffice API, designed to feel like Eloquent.

## Installation

```bash
composer require innobrain/laravel-onoffice-adapter
```

## Configuration

Add your onOffice API credentials to `.env`:

```
ON_OFFICE_TOKEN=your-token
ON_OFFICE_SECRET=your-secret
```

::: tip
For advanced configuration (retry settings, custom headers), publish the config file:
```bash
php artisan vendor:publish --tag="laravel-onoffice-adapter-config"
```
:::

## Your First Query

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

All queries return a Laravel `Collection` of arrays, where each array represents a record with `id`, `type`, and `elements` keys.

## Building Queries

Chain methods to filter, sort, and limit results:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

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

## Available Repositories

| Repository | Description |
|------------|-------------|
| `EstateRepository` | Real estate properties |
| `AddressRepository` | Contacts and addresses |
| `ActivityRepository` | Activity logs |
| `SearchCriteriaRepository` | Buyer search profiles |
| `FieldRepository` | Field metadata |
| `FileRepository` | File uploads and downloads |
| `RelationRepository` | Record relationships |
| `SettingRepository` | System settings |

See [Repositories](./repositories/index.md) for detailed documentation on each.

## Testing Your Code

The package includes a built-in fake system for testing. Stub API responses with factories:

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

Use `preventStrayRequests()` to ensure all API calls are stubbed:

```php
EstateRepository::preventStrayRequests();
EstateRepository::fake(/* ... */);

// Any unstubbed request will throw StrayRequestException
```

See [Testing](./testing/factories.md) for factories, assertions, and advanced patterns.
