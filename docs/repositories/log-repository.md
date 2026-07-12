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

$count = LogRepository::query()->withModule('estate')->limit(0)->count();

LogRepository::query()->each(fn ($logs) => /* process */);
```

::: warning
Log reads require an admin API user — anyone else gets an access violation (error 156).

The API requires a `listlimit` between 0 and 500, and `cntabsolute` echoes the number of returned rows instead of reporting a true total. `get()` and `each()` set the limit automatically; `first()` needs an explicit `limit(1)`. `count()` only returns the real total with `limit(0)` — any other limit makes it return that limit. `find($id)` sends no `listlimit` and is rejected by the API — filter with `get()` instead.
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
