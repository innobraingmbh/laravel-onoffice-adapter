# Activity Repository

Manage agents log / activities related to addresses or estates. The resource type is `agentslog`.

## Querying Activities

```php
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;

// Query by address
$activities = ActivityRepository::query()
    ->addressIds([34, 35])
    ->get();

// Query by estate
$estateActivities = ActivityRepository::query()
    ->estateId(2507)
    ->get();

// Single activity by ID
$activity = ActivityRepository::query()
    ->find(67075);
```

## Selecting Fields

```php
$activities = ActivityRepository::query()
    ->select([
        'Objekt_nr',     // Estate number(s)
        'Adress_nr',     // Address number(s)
        'Aktionsart',    // Kind of action
        'Aktionstyp',    // Action type
        'Datum',         // Date and time
        'created',       // Creation date
        'Benutzer',      // User name
        'Benutzer_nr',   // User ID
        'Bemerkung',     // Note
        'merkmal',       // Characteristic
        'Kosten',        // Costs
        'dauer',         // Duration
        'Beratungsebene', // Advisory level
        'Absagegrund',   // Reason of cancellation
    ])
    ->estateId(2507)
    ->get();
```

## Filtering

```php
// Filter by action kind
$activities = ActivityRepository::query()
    ->estateId(2507)
    ->where('Aktionsart', 'Email')
    ->get();

// Filter by multiple action kinds
$activities = ActivityRepository::query()
    ->whereIn('Aktionsart', ['Email', 'Preisänderung'])
    ->get();

// Filter by date range
$activities = ActivityRepository::query()
    ->whereBetween('created', '2024-01-01 00:00:00', '2024-12-31 15:00:00')
    ->get();

// Filter by advisory level and cancellation reason
$activities = ActivityRepository::query()
    ->estateId(6103)
    ->whereIn('Beratungsebene', ['G Absage'])
    ->whereIn('Absagegrund', ['Alter', 'Lage'])
    ->get();
```

::: warning
The following fields cannot be used in filter: `Benutzer`, `Adress_nr`, `Objekt_nr`, `dauer`. Use the dedicated methods like `addressIds()` and `estateId()` instead.
:::

## Creating Activities

```php
// Basic activity entry
ActivityRepository::query()
    ->create([
        'datetime' => '2024-02-08 11:13:30', // Optional, defaults to now
        'addressids' => [34, 35],
        'estateid' => 41,
        'actionkind' => 'Email',      // Aktionsart (German names)
        'actiontype' => 'Ausgang',    // Aktionstyp (German names)
        'note' => 'Contract documents sent',
    ]);

// Full activity entry with all options
ActivityRepository::query()
    ->create([
        'datetime' => '2024-02-08 11:13:30',
        'addressids' => [34, 35],
        'estateid' => 41,
        'actionkind' => 'Email',
        'actiontype' => 'Ausgang',
        'note' => 'Contract documents sent',
        'origincontact' => 'immobilienscout24_system', // HerkunftKontakt
        'features' => ['indMulti1832Select5686'],      // merkmal
        'cost' => 2.45,
        'duration' => 3000, // seconds
        'advisorylevel' => 'B', // A to G
        'reasoncancellation' => 'Architektur', // Only with level G
        'userid' => 5, // Support user ID
        'taskid' => 10, // Linked task
        'appointmentid' => 20, // Linked appointment
        'projectid' => 30, // Linked project
        'fileids' => [100, 101], // Linked files
    ]);

// Revocation confirmation
ActivityRepository::query()
    ->create([
        'addressids' => [32],
        'actionkind' => 'Widerruf bestätigt',
        'actiontype' => 'Vorzeitiger Beginn',
        'note' => 'Revocation confirmed...',
    ]);
```

::: tip
When both `addressids` and `estateid` are set, entries are created in "Offered till now" tabs under both Addresses >> Property Search and Estates >> Prospective Buyers.
:::

## Advisory Levels (Beratungsebene)

| Level | Description |
|-------|-------------|
| A | Rental/purchase contract signed |
| B | Written rental/purchase commitment |
| C | In intensive discussion |
| D | Interested, but still checking |
| E | Documentation received |
| F | Documentation ordered |
| G | Cancellation (allows `reasoncancellation`) |

## Sorting

```php
$activities = ActivityRepository::query()
    ->estateId(2507)
    ->orderBy('Aktionsart')
    ->orderByDesc('Datum')
    ->get();
```

## Counting

```php
$count = ActivityRepository::query()
    ->estateId(10)
    ->count();
```

## Chunked Processing

```php
ActivityRepository::query()
    ->addressIds([1])
    ->each(function (array $activities) {
        foreach ($activities as $activity) {
            // Process each activity
        }
    });
```

## Common Action Kinds (Aktionsart)

Kind of actions and action types can be found in enterprise under "Extras >> Settings >> Administration >> Action types". German names are used for the API.

Common examples:
- `Email` - Email correspondence
- `Telefon` - Phone call
- `Besichtigung` - Property viewing
- `Preisänderung` - Price change
- `Widerruf bestätigt` - Revocation confirmed

Use filters (`where()`, `whereLike()`, etc.) for advanced queries as needed.
