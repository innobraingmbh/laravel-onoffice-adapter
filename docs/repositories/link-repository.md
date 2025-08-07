# Link Repository

Manage link data from onOffice.

## Querying Links
```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Facades\LinkRepository;

$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->recordId(1)
    ->get();

$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->recordId(1)
    ->first();

// Find by ID
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->find(1);
```

## Additional Methods
- **`withResourceId()`**: Add resource ID to the query.
- **`recordId()`**: Add record ID to the query.
