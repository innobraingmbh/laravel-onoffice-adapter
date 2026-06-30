# Bus

The onOffice API allows sending multiple actions in a single request. Use the `Bus` facade to bundle several requests into one HTTP call, for example to read estates and addresses at the same time. It reads like Laravel's `Bus::batch()`:

```php
use Innobrain\OnOfficeAdapter\Facades\Bus;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

$results = Bus::batch([
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

$results = Bus::batch([
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
Bus::batch()
    ->add(EstateRepository::query()->select('kaufpreis'))
    ->add(AddressRepository::query()->whereLike('Vorname', 'Max'))
    ->once();
```

Builders are converted to their read request via `toRequest()`, which is available on all builders that support `get()` pagination (Estate, Address, Appointment, Task, Activity, User, Last Seen). A batched action is never paginated, so the builder's `limit()`, `pageSize()` and `offset()` are baked into the single request. The API caps each action at 500 records.

## Identifying Results

Results are returned in the order the requests were added. For explicit matching, you can give each request an identifier, which the API echoes back in the result:

```php
$results = Bus::batch([
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

## Testing

Faking works like with any other repository. Each page of the faked response becomes one action result of the next `once()`:

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\Bus;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

Bus::fake(Bus::response([
    Bus::page(recordFactories: [
        EstateFactory::make()->id(1),
    ]),
    Bus::page(resourceType: OnOfficeResourceType::Address, recordFactories: [
        AddressFactory::make()->id(2),
    ]),
]));

$results = Bus::batch([
    // ...
])->once();
```

Every action of a batch is recorded individually, so `assertSent()` callbacks receive the single `OnOfficeRequest` objects and `assertSentCount()` counts actions, not HTTP calls:

```php
Bus::assertSentCount(2);
Bus::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::Address);
```
