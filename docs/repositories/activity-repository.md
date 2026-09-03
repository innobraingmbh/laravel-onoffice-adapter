# Activity Repository

Manage agents log / activities. The resource type is `agentslog`.

## Querying Activities

```php
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;

$activities = ActivityRepository::query()->addressIds([34, 35])->get();
$activities = ActivityRepository::query()->estateId(2507)->get();
$activity = ActivityRepository::query()->find(67075);
```

## Selecting & Filtering

```php
$activities = ActivityRepository::query()
    ->select(['Aktionsart', 'Aktionstyp', 'Datum', 'Bemerkung'])
    ->estateId(2507)
    ->where('Aktionsart', 'Email')
    ->whereBetween('created', '2024-01-01', '2024-12-31')
    ->orderByDesc('Datum')
    ->get();
```

::: warning
Cannot filter by: `Benutzer`, `Adress_nr`, `Objekt_nr`, `dauer`. Use `addressIds()` and `estateId()` instead.
:::

## Creating Activities

`addressIds()` and `estateId()` take precedence over `addressids` and `estateid` keys in the data array:

```php
ActivityRepository::query()
    ->addressIds([34])
    ->estateId(41)
    ->create([
        'actionkind' => 'Email',
        'actiontype' => 'Ausgang',
        'note' => 'Contract sent',
        'advisorylevel' => 'B',      // A-G levels
        'cost' => 2.45,
        'duration' => 3000,          // seconds
    ]);
```

::: warning
`Datum` cannot be set on create. The server discards the value and stores the current time without an error.
:::

## Advisory Levels

| Level | Description |
|-------|-------------|
| A | Contract signed |
| B | Written commitment |
| C | Intensive discussion |
| D | Still checking |
| E | Documentation received |
| F | Documentation ordered |
| G | Cancellation (allows `reasoncancellation`) |

## Count & Chunked

```php
$count = ActivityRepository::query()->estateId(10)->count();
ActivityRepository::query()->addressIds([1])->each(fn ($activities) => /* process */);
```
