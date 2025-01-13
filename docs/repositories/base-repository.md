# Base Repository

Use `BaseRepository` for endpoints not covered by built-in repositories, or for making fully custom calls.

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;

$collection = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate
    ));
```

You can pass strings for resource types not present in `OnOfficeResourceType`. For example, `'myCustomType'`.

## Single-call Execution
```php
$first = BaseRepository::query()
    ->call(new OnOfficeRequest(...))
    ->once();
```

## Chunked Pagination
```php
BaseRepository::query()
    ->chunked(
        new OnOfficeRequest(...),
        function (array $records) {
            // Process each page chunk
        }
    );
```

## Debug Tools
- **`dd()`**: Dump request and die
- **`dump()`**: Dump request without halting execution
- **`raw()`**: Dump raw request array and die
- **`record()`** + `lastRecorded()`: Inspect the last request/response

::: tip
Use `BaseRepository` when your use case is unique or not yet fully supported by specialized repositories.
:::