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

`module()` is an alias for `withResourceId()`.

## Single Records

```php
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->recordId(100)
    ->first();

// find() takes the record id directly, so recordId() is not needed
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::Estate)
    ->find(100);
```

## Resource Types

`OnOfficeResourceId::Estate`, `OnOfficeResourceId::Address`, `OnOfficeResourceId::AgentsLog`

For `AgentsLog` links, `type()` controls whether the agents log entry belongs to an estate or an address (defaults to `Estate`):

```php
$link = LinkRepository::query()
    ->withResourceId(OnOfficeResourceId::AgentsLog)
    ->type(OnOfficeResourceId::Address)
    ->recordId(67075)
    ->get();
```
