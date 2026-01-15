# Estate Repository

Manage real estate data from onOffice. The resource type is `estate`.

## Querying Estates

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

// Basic query - returns all estates
$estates = EstateRepository::query()
    ->get();

// First estate
$estate = EstateRepository::query()
    ->first();

// Find by ID (resourceid)
$estate = EstateRepository::query()
    ->find(100);
```

## Filtering

Use the `where()` method to filter estates. Available operators: `=`, `>`, `<`, `>=`, `<=`, `!=`, `<>`, `between`, `like`, `not like`, `in`, `not in`.

```php
// Active estates with purchase price < 300000
$estates = EstateRepository::query()
    ->where('status', 1) // 1 = Active, 2 = Pending, 0 = Archive
    ->where('kaufpreis', '<', 300000)
    ->get();

// Filter by property type using IN operator
$estates = EstateRepository::query()
    ->whereIn('objektart', ['haus', 'wohnung'])
    ->get();

// Filter by date range
$estates = EstateRepository::query()
    ->where('geaendert_am', '>', '2024-01-01 00:00')
    ->get();

// Using whereBetween
$estates = EstateRepository::query()
    ->whereBetween('kaufpreis', 100000, 500000)
    ->get();
```

## Selecting Fields

Specify which fields to retrieve using `select()`:

```php
$estates = EstateRepository::query()
    ->select(['Id', 'kaufpreis', 'lage', 'objekttitel', 'wohnflaeche'])
    ->where('status', 1)
    ->get();

// Marketing status fields
$estates = EstateRepository::query()
    ->select(['Id', 'verkauft', 'reserviert'])
    // verkauft=1: Sold/Rented, reserviert=1: Reserved, both=0: Open
    ->get();
```

## Sorting

```php
$estates = EstateRepository::query()
    ->orderBy('kaufpreis')
    ->orderByDesc('warmmiete')
    ->get();
```

## Search

The `search()` method uses the onOffice quick search endpoint for estate address, owner, and external estate number:

```php
$estates = EstateRepository::query()
    ->setInput('Karmelitenstr.') // Search term
    ->where('objektart', 'haus')
    ->search();
```

## Create & Modify

```php
// Create a new estate
$newEstate = EstateRepository::query()
    ->create([
        'objektart' => 'haus',
        'nutzungsart' => 'wohnen',
        'vermarktungsart' => 'kauf',
        'objekttyp' => 'einfamilienhaus',
        'plz' => 52068,
        'ort' => 'Aachen',
        'land' => 'DEU', // ISO 3166-1 alpha-3
        'kaufpreis' => 200000,
        'wohnflaeche' => 75,
        'anzahl_zimmer' => 3,
    ]);

// Modify an existing estate
EstateRepository::query()
    ->addModify('kaufpreis', 180000)
    ->addModify('status', 1)
    ->modify(100); // Estate ID
```

::: tip
System fields like `erstellt_am`, `erstellt_von`, `provisionsbetrag` are set automatically.
:::

## Estate Files

```php
// Retrieve files
$files = EstateRepository::files(100)->get();

// Modify file metadata
EstateRepository::files(100)
    ->addModify('Art', 'Titelbild')
    ->modify(12); // File ID

// Delete a file
EstateRepository::files(100)->delete(12);
```

## Estate Pictures

```php
// Retrieve pictures published on homepage
$pictures = EstateRepository::pictures(100)->get();

// For multiple estates
$pictures = EstateRepository::pictures([100, 101, 102])->get();
```

## Custom Parameters

For API features not directly exposed by the builder, use `parameters()`:

```php
// Geo range search
$estates = EstateRepository::query()
    ->parameters([
        'georangesearch' => [
            'country' => 'DEU',
            'zip' => '52068',
            'radius' => 10
        ]
    ])
    ->get();

// Multilingual estates
$estates = EstateRepository::query()
    ->parameters([
        'estatelanguage' => 'ENG',
        'addestatelanguage' => true,
        'addMainLangId' => true,
    ])
    ->find(4457);

// Using a predefined filter from enterprise
$estates = EstateRepository::query()
    ->parameters(['filterid' => 109])
    ->get();
```

## Counting

```php
$count = EstateRepository::query()
    ->where('objektart', 'haus')
    ->where('status', 1)
    ->count();
```

## Chunked Processing

For large datasets:

```php
EstateRepository::query()
    ->where('status', 1)
    ->each(function (array $estates) {
        foreach ($estates as $estate) {
            // Process each estate
        }
    });
```

## Common Field Names

| Field | Description |
|-------|-------------|
| `status` | 1 = Active, 2 = Pending, 0 = Archive |
| `objektart` | Property type (haus, wohnung, grundstueck, etc.) |
| `nutzungsart` | Type of use (wohnen, gewerbe) |
| `vermarktungsart` | Marketing type (kauf, miete) |
| `kaufpreis` | Purchase price |
| `kaltmiete` / `warmmiete` | Cold/warm rent |
| `wohnflaeche` | Living area |
| `grundstuecksflaeche` | Plot area |
| `anzahl_zimmer` | Number of rooms |
| `verkauft` | Sold/rented status (1 = yes) |
| `reserviert` | Reserved status (1 = yes) |
| `geaendert_am` | Last modified date |
| `veroeffentlichen` | Publish on homepage |

Learn more about file handling in the [File Repository](./file-repository.md).
