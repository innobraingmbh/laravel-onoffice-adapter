---
name: onoffice-adapter
description: Query and manage onOffice CRM data (estates, addresses, activities, relations, files) with the innobrain/laravel-onoffice-adapter fluent query builder, including batching and testing with fakes.
---

# onOffice Adapter Development

## When to use this skill

Use this skill when code uses the `innobrain/laravel-onoffice-adapter` package to query or modify onOffice data, or when writing tests for such code.

## Core concepts

- Every resource has a **Facade repository** in `Innobrain\OnOfficeAdapter\Facades\*` (e.g. `EstateRepository`, `AddressRepository`, `ActivityRepository`, `RelationRepository`, `FileRepository`, `SearchCriteriaRepository`, `FieldRepository`, `AppointmentRepository`, `TaskRepository`, `UserRepository`).
- `Repository::query()` returns an Eloquent-like builder: `select()`, `where()`, `whereIn()`, `whereLike()`, `whereBetween()`, `orderBy()`, `orderByDesc()`, `limit()`, `offset()`, `when()`.
- Terminal methods: `get()` (auto-paginates all pages), `first()`, `find($id)`, `count()`, `each(fn)` (chunked processing), `create()`, `modify()`. Special-purpose builders (relations, files, fields) support only a subset. The relation builder has no `select()`/`where()`/`orderBy()`/`find()`/`count()`.
- `select()` only the fields needed.
- For endpoints without a repository, use `BaseRepository::query()->call(new OnOfficeRequest(...))`.

## Querying

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

$estates = EstateRepository::query()
    ->select(['Id', 'objekttitel', 'kaufpreis', 'wohnflaeche', 'status'])
    ->where('status', 1)
    ->where('kaufpreis', '<', 500000)
    ->whereIn('objektart', ['haus', 'wohnung'])
    ->orderByDesc('kaufpreis')
    ->limit(50)
    ->get();

$estate = EstateRepository::query()->find(123);
```

`each()` processes one page per callback:

```php
EstateRepository::query()->each(function (array $estates) {
    // one page per callback invocation
});
```

A failed page throws `OnOfficeException`. No partial results. Chunks already passed to the `each()` callback are not rolled back.

## Writing data

```php
// Create
$estate = EstateRepository::query()->create([
    'objektart' => 'haus',
    'vermarktungsart' => 'kauf',
    'kaufpreis' => 350000,
]);

// Modify
EstateRepository::query()
    ->addModify(['kaufpreis' => 320000])
    ->modify(123);

// Log an activity
ActivityRepository::query()
    ->estateId(41)
    ->addressIds([34])
    ->create([
        'actionkind' => 'Email',
        'actiontype' => 'Ausgang',
        'note' => 'Contract sent',
    ]);
```

## Relations

Link records with `RelationRepository` and the `OnOfficeRelationType` enum:

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;

// Get contact persons for an estate
// Returns a Collection keyed by parent ID => array of child IDs, not full records
$contacts = RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([48])
    ->get();

// Create a buyer relation (parent = estate, child = address)
RelationRepository::query()
    ->relationType(OnOfficeRelationType::Buyer)
    ->parentIds([48])
    ->childIds([181])
    ->create();
```

## Files

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;

FileRepository::upload()
    ->uploadInBlocks()
    ->saveAndLink(base64_encode($fileContent), [
        'module' => 'estate',
        'relatedRecordId' => 409,
        'file' => 'document.pdf',
        'Art' => 'Dokument',
    ]);

// Estate pictures
$pictures = EstateRepository::pictures(123)
    ->category(['Titelbild', 'Foto'])
    ->get();
```

## Batching multiple actions in one HTTP call

`Query::batch()` sends builders or raw `OnOfficeRequest` objects in one API call. Batched actions are never paginated: only the first page is returned (max 500 records per action). Only builders implementing `toRequest()` are batchable: estate, address, activity, appointment, task, user, last-seen, relation, and link. Others throw.

```php
use Innobrain\OnOfficeAdapter\Facades\Query;

$results = Query::batch([
    EstateRepository::query()->select('kaufpreis')->limit(10),
    AddressRepository::query()->whereLike('Vorname', 'Max'),
])->once();

$estates = data_get($results[0], 'data.records');

// Single-record reads inside a batch
$results = Query::batch([
    EstateRepository::query()->withId(5),
    AddressRepository::query()->withId(9),
])->once();
```

## Testing

Fake repositories in tests:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1)->data(['kaufpreis' => 450000]),
    ]),
]));

$estates = EstateRepository::query()->get();

expect($estates)->toHaveCount(1);
EstateRepository::assertSentCount(1);
EstateRepository::assertSent(fn ($request) => /* inspect OnOfficeRequest */ true);
```

- Multiple `page()` entries in one `response()` fake pagination; multiple `response()` entries fake sequential calls.
- `Repository::preventStrayRequests()` makes unstubbed requests throw.
- Batches are faked through `Query::fake()` only. Per-repository fakes are never consumed by a batch. Fake exactly one page per batched action.
- Factories exist for each record type (`EstateFactory`, `AddressFactory`, `ActivityFactory`, `RelationFactory`, `FileFactory`, ...).

## Useful extras

```php
// Conditional clauses
EstateRepository::query()
    ->when($maxPrice, fn ($q) => $q->where('kaufpreis', '<=', $maxPrice))
    ->get();

// Request middleware / debugging
EstateRepository::query()
    ->before(fn ($request) => Log::info('Sending', ['request' => $request->toArray()]))
    ->after(fn ($response) => Log::info('Received', ['status' => $response->status()]))
    ->get();

EstateRepository::query()->dump()->get(); // dump request without stopping
EstateRepository::query()->dd()->get();   // dump and die

// Per-call credentials (multi-tenant)
EstateRepository::query()->withCredentials($token, $secret)->get();
```

## Field names

Field names are customer-specific and mostly German (`kaufpreis`, `wohnflaeche`, `objektart`). Query `FieldRepository` for available fields and permitted values:

```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

$fields = FieldRepository::query()->withModules(['estate'])->get();
```
