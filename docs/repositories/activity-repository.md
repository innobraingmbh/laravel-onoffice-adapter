# Activity Repository

The Activity Repository provides functionality to manage activities in the onOffice system. Activities can be associated with either estates or addresses.

## Query Operations

### Basic Queries

```php
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;

// Query activities by estate
$activities = ActivityRepository::query()
    ->estateId(1)
    ->get();
 
// Query activities by addresses
$activities = ActivityRepository::query()
    ->addressIds([1, 2])
    ->get();

// Get first activity
$activity = ActivityRepository::query()
    ->addressIds(1)
    ->first();

// Find specific activity
$activity = ActivityRepository::query()
    ->find(1);

// Process activities in chunks
ActivityRepository::query()
    ->addressIds([1, 2])
    ->each(function (array $activities) {
        // Process each chunk of activities
    });

// Count activities
$count = ActivityRepository::query()
    ->addressIds([1, 2])
    ->count();
```

::: tip
The count method returns the number of records that match the query from the API. This number might be lower than the actual number of records when queried with get().
:::

## Create Operations

```php
// Create activity for addresses
$activity = ActivityRepository::query()
    ->addressIds([1, 2, 3])
    ->create([
        'note' => 'This is a note',
        'datetime' => '2021-02-08 11:13:30'
    ]);

// Create activity for estate
$activity = ActivityRepository::query()
    ->estateId(1)
    ->create([
        'note' => 'Estate activity note',
        'datetime' => '2021-02-08 11:13:30'
    ]);
```

## Available Methods

### Configuration Methods
- `estateId(int $estateId)`: Set the estate ID for the activity
- `addressIds(int|array $addressIds)`: Set one or more address IDs for the activity
- `where(string $field, mixed $value)`: Add filter conditions

### Query Methods
- `get()`: Returns a Collection of all matching activities
- `first()`: Returns the first matching activity or null
- `find(int $id)`: Returns a specific activity by ID or null
- `each(callable $callback)`: Processes activities in chunks using the provided callback
- `count()`: Returns the total count of matching activities

### Creation Methods
- `create(array $data)`: Creates a new activity with the provided data

## Parameters

### Query Parameters
- `estateId`: ID of the estate to query activities for
- `addressIds`: Single ID or array of address IDs to query activities for
- Additional filter parameters as supported by the onOffice API

### Create Parameters
Required fields:
- `note`: Activity note/description
- `datetime`: Activity date and time
Additional fields as defined in your onOffice setup

## Returns

- Query operations return either a Collection of activities or a single activity array
- Create operations return the newly created activity array
- Count operations return an integer

## Exceptions

All methods may throw `OnOfficeException` for API-related errors
