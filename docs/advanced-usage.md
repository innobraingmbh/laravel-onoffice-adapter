# Advanced Usage

When interacting with the **onOffice Adapter for Laravel** in more sophisticated ways, you can leverage middlewares, advanced debugging, concurrency, and custom repository extensions.

## Middlewares
Middlewares let you inject custom logic before each request is sent:

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
Use multiple `before` calls to chain any number of middlewares.
:::

## Custom Endpoints with BaseRepository
When an endpoint or feature is not covered by the existing repositories, you can directly interact with onOffice using `BaseRepository`:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;

$results = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        'customResource' // If not defined in OnOfficeResourceType
    ));
```

## Advanced Debugging
In addition to `dd()` (dump and die), the adapter supports:

```php
// Dump the request without stopping execution
BaseRepository::query()->dump()->call(...);

// Dump the raw request payload, then exit — like dd()
BaseRepository::query()->raw()->call(...);

// Record requests and responses
BaseRepository::record();
BaseRepository::query()->call(...);
$lastPair = BaseRepository::lastRecorded();
```

Note that `raw()` and `dd()` call `exit(1)` after dumping — only `dump()` lets the request complete.

## Large Dataset Handling
To handle large datasets without memory issues, use chunked processing with `each()`:

```php
EstateRepository::query()
    ->each(function (array $estates) {
        foreach ($estates as $estate) {
            // Process chunk
        }
    });
```

::: warning
Each chunk requests the next page automatically.
:::

## Extending the Adapter
If you frequently call a particular endpoint, you can extend `BaseRepository` and implement your own custom builder for specialized logic:

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

This leverages the same stubbing, chunking, and debugging features as default repositories.