# Filter Repository

Retrieve filter definitions for the `address` or `estate` modules.

## Usage
```php
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

// Estate filters
$filters = FilterRepository::query()
    ->estate()
    ->get();

// Address filters
$filters = FilterRepository::query()
    ->address()
    ->get();
```

::: warning
You **must** specify a module (`estate()` or `address()`) before calling `get()`, `first()`, or `each()`.
:::

## Example
```php
$filter = FilterRepository::query()
    ->estate()
    ->first();

FilterRepository::query()
    ->address()
    ->each(function (array $filters) {
        // handle each page chunk of filters
    });
```

If you fail to set a module, an `OnOfficeQueryException` is thrown.
Use filters to see the available filter options in onOffice for each module.