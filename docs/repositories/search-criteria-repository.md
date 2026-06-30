# Search Criteria Repository

Manage search criteria. The resource type is `searchcriterias`.

## Modes

| Mode | Description |
|------|-------------|
| `internal` | By internal address ID (Datensatznummer) |
| `external` | By customer number (KdNr) |
| `searchcriteria` | By search criteria ID |

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
The endpoint cannot list search criteria without ids, so `first()` and `each()` are not supported. Read by id: `find()` returns a single record, `recordIds(...)->get()` returns every requested record in one request.
:::

::: tip
`find([29, 30])` issues one request but only returns the first record. Use `recordIds([29, 30])->get()` to get them all.
:::

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

## Response Structure

Field values by type:
- **Multiselect**: `fieldname: [value_1, ..., value_n]`
- **Range**: `range_fieldname: [from, to]`
- **Normal**: `fieldname: value`

Meta fields in `_meta`: `internaladdressid`, `externaladdressid`, `kocriterias`, `status`, `creationdate`, `editdate`
