# Getting Started

A query builder for the onOffice API with an Eloquent-style interface.

## Installation

Requires PHP 8.2+ and Laravel 11, 12, or 13.

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
To change retry settings or headers, publish the config file:
```bash
php artisan vendor:publish --tag="onoffice-adapter-config"
```
:::

## Queries

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

`get()` returns a Laravel `Collection` of records, where each record is an array with `id`, `type`, and `elements` keys. `find()` and `first()` return a single record array (or `null`); `count()` returns an `int`.

Element values come back as strings: booleans are `"0"`/`"1"` (empty: `""`) and empty dates are the literal `"0000-00-00"`. Datetimes carry no timezone marker. Creation fields such as `erstellt_am` and `Eintragsdatum` are Europe/Berlin wall time. Modification fields such as `modified` and `Letzte_Aktion` are UTC. Only appointment listings return timezone-qualified UTC.

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
| `whereNot($field, $value)` | Negated filter |
| `whereIn($field, $values)` | Filter by array of values |
| `whereNotIn($field, $values)` | Exclude array of values |
| `whereLike($field, $pattern)` | Pattern matching |
| `whereNotLike($field, $pattern)` | Negated pattern matching |
| `whereBetween($field, $min, $max)` | Range filter |
| `orderBy($field)` | Sort ascending |
| `orderByDesc($field)` | Sort descending |
| `limit($n)` | Max results |
| `offset($n)` | Skip results |

## Pagination

List builders return Laravel paginators:

```php
// LengthAwarePaginator
$estates = EstateRepository::query()->paginate(perPage: 25);

// Paginator without a total count query
$estates = EstateRepository::query()->simplePaginate(perPage: 25);

// One page, no paginator
$estates = EstateRepository::query()->forPage(page: 2, perPage: 25)->get();
```

`each()` processes one page per callback:

```php
EstateRepository::query()->each(function (array $estates) {
    // Process one page of records per call
});
```

::: warning
A failed page throws `OnOfficeException`. No partial results. Chunks already passed to the `each()` callback are not rolled back.
:::

## Available Repositories

| Repository | Description |
|------------|-------------|
| `ActionRepository` | Action types |
| `ActivityRepository` | Activity logs |
| `AddressRepository` | Contacts and addresses |
| `AppointmentRepository` | Calendar appointments |
| `BaseRepository` | Custom requests for uncovered endpoints |
| `EstateRepository` | Real estate properties |
| `FieldRepository` | Field metadata |
| `FileRepository` | File uploads and downloads |
| `FilterRepository` | Saved filters |
| `LastSeenRepository` | Recently viewed records |
| `LinkRepository` | Smart links for records |
| `LogRepository` | API logs |
| `MacroRepository` | Macro resolution |
| `MarketplaceRepository` | Marketplace operations |
| `RelationRepository` | Record relationships |
| `SearchCriteriaRepository` | Buyer search profiles |
| `SettingRepository` | Users, regions, imprint, actions |
| `TaskRepository` | Tasks |
| `UserRepository` | onOffice users |

See [Repositories](./repositories/index.md).

## Testing

Fake responses with record factories:

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

`preventStrayRequests()` makes unstubbed requests throw:

```php
EstateRepository::preventStrayRequests();
EstateRepository::fake(/* ... */);

// Any unstubbed request will throw StrayRequestException
```

See [Testing](./testing/factories.md).
