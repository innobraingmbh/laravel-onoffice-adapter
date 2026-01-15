# Filter Repository

Retrieve predefined filters from onOffice enterprise.

## Usage

```php
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

$filters = FilterRepository::query()->estate()->get();
$filters = FilterRepository::query()->address()->get();
$filter = FilterRepository::query()->estate()->first();
```

::: warning
Must specify module (`estate()` or `address()`) before `get()`, `first()`, or `each()`.
:::

## Using Filters

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

$estates = EstateRepository::query()
    ->parameters(['filterid' => 109])
    ->get();
```

## Response

| Field | Description |
|-------|-------------|
| `id` | Filter ID for `parameters(['filterid' => id])` |
| `name` | Filter name |
| `scope` | Visibility |
