# Query

The onOffice API allows sending multiple actions in a single request. Use the `Query` facade to bundle several requests into one HTTP call, for example to read estates and addresses at the same time:

```php
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

$results = Query::batch([
    EstateRepository::query()->select('kaufpreis')->limit(10),
    AddressRepository::query()->whereLike('Vorname', 'Max'),
])->once();
```

`once()` executes one API call and returns a collection with one result element per action, in the order they were added:

```php
$estates = data_get($results[0], 'data.records');
$addresses = data_get($results[1], 'data.records');
```

> [!WARNING]
> A batched action is never paginated — you get the **first page only** (max 500 records per action). The builder's `limit()`, `pageSize()` and `offset()` are baked into that single request. If you need every matching record, query the resource through its own repository with `->get()` instead of batching it.

## Adding Requests

`batch()` accepts query builders and raw `OnOfficeRequest` objects, in any combination:

```php
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;

$results = Query::batch([
    EstateRepository::query()->select('kaufpreis'),
    new OnOfficeRequest(
        OnOfficeAction::Get,
        OnOfficeResourceType::Fields,
        parameters: ['modules' => ['estate']],
    ),
])->once();
```

You can also keep adding to the batch fluently before sending:

```php
Query::batch()
    ->add(EstateRepository::query()->select('kaufpreis'))
    ->add(AddressRepository::query()->whereLike('Vorname', 'Max'))
    ->once();
```

Builders are converted to their read request via `toRequest()`, which is available on all builders that support `get()` pagination (Estate, Address, Appointment, Task, Activity, User, Last Seen).

A builder's `withCredentials()` apply to the whole batch, since all actions are sent in one API call. Adding builders with different credentials to the same batch throws an `OnOfficeException` — send them as separate batches instead.

## Reading a Single Record

Call `withId()` on a builder to read one record by its id instead of a list. It is the batch-friendly counterpart to `find()`:

```php
$results = Query::batch([
    EstateRepository::query()->withId(5),
    AddressRepository::query()->withId(9),
])->once();

$estate = data_get($results[0], 'data.records.0');
```

`withId()` is the lazy form of a single-record read: it sets the target id and waits. `find($id)` is the eager form of `->withId($id)->first()` — it sends straight away and hands you the record itself, or `null` when it is missing. Reach for `withId()` only when you want to defer the read into `Query::batch()`; otherwise `find()` is more direct.

An id-scoped read carries no paging parameters: the builder's `limit()`, `pageSize()` and `offset()` are ignored, so `withId($id)` sends exactly the request `find($id)` sends — inside a batch or out.

```php
EstateRepository::query()->find(5);                 // eager — returns the record
EstateRepository::query()->withId(5);               // lazy — defer into Query::batch()
```

`withId()` is available on the same builders as `toRequest()`.

## Identifying Results

Results are returned in the order the requests were added. For explicit matching, you can give each request an identifier, which the API echoes back in the result:

```php
$results = Query::batch([
    new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
        identifier: 'estates',
    ),
    new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Address,
        identifier: 'addresses',
    ),
])->once();

$estates = data_get($results->firstWhere('identifier', 'estates'), 'data.records');
```

## Error Handling

If the batch response or any action inside it fails, an `OnOfficeException` is thrown — the same behavior as single requests. Note that the API may have executed the other actions of the batch regardless. The full response is available via `$exception->getOriginalResponse()` if you need to inspect partial results.

A response that does not contain exactly one result per action also throws an `OnOfficeException`, so a truncated response can never be silently misaligned with the request order.

## Testing

Faking works like with any other repository. Each page of the faked response becomes one action result, returned in the order the requests were added. Every action is recorded individually, so `assertSentCount()` counts actions (not HTTP calls) and `assertSent()` callbacks receive the individual `OnOfficeRequest` objects:

```php
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

test('it reads estates and addresses in one call', function () {
    Query::fake(Query::response([
        Query::page(recordFactories: [
            EstateFactory::make()->id(1),
        ]),
        Query::page(resourceType: OnOfficeResourceType::Address, recordFactories: [
            AddressFactory::make()->id(2),
        ]),
    ]));

    $results = Query::batch([
        EstateRepository::query()->select('kaufpreis'),
        AddressRepository::query()->whereLike('Vorname', 'Max'),
    ])->once();

    expect(data_get($results[0], 'data.records.0.id'))->toBe(1)
        ->and(data_get($results[1], 'data.records.0.id'))->toBe(2);

    Query::assertSentCount(2);
    Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::Address);
});
```

Batches are faked through `Query::fake()` only — a per-repository fake like `EstateRepository::fake()` is never consumed by a batch. To prevent a mistake here from silently hitting the live API, a batch that contains a builder from a faked (or stray-preventing) repository throws a `StrayRequestException` when the batch itself is not faked. Fake exactly one page per action; a count mismatch throws an `OnOfficeException`.
