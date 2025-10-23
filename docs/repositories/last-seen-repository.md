# Last Seen Repository

Manage records last seen data from onOffice.

## Querying Logs
```php
use Innobrain\OnOfficeAdapter\Facades\LastSeenRepository;

// Basic query
$lastSeen = LastSeenRepository::query()
    ->withModule('estate')
    ->get();

// First log entry
$lastSeen = LastSeenRepository::query()
    ->withModule('estate')
    ->first();
```

## Additional Methods
- **`withModule()`**: Filter logs by module.
- **`withUserId()`**: Filter logs by user ID.
