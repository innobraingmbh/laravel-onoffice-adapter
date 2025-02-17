# Log Repository

Manage log data from onOffice.

## Querying Logs
```php
use Innobrain\OnOfficeAdapter\Facades\LogRepository;

// Basic query
$logs = LogRepository::query()
    ->get();

// First log entry
$log = LogRepository::query()
    ->first();

// Find by ID
$log = LogRepository::query()
    ->find(100);
```

### Counting
```php
$count = LogRepository::query()
    ->where('objektart', 'haus')
    ->count();
```

## Additional Methods
- **`withModule()`**: Filter logs by module.
- **`withAction()`**: Filter logs by action.
- **`withUserId()`**: Filter logs by user ID.
