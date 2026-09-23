# Search Criteria Repository

Manage search criteria. Reads (`find()`, `get()`, `first()`) use resource type `searchcriterias`; `create()`, `modify()` and `delete()` use `searchcriteria`.

## Modes

| Mode | Description |
|------|-------------|
| `internal` | By internal address ID (Datensatznummer) |
| `external` | By customer number (KdNr) |
| `searchcriteria` | By search criteria ID |
| `filter` | By filter, without ids |

## Querying

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

// A single record
$criteria = SearchCriteriaRepository::query()->mode('searchcriteria')->find(29);
$criteria = SearchCriteriaRepository::query()->mode('internal')->find(1214);

// Several records, in one request
$criteria = SearchCriteriaRepository::query()->mode('searchcriteria')->recordIds([29, 30])->get();
```

::: warning
Outside the filter mode, search criteria are read by id: `find()` returns a single record, `recordIds(...)->get()` returns every requested record in one request.
:::

::: tip
`find([29, 30])` issues one request but only returns the first record. Use `recordIds([29, 30])->get()` to get them all.
:::

## Filtering

The filter mode lists search criteria without ids. It filters and sorts by the `_meta` fields (`internaladdressid`, `advisor`, `status`, `creationdate`, `editdate`), not by search criteria fields such as `vermarktungsart` — use [matching](#matching) for those.

```php
$criteria = SearchCriteriaRepository::query()
    ->mode('filter')
    ->where('advisor', 12)
    ->whereBetween('creationdate', '2024-01-01', '2024-12-31')
    ->orderByDesc('creationdate') // Required
    ->get();

$newest = SearchCriteriaRepository::query()
    ->mode('filter')
    ->whereIn('internaladdressid', [1214, 1215])
    ->orderByDesc('creationdate')
    ->first();
```

::: warning
The filter mode requires `orderBy()` and sorts by a single column. It does not report a total, so `count()` and `paginate()` are not supported; use `offset()` and `limit()` to read a window of the list.
:::

## Fields

The fields a customer has configured as search criteria, grouped by category. Each field carries its label, type, whether it is a range (`rangefield`) or knockout (`ko`) criterion, and the permitted `values` of select fields.

```php
$categories = SearchCriteriaRepository::fields()
    ->parameter('language', 'ENG')
    ->parameter('additionalTranslations', true)
    ->get();

$categories->first()['elements']['name'];   // "Preise"
$categories->first()['elements']['fields']; // [['id' => 'kaufpreis', 'rangefield' => 'true', ...], ...]
```

## Creating

```php
$created = SearchCriteriaRepository::query()
    ->addressId(1214) // Required
    ->create([
        'vermarktungsart' => ['kauf'],
        'objektart' => ['haus', 'wohnung'],
        'range_kaufpreis' => [100000, 500000],
    ]);
```

## Modifying

Only the fields you pass are changed. Range fields take the `__von` / `__bis` suffixes.

```php
SearchCriteriaRepository::query()
    ->addModify('kaufpreis__bis', '500000')
    ->addModify([
        'sys_ko' => ['kaufpreis', 'objektart'],
        'krit_bemerkung_oeffentlich' => 'Only south-facing',
    ])
    ->modify(29);
```

## Deleting

```php
SearchCriteriaRepository::query()->delete(29);
```

## Matching

Find the search criteria that match a set of field values, e.g. the ones a listing would satisfy. Each record holds the search criteria `Id` and the linked address (`adresse`).

```php
$matches = SearchCriteriaRepository::matching(['vermarktungsart' => 'kauf', 'range_plz' => '52074'])
    ->select(['Id', 'adresse', 'kaufpreis__bis']) // or ->outputAll()
    ->groupByAddress(false) // default: one search criteria per address
    ->orderByDesc('kaufpreis__bis')
    ->get();

$total = SearchCriteriaRepository::matching(['vermarktungsart' => 'kauf'])->count();

$page = SearchCriteriaRepository::matching(['vermarktungsart' => 'kauf'])
    ->when($zip, fn ($query) => $query->searchData(['range_plz' => $zip]))
    ->paginate(perPage: 25);
```

::: warning
The endpoint cannot order by `Id`; order by a search criteria field instead.
:::

## Response Structure

Field values by type:
- **Multiselect**: `fieldname: [value_1, ..., value_n]`
- **Range**: `range_fieldname: [from, to]`
- **Normal**: `fieldname: value`

Meta fields in `_meta`: `internaladdressid`, `externaladdressid`, `kocriterias`, `status`, `creationdate`, `editdate`
