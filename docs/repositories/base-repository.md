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

## Check User Rights
```php
BaseRepository::query()
    ->checkUserRights('edit', 'estate', 1)
    ->get();
```
Note this is not a possible request in the BaseRepository, but rather a showoff of the `after` callback of the request.
We can use this chainable method to check if the given user has the rights to the results of the request.
This is important if we are using the Master User for the request, but acting as a different user in the application.
The method is using the request response and removes every record that the user does not have rights to and returning a copied but filtered response object.

## Debug Tools
- **`dd()`**: Dump request and die
- **`dump()`**: Dump request without halting execution
- **`raw()`**: Dump raw request array and die
- **`record()`** + `lastRecorded()`: Inspect the last request/response

::: tip
Use `BaseRepository` when your use case is unique or not yet fully supported by specialized repositories.
:::
