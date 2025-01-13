# Address Repository

Manage address records in onOffice:

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

// Basic query
$addresses = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->get();

// Find a single address
$address = AddressRepository::query()
    ->find(1);

// Create a new address
$newAddress = AddressRepository::query()
    ->create([
        'Vorname' => 'Hans',
        'Nachname' => 'Müller',
        'Land' => 'DE',
    ]);
```

## Filtering & Searching
```php
$searchedAddresses = AddressRepository::query()
    ->where('country', 'DE')
    ->search();

$count = AddressRepository::query()
    ->where('Land', 'DE')
    ->count();
```
::: warning
`search()` uses the onOffice search endpoint. Ensure you set relevant filters and parameters.
:::

## Modifying Addresses
```php
// Single field
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->modify(1);

// Multiple fields
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->addModify('Nachname', 'Müller')
    ->modify(2);
```

## Additional Methods
- **`recordIds()`**: Restrict results to specific IDs.
- **`each()`**: Process addresses in chunks for large result sets.
- **`count()`**: Retrieve the total matching count from the API.

For detailed parameters, refer to your onOffice API docs or your existing onOffice configuration.