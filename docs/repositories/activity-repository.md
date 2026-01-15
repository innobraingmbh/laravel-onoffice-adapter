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

```php
ActivityRepository::query()->create([
    'addressids' => [34],
    'estateid' => 41,
    'actionkind' => 'Email',
    'actiontype' => 'Ausgang',
    'note' => 'Contract sent',
    'advisorylevel' => 'B',      // A-G levels
    'cost' => 2.45,
    'duration' => 3000,          // seconds
]);
```

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
