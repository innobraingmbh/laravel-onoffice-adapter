# Log Repository

Read API log entries for debugging and auditing.

## Usage

```php
use Innobrain\OnOfficeAdapter\Facades\LogRepository;

$logs = LogRepository::query()->get();
$log = LogRepository::query()->limit(1)->first();

$logs = LogRepository::query()
    ->withModule('estate')
    ->withAction('create')
    ->withUserId(5)
    ->get();

$count = LogRepository::query()->withModule('estate')->limit(1)->count();

LogRepository::query()->each(fn ($logs) => /* process */);
```

::: warning
The API requires a `listlimit` between 0 and 500. `get()` and `each()` set it automatically; `first()` and `count()` need an explicit `limit()`. `find($id)` sends no `listlimit` and is rejected by the API — filter with `get()` instead.
:::

## Response

Each record has the shape `{id, type, elements}`:

| Field | Description |
|-------|-------------|
| `id` | Log entry ID |
| `type` | Always `Log` |
| `elements.module` | Module name |
| `elements.action` | Action performed |
| `elements.userId` | User ID, or `null` |
| `elements.dateTime` | When the action occurred |
| `elements.resourceId` | ID of the affected record |
| `elements.resourceTable` / `elements.resourcePk` | Affected table and primary key |
