# Advanced Usage

## Middlewares
`before()` runs a callback before each request:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;

BaseRepository::query()
    ->before(function (OnOfficeRequest $request) {
        // Add a parameter dynamically
        $request->parameters['someDynamicKey'] = 'someValue';
    })
    ->call(
        new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        )
    );
```

::: tip
Multiple `before()` calls run in order.
:::

## Custom Endpoints with BaseRepository
`BaseRepository` sends any request:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;

$results = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        'customResource' // string resource types are accepted
    ));
```

## Debugging

```php
// Dump the request without stopping execution
BaseRepository::query()->dump()->call(...);

// Dump the raw request payload, then exit
BaseRepository::query()->raw()->call(...);

// Record requests and responses
BaseRepository::record();
BaseRepository::query()->call(...);
$lastPair = BaseRepository::lastRecorded();
```

`raw()` and `dd()` call `exit(1)` after dumping. `dump()` lets the request complete.

## Chunked Reads
`each()` processes one page per callback:

```php
EstateRepository::query()
    ->each(function (array $estates) {
        foreach ($estates as $estate) {
            // Process chunk
        }
    });
```

::: warning
Each chunk requests the next page automatically. A failed page throws `OnOfficeException`. Chunks already passed to the callback are not rolled back.
:::

## Extending the Adapter
Extend `Builder` and `BaseRepository` for a custom endpoint:

```php
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Repositories\BaseRepository;

class MySpecialBuilder extends Builder
{
    public function fetchSomething(): Collection
    {
        // custom logic here
    }
}

class MySpecialRepository extends BaseRepository
{
    protected function createBuilder(): MySpecialBuilder
    {
        return new MySpecialBuilder();
    }
}
```

Custom repositories get faking, chunking, and debugging from the base classes.