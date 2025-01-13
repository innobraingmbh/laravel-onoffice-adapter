# Address Repository

The Address Repository provides comprehensive functionality to manage address records in the onOffice system. It supports various operations including querying, creating, modifying, and searching address records.

## Query Operations

### Basic Queries

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

// Get multiple addresses
$addresses = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->get();

// Get first address
$address = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->first();

// Find specific address by ID
$address = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->find(1);

// Process addresses in chunks
AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->each(function (array $addresses) {
        // Process each chunk of addresses
    });
```

### Search Operations

You can perform searches with filters and sorting:

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

// Search with filters
$addresses = AddressRepository::query()
    ->where('country', 'DE')
    ->search();

// Count addresses
$count = AddressRepository::query()
    ->recordIds([1, 2, 3])
    ->count();
```

::: tip
The count method returns the number of records that match the query from the API. This number might be lower than the actual number of records when queried with get().
:::

## Modification Operations

### Create Address

```php
$address = AddressRepository::query()
    ->create([
        'Vorname' => 'Hans',
        'Nachname' => 'Müller',
        'Land' => 'DE',
        // Additional address fields
    ]);
```

### Modify Address

```php
// Single modification
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->modify(1); // Modifies address with ID 1

// Multiple modifications
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->addModify('Nachname', 'Müller')
    ->modify(1);
```

## Available Methods

### Configuration Methods
- `addCountryIsoCodeType(string $countryIsoCodeType)`: Set the country ISO code type
- `recordIds(array $ids)`: Specify which record IDs to query
- `where(string $field, mixed $value)`: Add filter conditions

### Query Methods
- `get()`: Returns a Collection of all matching addresses
- `first()`: Returns the first matching address or null
- `find(int $id)`: Returns a specific address by ID or null
- `each(callable $callback)`: Processes addresses in chunks using the provided callback
- `count()`: Returns the total count of matching addresses
- `search()`: Performs a search with current filters and returns matching addresses

### Modification Methods
- `create(array $data)`: Creates a new address with the provided data
- `addModify(string $field, mixed $value)`: Adds a field to be modified
- `modify(int $id)`: Applies modifications to the specified address

## Parameters

### Query Parameters
- `countryIsoCodeType`: ISO code for the country (e.g., 'DE', 'US')
- `recordIds`: Array of address record IDs to query
- Additional filter parameters as supported by the onOffice API

### Create/Modify Parameters
Common address fields include:
- `Vorname`: First name
- `Nachname`: Last name
- `Land`: Country
- Additional fields as defined in your onOffice setup

## Returns

- Query operations return either a Collection of addresses or a single address array
- Create operations return the newly created address array
- Modify operations return boolean indicating success
- Count operations return an integer
- Search operations return a Collection of matching addresses

## Exceptions

All methods may throw `OnOfficeException` for API-related errors

## Example Response

```php
[
    'id' => 1,
    'Vorname' => 'Hans',
    'Nachname' => 'Müller',
    'Land' => 'DE',
    // Additional address fields
]
