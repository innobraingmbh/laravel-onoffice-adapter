# Search Criteria Repository

The Search Criteria Repository provides functionality to manage search criteria in the onOffice API.

## Configuration Methods

### Mode

Sets the mode for the search criteria operations.

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

$repository = SearchCriteriaRepository::query()
    ->mode('internal'); // Default is 'internal'
```

### Address ID

Sets the address ID for creating search criteria.

```php
$repository = SearchCriteriaRepository::query()
    ->addressId(1214);
```

## Operations

### Find

Retrieves search criteria by ID.

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

// Find a single search criteria
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find(1);

// Find multiple search criteria
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find([1, 2, 3]);
```

### Create

Creates a new search criteria. Requires an address ID to be set.

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

$searchCriteria = SearchCriteriaRepository::query()
    ->addressId(1214)
    ->create([
        // Search criteria data
    ]);
```

### Parameters

#### Find
- `id` (int|array): Single ID or array of IDs to find
- `mode` (string): Operation mode, defaults to 'internal'

#### Create
- `addressId` (int): Required. The address ID associated with the search criteria
- `data` (array): The search criteria data to create

### Returns

- Find: Returns an array containing the search criteria data, or null if not found
- Create: Returns an array containing the created search criteria data

### Exceptions

- Throws `OnOfficeQueryException` if address ID is not set when creating search criteria
- Throws `OnOfficeException` for API-related errors
