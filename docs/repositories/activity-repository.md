# Activity Repository

Manage activities related to addresses or estates.

## Basic Queries
```php
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;

// Query by address
$activities = ActivityRepository::query()
    ->addressIds([1, 2])
    ->get();

// Query by estate
$estateActivities = ActivityRepository::query()
    ->estateId(10)
    ->get();

// Single activity
$activity = ActivityRepository::query()
    ->find(42);
```

## Chunking & Counting
```php
ActivityRepository::query()
    ->addressIds(1)
    ->each(function (array $activities) {
        // process each chunk
    });

$count = ActivityRepository::query()
    ->estateId(10)
    ->count();
```

## Create an Activity
```php
ActivityRepository::query()
    ->addressIds([1, 2, 3])
    ->create([
        'note' => 'This is a note',
        'datetime' => '2023-05-01 10:00:00'
    ]);
```

- **`estateId()`** or **`addressIds()`**: define the context.
- **`create()`**: Add a new activity record in onOffice.

Use filters (`where()`, `whereLike()`, etc.) for advanced queries as needed.