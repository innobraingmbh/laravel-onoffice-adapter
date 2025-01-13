# Working with Factories

The **laravel-onoffice-adapter** provides a flexible system for creating and mocking API responses using factories. By leveraging these factories, you can tailor responses to specific scenarios, ensuring robust and isolated tests.

## Overview

Factories let you build consistent onOffice response payloads for testing without manually crafting array structures. You only specify which fields to include, and the factory handles the rest.

### When to Use Factories

- **Unit Tests**: Quickly mock onOffice responses in repository or service tests.
- **Integration Tests**: Simulate real-world payloads from the onOffice API to validate data handling.
- **Regression Tests**: Reproduce specific scenarios or edge cases (such as empty responses, large sets of records, or invalid field data).

## BaseFactory

All factory classes extend the [`BaseFactory`](../../src/Facades/Testing/RecordFactories/BaseFactory.php), which provides:
- An **`id()`** method to set record IDs.
- An **`elements`** array for storing custom data fields.
- A convenient **`data()`** method for bulk assignment.
  
Example usage:

```php
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;

it('creates a custom data set', function () {
    $factory = BaseFactory::make()
        ->id(123)
        ->data([
            'Vorname' => 'Jane',
            'Nachname' => 'Doe',
        ]);

    $result = $factory->toArray();
    
    expect($result['id'])->toBe(123);
    expect($result['elements']['Vorname'])->toBe('Jane');
    expect($result['elements']['Nachname'])->toBe('Doe');
});
```

## Prebuilt Factories

### EstateFactory
Builds `estate` type records. Useful for simulating real estate properties.

```php
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

$estate = EstateFactory::make()
    ->id(1)
    ->data([
        'objektart' => 'haus',
        'kaufpreis' => 275000,
    ]);
```

### AddressFactory
Builds `address` type records. Perfect for mocking address data:

```php
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;

$address = AddressFactory::make()
    ->id(100)
    ->data([
        'Vorname' => 'John',
        'Nachname' => 'Smith',
        'Land' => 'DE',
    ]);
```

### ActivityFactory
Builds `agentslog` type records for testing activity logs related to estates or addresses.

```php
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\ActivityFactory;

$activity = ActivityFactory::make()
    ->id(999)
    ->data([
        'note' => 'Test activity note',
        'datetime' => now()->toDateTimeString(),
    ]);
```

### Additional Factories
- **`UserFactory`**: Mocks user objects (`type = 'user'`)
- **`FileFactory`**: Mocks file objects (`type = 'file'`)
- **`SearchCriteriaFactory`**: Mocks search criteria
- **`FilterFactory`**: Mocks filter definitions
- **`MarketPlaceUnlockProviderFactory`**: Mocks marketplace unlock provider responses
- **`EstatePictureFactory`**: Mocks picture data for an estate

Each factory sets default or required fields internally. You can customize as needed with `data()`.

## Faking Responses in Tests

Factories integrate neatly with the [`fake()`](../../src/Repositories/BaseRepository.php) method on repositories:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

test('it returns the faked estate', function () {
    // Prepare a single estate record
    $fakeEstate = EstateFactory::make()->id(123)->data(['objektart' => 'wohnung']);

    // Fake the repository's response
    EstateRepository::fake([
        // Provide a single page of records with that estate
        EstateRepository::page(recordFactories: [
            $fakeEstate,
        ]),
    ]);

    // Perform the query
    $estates = EstateRepository::query()->get();

    // Assertions
    expect($estates)->toHaveCount(1);
    expect($estates->first()['id'])->toBe(123);
    expect($estates->first()['elements']['objektart'])->toBe('wohnung');
});
```

### Multiple Responses or Paginated Data

Use `page()` to fake multiple pages, or pass in arrays of `OnOfficeResponsePage` objects:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

test('it returns multiple pages', function () {
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

    $response = EstateRepository::query()->get();

    expect($response->count())->toBe(4);
});
```

## Custom Factory Methods

If you want to create more specialized factories, you can either:
1. **Extend** existing factories to tailor them for your domain.
2. **Create brand new** factories that inherit from `BaseFactory`.

```php
namespace Tests\Factories;

use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;

class SpecialEstateFactory extends BaseFactory
{
    public function commercial(): static
    {
        $this->elements['objektart'] = 'buero_praxen';
        
        return $this;
    }

    public function withExtraData(): static
    {
        $this->data([
            'mieteinnahmen_pro_jahr_ist' => 100000,
            'ausstattung' => 'Luxus',
        ]);

        return $this;
    }
}
```

This can then be used exactly like any other factory:

```php
$estate = SpecialEstateFactory::make()
    ->id(200)
    ->commercial()
    ->withExtraData();

EstateRepository::fake([
    EstateRepository::page(recordFactories: [
        $estate,
    ])
]);

// ...
```

## Summary

The factory system in **laravel-onoffice-adapter** offers:

- **Convenience**: Single or multiple prebuilt records without manual array creation.
- **Flexibility**: Support for custom or extended factories.
- **Test Isolation**: Each test can shape the onOffice API response as needed.

For more in-depth testing scenarios, see the [Testing documentation](../getting-started.md#testing).
