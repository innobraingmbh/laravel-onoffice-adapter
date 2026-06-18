# Batch Repository

The onOffice API allows sending multiple actions in a single request. Use `BatchRepository` to bundle several requests into one HTTP call, for example to read estates and addresses at the same time.

```php
use Innobrain\OnOfficeAdapter\Facades\BatchRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

$results = BatchRepository::query()
    ->add(EstateRepository::query()->select('kaufpreis')->limit(10))
    ->add(AddressRepository::query()->whereLike('Vorname', 'Max'))
    ->send();
```

`send()` executes one API call and returns a collection with one result element per action, in the order they were added:

```php
$estates = data_get($results[0], 'data.records');
$addresses = data_get($results[1], 'data.records');
```

## Adding Requests

`add()` accepts query builders and raw `OnOfficeRequest` objects, in any combination:

```php
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;

$results = BatchRepository::query()
    ->add(EstateRepository::query()->select('kaufpreis'))
    ->add(new OnOfficeRequest(
        OnOfficeAction::Get,
        OnOfficeResourceType::Fields,
        parameters: ['modules' => ['estate']],
    ))
    ->send();
```

Builders are converted to their read request via `toRequest()`, which is available on all builders that support `get()` pagination (Estate, Address, Appointment, Task, Activity, User, Last Seen). A batched action is never paginated, so the builder's `limit()`, `pageSize()` and `offset()` are baked into the single request. The API caps each action at 500 records.

## Identifying Results

Results are returned in the order the requests were added. For explicit matching, you can give each request an identifier, which the API echoes back in the result:

```php
$results = BatchRepository::query()
    ->add(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
        identifier: 'estates',
    ))
    ->add(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Address,
        identifier: 'addresses',
    ))
    ->send();

$estates = data_get($results->firstWhere('identifier', 'estates'), 'data.records');
```

## Error Handling

If the batch response or any action inside it fails, an `OnOfficeException` is thrown — the same behavior as single requests. Note that the API may have executed the other actions of the batch regardless. The full response is available via `$exception->getOriginalResponse()` if you need to inspect partial results.

## Testing

Faking works like with any other repository. Each page of the faked response becomes one action result of the next `send()`:

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\BatchRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

BatchRepository::fake(BatchRepository::response([
    BatchRepository::page(recordFactories: [
        EstateFactory::make()->id(1),
    ]),
    BatchRepository::page(resourceType: OnOfficeResourceType::Address, recordFactories: [
        AddressFactory::make()->id(2),
    ]),
]));

$results = BatchRepository::query()
    ->add(...)
    ->add(...)
    ->send();
```

Every action of a batch is recorded individually, so `assertSent()` callbacks receive the single `OnOfficeRequest` objects and `assertSentCount()` counts actions, not HTTP calls:

```php
BatchRepository::assertSentCount(2);
BatchRepository::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::Address);
```
