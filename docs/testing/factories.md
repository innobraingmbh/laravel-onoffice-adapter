# Working with Factories

Factories let you build consistent onOffice response payloads for tests, so you don’t have to craft arrays manually.

## Overview
Each factory extends `BaseFactory`, providing:
- **`id()`** to set record IDs (int or string).
- **`data()`** to set custom fields.

Example:
```php
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

$fakeEstate = EstateFactory::make()
    ->id(123)
    ->data([
        'objektart' => 'haus',
        'kaufpreis' => 275000,
    ]);
```

## Repository Stubbing
Use the `fake()` method on a repository to supply factory-based responses:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

EstateRepository::fake([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1),
    ]),
]);

$response = EstateRepository::query()->get();
expect($response->count())->toBe(1);
```

## Multiple Pages or Sequencing
```php
EstateRepository::fake([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1),
        EstateFactory::make()->id(2),
    ]),
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(3),
        EstateFactory::make()->id(4),
    ]),
]);
```

## Other Factories
- **`AddressFactory`**
- **`ActivityFactory`**
- **`RelationFactory`**
- **`FileFactory`**
- **`FilterFactory`**
- **`SearchCriteriaFactory`**
- … and more

Each factory supports a similar interface:
```php
FactoryClass::make()->id(999)->data([...]);
```

For deeper customization, extend `BaseFactory` in your test suite with project-specific fields or methods.