# Query

The onOffice API accepts multiple actions in one request. `Query::batch()` sends several requests in one HTTP call:

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
> A batched action is never paginated. Only the first page is returned (max 500 records per action). The builder's `limit()`, `pageSize()` and `offset()` apply to that single request. To read every matching record, use the repository's `get()` instead.

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

`add()` appends to a batch:

```php
Query::batch()
    ->add(EstateRepository::query()->select('kaufpreis'))
    ->add(AddressRepository::query()->whereLike('Vorname', 'Max'))
    ->once();
```

Builders are converted to their read request via `toRequest()`, which is available on the Estate, Estate Language, Address, Appointment, Task, Activity, User, Last Seen, Relation, and Link builders.

A builder's `withCredentials()` applies to the whole batch, since all actions are sent in one API call. Builders with different credentials in the same batch throw an `OnOfficeException`. Send them as separate batches.

Credentials can also be set on the batch itself, which is the only way to send raw `OnOfficeRequest` objects with their own credentials:

```php
Query::batch([
    new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
])->withCredentials($token, $secret)->once();
```

The same conflict rule applies: batch credentials that differ from a builder's credentials throw.

## Reading a Single Record

`withId()` reads one record by id instead of a list:

```php
$results = Query::batch([
    EstateRepository::query()->withId(5),
    AddressRepository::query()->withId(9),
])->once();

$estate = data_get($results[0], 'data.records.0');
```

`withId()` sets the target id without sending. `find($id)` sends immediately and returns the record or `null`. Use `withId()` only inside `Query::batch()`.

An id-scoped read has no paging parameters. `limit()`, `pageSize()` and `offset()` are ignored, so `withId($id)` sends the same request as `find($id)`.

```php
EstateRepository::query()->find(5);                 // sends now, returns the record
EstateRepository::query()->withId(5);               // sends later, inside Query::batch()
```

`withId()` is available on the paginating builders (Estate, Address, Appointment, Task, Activity, User, Last Seen). Relation and Link builders support `toRequest()` but not `withId()`.

## Identifying Results

Results are returned in the order the requests were added. An identifier on a request is echoed back in its result:

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

If the batch response or any action inside it fails, an `OnOfficeException` is thrown. The API may have executed the other actions regardless. The full response is available via `$exception->getOriginalResponse()`.

A response without exactly one result per action also throws an `OnOfficeException`.

## Testing

Each page of the faked response becomes one action result, in the order the requests were added. Every action is recorded individually: `assertSentCount()` counts actions, not HTTP calls, and `assertSent()` callbacks receive the individual `OnOfficeRequest` objects:

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

Batches are faked through `Query::fake()` only. A per-repository fake such as `EstateRepository::fake()` is never consumed by a batch. An unfaked batch that contains a builder from a faked or stray-preventing repository throws a `StrayRequestException`. Fake exactly one page per action; a count mismatch throws an `OnOfficeException`.

To fake a failing action, set `errorCodeResult`/`messageResult` on its page. The top-level `status`/`errorCode`/`message` fields describe the whole response and are taken from the first page only. Setting a failing one on a later page throws.
