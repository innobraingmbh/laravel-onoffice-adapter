# Log Repository

Read API log entries for debugging and auditing.

## Usage

```php
use Innobrain\OnOfficeAdapter\Facades\LogRepository;

$logs = LogRepository::query()->get();
$log = LogRepository::query()->find(100);

$logs = LogRepository::query()
    ->withModule('estate')
    ->withAction('create')
    ->withUserId(5)
    ->get();

$count = LogRepository::query()->withModule('estate')->count();

LogRepository::query()->each(fn ($logs) => /* process */);
```

## Response

| Field | Description |
|-------|-------------|
| `id` | Log entry ID |
| `module` | Module name |
| `action` | Action performed |
| `user_id` | User ID |
| `timestamp` | When action occurred |
