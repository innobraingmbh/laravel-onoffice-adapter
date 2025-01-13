# Estate Repository

The Estate Repository provides comprehensive functionality to manage real estate properties in the onOffice system.

## Estates

### Query Operations

#### Basic Queries
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

// Get all estates
$estates = EstateRepository::query()
    ->get();

// Get first estate
$estate = EstateRepository::query()
    ->first();

// Find estate by ID
$estate = EstateRepository::query()
    ->find(1);

// Process estates in chunks
EstateRepository::query()
    ->each(function (array $estates) {
        // Process each chunk of estates
    });
```

#### Search Operations

You can search estates using a search term and additional filters:

```php
// Basic search with filter
EstateRepository::query()
    ->search('Karmelitenstr.')
    ->where('objektart', 'haus')
    ->get();

// Count matching estates
$count = EstateRepository::query()
    ->where('objektart', 'haus')
    ->count();
```

::: tip
The count method returns the number of records that match the query from the API. This number might be lower than the actual number of records when queried with get().
:::

### Modification Operations

#### Create Estate
```php
$estate = EstateRepository::query()
    ->create([
        'objektart' => 'haus',
        // Additional estate properties
    ]);
```

#### Modify Estate
```php
// Single modification
EstateRepository::query()
    ->addModify('objektart', 'haus')
    ->modify(1); // Modifies estate with ID 1

// Multiple modifications
EstateRepository::query()
    ->addModify('objektart', 'haus')
    ->addModify('status', 'active')
    ->modify(1);
```

## Estate Files

The Estate Repository also provides functionality to manage files associated with estates:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

// Get all files for an estate
$files = EstateRepository::files(1)
    ->get();

// Get first file
$file = EstateRepository::files(1)
    ->first();

// Find specific file
$file = EstateRepository::files(1)
    ->find(1);

// Process files in chunks
EstateRepository::files(1)
    ->each(function (array $files) {
        // Process each chunk of files
    });

// Modify file
EstateRepository::files(1)
    ->addModify('file_id', 1)
    ->modify(1);

// Delete file
EstateRepository::files(1)
    ->delete(1);
```

### Parameters

#### Query Methods
- `get()`: Returns a Collection of all matching estates
- `first()`: Returns the first matching estate or null
- `find(int $id)`: Returns a specific estate by ID or null
- `each(callable $callback)`: Processes estates in chunks using the provided callback
- `count()`: Returns the total count of matching estates

#### Modification Methods
- `create(array $data)`: Creates a new estate with the provided data
- `addModify(string $field, mixed $value)`: Adds a field to be modified
- `modify(int $id)`: Applies modifications to the specified estate

#### Search Methods
- `search(string $term)`: Searches estates using the provided term
- `where(string $field, mixed $value)`: Adds a filter condition

### Returns

- Query operations return either a Collection of estates or a single estate array
- Create operations return the newly created estate array
- Modify operations return boolean indicating success
- Count operations return an integer

### Exceptions

All methods may throw `OnOfficeException` for API-related errors
