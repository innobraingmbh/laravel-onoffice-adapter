# Search Criteria Repository

Manage onOffice Search Criteria. Commonly used to store user-defined search conditions.

## Configuration

### Mode
```php
SearchCriteriaRepository::query()
    ->mode('internal'); // can also be 'external' or others
```

### Address ID
```php
SearchCriteriaRepository::query()
    ->addressId(1214);
```
::: warning
`addressId` is **required** when creating a search criteria.
:::

## Operations

### Find
```php
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find(1);

$multiple = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find([1, 2, 3]);
```

### Create
```php
$created = SearchCriteriaRepository::query()
    ->addressId(1214)
    ->create([
        // search criteria data
    ]);
```

- **`find()`**: Returns single or multiple criteria objects.
- **`create()`**: Requires an `addressId`.

Use these methods to manage specialized saved searches in onOffice.