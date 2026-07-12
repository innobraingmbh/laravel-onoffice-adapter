# Estate Repository

Manage real estate data from onOffice. The resource type is `estate`.

## Querying Estates

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

$estates = EstateRepository::query()->get();
$estate = EstateRepository::query()->first();
$estate = EstateRepository::query()->find(100);
```

## Selecting Fields

```php
$estates = EstateRepository::query()
    ->select(['Id', 'kaufpreis', 'objekttitel'])
    ->addSelect('wohnflaeche')
    ->get();
```

## Filtering

Operators: `=`, `>`, `<`, `>=`, `<=`, `!=`, `<>`, `between`, `like`, `not like`, `in`, `not in`.

```php
$estates = EstateRepository::query()
    ->where('status', 1)
    ->where('kaufpreis', '<', 300000)
    ->whereIn('objektart', ['haus', 'wohnung'])
    ->whereNotIn('objekttyp', ['buero'])
    ->whereNot('reserviert', 1)
    ->whereBetween('wohnflaeche', 50, 150)
    ->whereLike('objekttitel', '%Villa%')
    ->whereNotLike('objekttitel', '%Garage%')
    ->get();
```

## Conditional Queries

```php
$estates = EstateRepository::query()
    ->when($minPrice, fn ($q) => $q->where('kaufpreis', '>=', $minPrice))
    ->get();
```

## Sorting & Pagination

```php
$estates = EstateRepository::query()
    ->orderBy('kaufpreis')
    ->orderByDesc('geaendert_am')
    ->offset(100)
    ->limit(50)
    ->pageSize(100) // Records per API call (max 500)
    ->get();
```

## Search

Quick search for estate address, owner, or external estate number:

```php
$estates = EstateRepository::query()
    ->setInput('Karmelitenstr.')
    ->search();
```

## Create & Modify

```php
$estate = EstateRepository::query()
    ->create([
        'objektart' => 'haus',
        'nutzungsart' => 'wohnen',
        'vermarktungsart' => 'kauf',
        'kaufpreis' => 200000,
    ]);

EstateRepository::query()
    ->addModify(['kaufpreis' => 180000, 'status2' => 'status2obj_aktiv'])
    ->modify(100);
```

::: warning
`status` cannot be written: an integer is rejected as a type error, a string returns success without being applied, and on create it is silently ignored. Write `status2` instead (`status2obj_aktiv`, `status2obj_archiviert`) — it cascades into `status`.
:::

::: warning
Modifying `benutzer` (Betreuer) only works while the record still belongs to the API user, and the value is never validated — even a nonexistent ID is stored. Once `benutzer` is anyone else, every further `benutzer` modify returns success and changes nothing.
:::

## Estate Files

```php
$files = EstateRepository::files(100)->get();
$file = EstateRepository::files(100)->find(12);

EstateRepository::files(100)
    ->addModify('Art', 'Titelbild')
    ->modify(12);

EstateRepository::files(100)->delete(12);
```

## Estate Pictures

```php
$pictures = EstateRepository::pictures(100)->get();
$pictures = EstateRepository::pictures([100, 101])->get();

$pictures = EstateRepository::pictures(100)
    ->category(['Titelbild', 'Foto'])
    ->size(800, 600)
    ->language('en')
    ->get();

EstateRepository::pictures([100, 101])->each(function (array $pictures) {
    // Process chunk
});
```

Categories: `Titelbild`, `Foto`, `Foto_gross`, `Grundriss`, `Lageplan`, `Epass_Skala`, `Panorama`, `Link`, `Film-Link`, `Ogulo-Link`, `Objekt-Link`, `Expose`

These are the built-in categories. Customers can define their own, and the API offers no way to enumerate them — custom category names have to be known up front.

## Custom Parameters

```php
$estates = EstateRepository::query()
    ->parameters([
        'georangesearch' => ['country' => 'DEU', 'zip' => '52068', 'radius' => 10],
    ])
    ->get();

$estates = EstateRepository::query()
    ->parameters(['estatelanguage' => 'ENG', 'filterid' => 109])
    ->get();
```

## Counting & Chunked Processing

```php
$count = EstateRepository::query()->where('status', 1)->count();

EstateRepository::query()
    ->where('status', 1)
    ->each(function (array $estates) {
        // Process chunk
    });
```

## Debugging

```php
EstateRepository::query()->dump()->get();  // Dump request
EstateRepository::query()->dd()->get();    // Dump and die
EstateRepository::query()->raw()->get();   // Dump raw array
```

## Middleware

```php
EstateRepository::query()
    ->before(fn ($request) => Log::info('Sending', ['r' => $request]))
    ->after(fn ($response) => Log::info('Received', ['s' => $response->status()]))
    ->get();
```

## Alternative Credentials

```php
$estates = EstateRepository::query()
    ->withCredentials($token, $secret, $apiClaim)
    ->get();
```

## Common Field Names

| Field | Description |
|-------|-------------|
| `status` | 1 = Active, 2 = Pending, 0 = Archive — read-only, write `status2` |
| `status2` | `status2obj_aktiv` / `status2obj_archiviert` — the writable status |
| `objektart` | Property type (haus, wohnung, grundstueck) |
| `nutzungsart` | Type of use (wohnen, gewerbe) |
| `vermarktungsart` | Marketing type (kauf, miete) |
| `kaufpreis` | Purchase price |
| `kaltmiete` / `warmmiete` | Cold/warm rent |
| `wohnflaeche` | Living area |
| `grundstuecksflaeche` | Plot area |
| `anzahl_zimmer` | Number of rooms |
| `verkauft` | Sold/rented (1 = yes) |
| `reserviert` | Reserved (1 = yes) |
| `geaendert_am` | Last modified date |

See also: [File Repository](./file-repository.md)
