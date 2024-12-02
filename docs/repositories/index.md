# Repositories

Repositories are the primary way to access data from onOffice. This guide provides an overview of the available repositories and their functionalities.

## Available Repositories

1. [Estate Repository](./estate-repository.md)
2. [Setting Repository](./setting-repository.md)
3. [Field Repository](./field-repository.md)
4. [File Repository](./file-repository.md)
5. [Base Repository](./base-repository.md)
6. [Marketplace Repository](./marketplace-repository.md)
7. [Address Repository](./address-repository.md)
8. [Relation Repository](./relation-repository.md)
9. [Search Criteria Repository](./search-criteria-repository.md)
10. [Activity Repository](./activity-repository.md)
11. [Filter Repository](./filter-repository.md)

## Common Usage Pattern

Most repositories follow a similar query pattern:

```php
$estates = EstateRepository::query()
    ->get();

$estate = EstateRepository::query()
    ->first();

$estate = EstateRepository::query()
    ->find(1);

$estate = EstateRepository::query()
    ->where('objektart', 'buero_praxen')
    ->whereIn('estate_id', [1, 2, 3])
    ->get();

$count = EstateRepository::query()
    ->whereLike('objekttitel', '%Einliegerwohnung%')
    ->whereBetween('kaufpreis', 100000, 200000)
    ->count();
 
EstateRepository::query()
    ->each(function (array $estates) {
        // First page
    });
```

## Key Features

- **Querying**: All repositories support basic querying operations like `get()`, `first()`, and `find()`.
- **Pagination**: Most repositories support pagination through the `each()` method.
- **Modification**: Some repositories allow data modification using `create()`, `modify()`, or `delete()` methods.
- **Debugging**: The Base Repository provides debugging tools like `dd()`, `dump()`, and `raw()`.

## Repository-Specific Features

### Estate Repository
- Supports querying and modifying estates
- Handles estate files separately

### Setting Repository
- Provides access to users, regions, imprint, and actions settings

### Field Repository
- Allows querying fields with specific modules

### File Repository
- Supports file uploads, linking, and combined save-and-link operations

### Base Repository
- Offers custom query execution and debugging tools

### Marketplace Repository
- Provides functionality to unlock providers

### Address Repository
- Supports address-specific operations and searching

### Relation Repository
- Handles parent-child relationships between entities

### Search Criteria Repository
- Allows querying search criteria with specific modes

### Activity Repository
- Supports querying and creating activities related to estates and addresses

### Filter Repository
- Provides filtering capabilities for estates and addresses

## Detailed Documentation

For detailed information on each repository, including specific methods and examples, please refer to the individual repository documentation linked above.
