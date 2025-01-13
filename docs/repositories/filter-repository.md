# Filter Repository

The Filter Repository provides functionality to retrieve filter definitions for both address and estate modules in the onOffice system.

## Query Operations

### Basic Queries

```php
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

// Get estate filters
$result = FilterRepository::query()
    ->estate()
    ->get();

// Get address filters
$result = FilterRepository::query()
    ->address()
    ->get();

// Get first filter
$filter = FilterRepository::query()
    ->estate()
    ->first();

// Process filters in chunks
FilterRepository::query()
    ->address()
    ->each(function (array $filters) {
        // Process each chunk of filters
    });
```

::: warning
You must specify a module using either `estate()` or `address()` before calling any query method (`get()`, `first()`, or `each()`).
:::

### Error Handling

If you don't specify a module, you will get an `OnOfficeQueryException`:

```php
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;

try {
    $result = FilterRepository::query()->get();
} catch (OnOfficeQueryException $e) {
    // Exception: Filter Builder module is not set
}
```

## Available Methods

### Module Selection Methods
- `estate()`: Set module to estate filters
- `address()`: Set module to address filters

### Query Methods
- `get()`: Returns a Collection of all filters for the selected module
- `first()`: Returns the first filter or null
- `each(callable $callback)`: Processes filters in chunks using the provided callback

## Returns

- Query operations return either a Collection of filters or a single filter array
- Each filter contains information about available filter options for the selected module

## Exceptions

- Throws `OnOfficeQueryException` if no module is specified
- May throw `OnOfficeException` for API-related errors
