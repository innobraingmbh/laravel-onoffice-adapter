# Advanced Usage

This guide describes additional usage patterns and advanced techniques for working with the onOffice Adapter for Laravel.
It covers scenarios like custom middlewares, advanced debugging, concurrency strategies, and more.

## 1. Advanced Middlewares

Beyond simple logging, you might want to alter the request payload dynamically or inject extra logic before every request:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;

BaseRepository::query()
    ->before(static function (OnOfficeRequest $request) {
        // You can dynamically alter the request parameters:
        $request->parameters['someDynamicKey'] = 'someValue';

        // Or read the request to conditionally do more advanced logic:
        if ($request->resourceType === 'estate') {
            // Perform specialized logging or transformations...
        }
    })
    ->call(
        new OnOfficeRequest(
            // For example, a read call...
        )
    );
```

### Tips
1. **Conditional Logic**: Middlewares allow you to attach custom logic to just the calls or resources that matter.
2. **Multiple Middlewares**: You can chain as many `before(...)` calls as needed. Each middleware in the chain can modify the `OnOfficeRequest`.

## 2. Using the `BaseRepository` for Generic Endpoints

If a repository for your particular use case doesn't exist yet, or if your use case doesn't neatly fit into the provided repositories, you can still call custom endpoints using the `BaseRepository`:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;

// Example:
$collection = BaseRepository::query()
    ->call(
        new OnOfficeRequest(
            OnOfficeAction::Read,
            'myCustomResourceType', // not in the Enums yet
            123 // Resource ID
        )
    );
```

This way, you maintain full control over the request structure while also benefiting from the same debugging features, chunking logic, and stubbing capabilities as the built-in repositories.


## 3. Advanced Debugging and Inspecting

By default, the adapter provides `dd()`, `dump()`, `raw()`, and `record()` for debugging, but you can chain them in more advanced ways:

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;

// Dump the request, then proceed to record it:
BaseRepository::query()
    ->dump()
    ->record()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

// After the call, retrieve the last recorded request and response:
$request = BaseRepository::lastRecordedRequest();
$response = BaseRepository::lastRecordedResponse();

// Possibly do more logic or assertions with them:
if ($response['response']['results'][0]['status']['errorcode'] !== 0) {
    // Handle error scenario
}
```

### Best Practices
1. **Chain**: You can combine multiple debugging methods. For instance, calling `dd()->dump()` is possible, but once `dd()` is reached, execution will stop.
2. **Conditional**: You can conditionally call debugging features if you're only diagnosing specific calls or error states.

## 4. Concurrency and Chunking Patterns

When retrieving large datasets, consider chunking or concurrency patterns to prevent timeouts or memory issues.

### Example: Chunked Processing

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::query()
    ->each(function (array $estates) {
        // Process the entire page of estates here
        foreach ($estates as $estate) {
            // Handle each estate
        }
    });
```

Under the hood, the adapter will:
1. Fetch the first chunk.
2. Call your callback with that chunk.
3. Determine if there is another page to fetch.
4. Continue until no more pages are left or the chunk limit is reached.

**Pros**:
- Memory-friendly approach.
- Automatic pagination logic internally.

**Cons**:
- Potentially slower total run time if data is spread across many pages.

## 5. Extending the Adapter

You can create your own repositories by extending `BaseRepository` with custom builders:

```php
namespace App\Repositories;

use Innobrain\OnOfficeAdapter\Repositories\BaseRepository;
use App\Query\CustomBuilder;

class MyRepository extends BaseRepository
{
    protected function createBuilder(): CustomBuilder
    {
        // Return your builder logic
        return new CustomBuilder;
    }
}
```

Then define the new builder to handle specialized logic, or unique endpoints within onOffice.

## 6. Known Edge Cases

1. **Invalid HMAC**: If your token/secret mismatch onOffice's expectations, you'll see a `The HMAC is invalid` error. Check that both the token is 32 chars and the secret is 64.
2. **Resource Types**: If you're passing a resource type that doesn't exist in the `OnOfficeResourceType` enum, ensure you're referencing correct API docs or simply pass a string.

## Summary

- **Middlewares** can do advanced request transformations or logging.
- **`BaseRepository`** can help with endpoints not fully supported by existing repositories.
- **Debugging** goes beyond `dump()` and `dd()`; you can record requests/responses and chain multiple debugging techniques.
- **Chunked retrieval** helps process large results in memory-safe pieces.
- **Custom Repositories** can be built for specialized endpoints.
- **Edge Cases**: Mind token & secret constraints, or add your own enum entries for custom resource types.

Explore these patterns to better leverage the onOffice Adapter for scenarios that exceed simple CRUD operations. For any further details, check [Getting Started](/getting-started.md) and the official onOffice API documentation.
