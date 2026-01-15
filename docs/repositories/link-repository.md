# Link Repository

Retrieve URLs of detail views for editing estates, addresses, and activities in onOffice enterprise.

## Usage

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Facades\LinkRepository;

// Get edit link for an estate
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->recordId(100)
    ->get();

// Get edit link for an address
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Address)
    ->recordId(10505)
    ->get();

// Get edit link for an activity/agents log entry
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::AgentsLog)
    ->recordId(67075)
    ->get();
```

## Available Resource Types

| Resource ID | Description |
|-------------|-------------|
| `OnOfficeResourceId::Estate` | Estate/property detail view |
| `OnOfficeResourceId::Address` | Address detail view |
| `OnOfficeResourceId::AgentsLog` | Activity/agents log detail view |

## Response

The response contains URLs that can be used to:
- Open records directly in onOffice enterprise
- Create links for external systems to access onOffice data
- Provide quick access to specific records

## Alternative Methods

```php
// First link
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->recordId(100)
    ->first();

// Find by record ID (shorthand)
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->find(100);
```

## Use Cases

The Link Repository is useful for:
- Creating "Edit in onOffice" buttons in external applications
- Generating deep links for CRM integrations
- Building dashboards that link back to onOffice records
