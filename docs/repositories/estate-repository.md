# Estate Repository

Manage real estate data from onOffice.

## Querying Estates
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

// Basic query
$estates = EstateRepository::query()
    ->get();

// First estate
$estate = EstateRepository::query()
    ->first();

// Find by ID
$estate = EstateRepository::query()
    ->find(100);
```

## Search
```php
// Full text search with filters
$estates = EstateRepository::query()
    ->setInput('Karmelitenstr.') // Set the search term
    ->where('objektart', 'haus')
    ->search();
```

::: tip
The `search()` method calls the onOffice search endpoint, which might differ from direct `get()` queries. Ensure you set the search term using `setInput()`.
:::

## Create & Modify
```php
$newEstate = EstateRepository::query()
    ->create([
        'objektart' => 'haus',
        'kaufpreis' => 150000
    ]);

EstateRepository::query()
    ->addModify('status', 'active')
    ->modify(100); // Modify estate with ID=100
```

## Estate Files
```php
// Retrieve files
$files = EstateRepository::files(100)->get();

// Modify or delete a file
EstateRepository::files(100)->addModify('file_id', 12)->modify(12);
EstateRepository::files(100)->delete(12);
```

### Counting
```php
$count = EstateRepository::query()
    ->where('objektart', 'haus')
    ->count();
```

Learn more about file handling in the [File Repository](./file-repository.md).