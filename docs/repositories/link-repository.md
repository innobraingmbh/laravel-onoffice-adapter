# Link Repository

Get URLs for editing records in onOffice enterprise.

## Usage

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Facades\LinkRepository;

$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->recordId(100)
    ->get();

$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Address)
    ->recordId(10505)
    ->get();

$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::AgentsLog)
    ->recordId(67075)
    ->get();
```

## Resource Types

`OnOfficeResourceId::Estate`, `OnOfficeResourceId::Address`, `OnOfficeResourceId::AgentsLog`
