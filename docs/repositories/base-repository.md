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
The `once()` method executes a single API request and returns the `Illuminate\Http\Client\Response` object.
```php
$response = BaseRepository::query()
    ->once(new OnOfficeRequest(...));

// You can then process the response, for example:
// $record = $response->json('response.results.0.data.records.0');
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

## Check User Record Rights
```php
BaseRepository::query()
    ->checkUserRecordsRight('edit', 'estate', 1)
    ->get();
```
This chainable method checks if the given user has rights to the records in the response. It removes every record that the user does not have access to from the response. This is useful when using the Master User credentials for requests but acting as a different user in the application.

## Debug Tools
- **`dd()`**: Dump request and die
- **`dump()`**: Dump request without halting execution
- **`raw()`**: Dump raw request array and die
- **`record()`** + `lastRecorded()`: Inspect the last request/response

::: tip
Use `BaseRepository` when your use case is unique or not yet fully supported by specialized repositories.
:::
