# Filter Repository

Retrieve predefined filters from onOffice enterprise.

## Usage

```php
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

$filters = FilterRepository::query()->estate()->get();
$filters = FilterRepository::query()->address()->get();
$filter = FilterRepository::query()->estate()->first();

FilterRepository::query()->estate()->each(function (array $filters) {
    // Process chunk
});
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

Each record has the shape `{id, type, elements}`:

| Field | Description |
|-------|-------------|
| `id` | Filter ID for `parameters(['filterid' => id])` |
| `type` | Always `filter` |
| `elements.name` | Filter name |
| `elements.scope` | Visibility |
| `elements.userId` | Owning user ID, or `null` |
| `elements.groupId` | Group ID |
