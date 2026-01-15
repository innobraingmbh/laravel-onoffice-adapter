# Filter Repository

Retrieve predefined filter definitions from onOffice enterprise. Filters are configured in "Extras >> Settings >> Filters" and can be used to apply saved filter logic to queries.

## Usage

```php
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

// Get estate filters
$filters = FilterRepository::query()
    ->estate()
    ->get();

// Get address filters
$filters = FilterRepository::query()
    ->address()
    ->get();
```

::: warning
You **must** specify a module (`estate()` or `address()`) before calling `get()`, `first()`, or `each()`. An `OnOfficeQueryException` is thrown if no module is set.
:::

## Response Structure

Each filter contains:

| Field | Description |
|-------|-------------|
| `id` | Filter ID (use with `parameters(['filterid' => id])` in queries) |
| `name` | Filter name as shown in enterprise |
| `scope` | Filter scope/visibility |

## Using Filters in Queries

Once you know a filter's ID, use it in estate or address queries via the `parameters()` method:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

// Apply filter ID 109 to estate query
$estates = EstateRepository::query()
    ->parameters(['filterid' => 109])
    ->get();
```

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

// Apply filter ID 102 to address query
$addresses = AddressRepository::query()
    ->parameters(['filterid' => 102])
    ->get();
```

## Chunked Processing

```php
FilterRepository::query()
    ->estate()
    ->each(function (array $filters) {
        foreach ($filters as $filter) {
            // Process filter
            echo $filter['id'] . ': ' . $filter['name'];
        }
    });
```

## First Filter

```php
$filter = FilterRepository::query()
    ->address()
    ->first();
```

## Why Use Filters?

Filters allow you to:
- Reuse complex filter logic defined in onOffice enterprise
- Apply business-specific filtering rules consistently
- Avoid duplicating filter conditions in code
- Let non-developers manage filter criteria through the GUI
