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
    ->whereNot('reserviert', 1)
    ->whereBetween('wohnflaeche', 50, 150)
    ->whereLike('objekttitel', '%Villa%')
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
    ->addModify(['kaufpreis' => 180000, 'status' => 1])
    ->modify(100);
```

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
```

Categories: `Titelbild`, `Foto`, `Foto_gross`, `Grundriss`, `Lageplan`, `Epass_Skala`, `Panorama`, `Link`, `Film-Link`, `Ogulo-Link`, `Objekt-Link`, `Expose`

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
| `status` | 1 = Active, 2 = Pending, 0 = Archive |
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
