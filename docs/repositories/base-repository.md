# Base Repository

`BaseRepository` sends any request.

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

Resource types not in `OnOfficeResourceType` can be passed as strings.

## Single-call Execution
`once()` sends one request and returns the `Illuminate\Http\Client\Response`.
```php
$response = BaseRepository::query()
    ->once(new OnOfficeRequest(...));

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

::: warning
A failed page throws `OnOfficeException`. Chunks already passed to the callback are not rolled back.
:::

## Check User Record Rights
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::query()
    ->checkUserRecordsRight('edit', 'estate', 1)
    ->get();
```
Removes every record the given user cannot access from the response. Use it when requests run with master credentials on behalf of another user.

## Debug Tools
- `dd()`: dump the request and exit
- `dump()`: dump the request and continue
- `raw()`: dump the raw request array and exit
- `record()` + `lastRecorded()`: the last request/response pair
- `lastRecordedRequest()` / `lastRecordedResponse()`: only the request or only the response
- `stopRecording()`: stop recording
