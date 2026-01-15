# Log Repository

Read API log entries from onOffice. This is useful for debugging and auditing API activity.

## Querying Logs

```php
use Innobrain\OnOfficeAdapter\Facades\LogRepository;

// Get all log entries
$logs = LogRepository::query()
    ->get();

// First log entry
$log = LogRepository::query()
    ->first();

// Find specific log entry
$log = LogRepository::query()
    ->find(100);
```

## Filtering

```php
// Filter by module
$logs = LogRepository::query()
    ->withModule('estate')
    ->get();

// Filter by action
$logs = LogRepository::query()
    ->withAction('read')
    ->get();

// Filter by user
$logs = LogRepository::query()
    ->withUserId(5)
    ->get();

// Combine filters
$logs = LogRepository::query()
    ->withModule('estate')
    ->withAction('create')
    ->withUserId(5)
    ->get();
```

## Counting

```php
$count = LogRepository::query()
    ->withModule('estate')
    ->count();
```

## Chunked Processing

```php
LogRepository::query()
    ->withModule('estate')
    ->each(function (array $logs) {
        foreach ($logs as $log) {
            // Process log entry
        }
    });
```

## Response Structure

Each log entry typically contains:

| Field | Description |
|-------|-------------|
| `id` | Log entry ID |
| `module` | Module name (estate, address, etc.) |
| `action` | Action performed (read, create, modify, etc.) |
| `user_id` | User who performed the action |
| `timestamp` | When the action occurred |
| `details` | Additional action details |

## Use Cases

The Log Repository is useful for:
- Debugging API integration issues
- Auditing user activity
- Tracking changes to records
- Monitoring API usage
