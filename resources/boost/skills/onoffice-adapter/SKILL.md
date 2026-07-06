---
name: onoffice-adapter
description: Query and manage onOffice CRM data (estates, addresses, activities, relations, files) with the innobrain/laravel-onoffice-adapter fluent query builder, including batching and testing with fakes.
---

# onOffice Adapter Development

## When to use this skill

Use this skill when working with the `innobrain/laravel-onoffice-adapter` package — whenever code needs to talk to the onOffice (enterprise) real estate CRM API: querying or modifying estates, addresses, activities, relations, files, or writing tests for such code.

## Core concepts

- Every resource has a **Facade repository** in `Innobrain\OnOfficeAdapter\Facades\*` (e.g. `EstateRepository`, `AddressRepository`, `ActivityRepository`, `RelationRepository`, `FileRepository`, `SearchCriteriaRepository`, `FieldRepository`, `AppointmentRepository`, `TaskRepository`, `UserRepository`).
- `Repository::query()` returns an Eloquent-like builder: `select()`, `where()`, `whereIn()`, `whereLike()`, `whereBetween()`, `orderBy()`, `orderByDesc()`, `limit()`, `offset()`, `when()`.
- Terminal methods: `get()` (auto-paginates all pages), `first()`, `find($id)`, `count()`, `each(fn)` (chunked processing), `create()`, `modify()`.
- Always `select()` only the fields you need to minimize API payload.
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

For large datasets, process in chunks instead of loading everything:

```php
EstateRepository::query()->each(function (array $estates) {
    // one page per callback invocation
});
```

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
ActivityRepository::query()->create([
    'addressids' => [34],
    'estateid' => 41,
    'actionkind' => 'Email',
    'actiontype' => 'Ausgang',
    'note' => 'Contract sent',
]);
```

## Relations

Link records (buyer ↔ estate, owner ↔ estate, etc.) with `RelationRepository` and the `OnOfficeRelationType` enum:

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;

// Get contact persons for an estate
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

Use the `Query` facade to bundle builders (or raw `OnOfficeRequest` objects) into a single API call. Batched actions are **never paginated** — you get the first page only (max 500 records per action):

```php
use Innobrain\OnOfficeAdapter\Facades\Query;

$results = Query::batch([
    EstateRepository::query()->select('kaufpreis')->limit(10),
    AddressRepository::query()->whereLike('Vorname', 'Max'),
])->once();

$estates = data_get($results[0], 'data.records');

// Single-record reads inside a batch: withId() is the lazy form of find()
$results = Query::batch([
    EstateRepository::query()->withId(5),
    AddressRepository::query()->withId(9),
])->once();
```

## Testing

Never hit the real API in tests. Fake repositories with response pages built from record factories:

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
- Batches are faked through `Query::fake()` only — per-repository fakes are never consumed by a batch. Fake exactly one page per batched action.
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
    ->get();

EstateRepository::query()->dump()->get(); // dump request without stopping
EstateRepository::query()->dd()->get();   // dump and die

// Per-call credentials (multi-tenant)
EstateRepository::query()->withCredentials($token, $secret)->get();
```

## Field names

onOffice field names are customer-specific and mostly German (`kaufpreis`, `wohnflaeche`, `objektart`). When unsure which fields exist or which values a select field permits, query them via `FieldRepository` instead of guessing:

```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

$fields = FieldRepository::query()->withModules(['estate'])->get();
```
