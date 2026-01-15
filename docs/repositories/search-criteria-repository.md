# Search Criteria Repository

Manage onOffice search criteria. The resource type is `searchcriterias`. Search criteria are commonly used to store user-defined search conditions for property matching.

## Modes

The `mode` parameter determines how IDs are interpreted:

| Mode | Description |
|------|-------------|
| `internal` | Returns search criteria for addresses by internal address ID (Datensatznummer) |
| `external` | Returns search criteria for addresses by external customer number (KdNr) |
| `searchcriteria` | Returns search criteria by their own IDs |

## Querying Search Criteria

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

// By search criteria ID
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('searchcriteria')
    ->find(29);

// Multiple search criteria
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('searchcriteria')
    ->find([29, 30, 31]);

// By internal address ID
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find(1214);

// By external customer number
$searchCriteria = SearchCriteriaRepository::query()
    ->mode('external')
    ->find(12345);
```

## Creating Search Criteria

```php
$created = SearchCriteriaRepository::query()
    ->addressId(1214) // Required
    ->create([
        // Search criteria data - depends on your field configuration
        'vermarktungsart' => ['kauf'],
        'objektart' => ['haus', 'wohnung'],
        'range_kaufpreis' => [100000, 500000], // Price range
        'range_wohnflaeche' => [50, 150], // Area range
        'anzahl_zimmer' => 3,
    ]);
```

::: warning
`addressId` is **required** when creating search criteria.
:::

## Response Structure

Search criteria responses contain:

### Field Values

Field values vary depending on the field type:
- **Multiselect/singleselect**: `fieldname: [value_1, ..., value_n]`
- **Range fields (from-to)**: `range_fieldname: [value_1, value_2]`
- **Normal fields**: `fieldname: value`

### Meta Fields (`_meta`)

| Field | Description |
|-------|-------------|
| `internaladdressid` | Internal address ID |
| `externaladdressid` | External address ID (KdNr) |
| `kocriterias` | Array of field names marked as knockout criteria |
| `advisor` | Advisor ID |
| `creator` | Creator ID |
| `creationdate` | Date of creation |
| `editdate` | Date and time of last edit |
| `status` | 1 = active, 0 = inactive |
| `publicnote` | Public note |
| `characteristic` | Characteristic(s) of the search criteria |

### Characteristics

Possible values for the `characteristic` field:
- `manual_saved` - Manually saved
- `automatic_created` - Automatically created
- `address_completion` - From address completion
- `deactivated_by_interested_person` - Deactivated by the person
- `automatic_deactivated` - Automatically deactivated

## Range Field (Location Search)

Search criteria may include a special `Range` field with location data:

```php
// Response may contain:
[
    'range_plz' => '52068',
    'range_ort' => 'Aachen',
    'range_strasse' => 'Example Street',
    'range_hausnummer' => '10',
    'range' => '10', // km radius
    'range_land' => 'DEU',
    'range_breitengrad' => '50.776',
    'range_laengengrad' => '6.085',
]
```

Use these methods to manage specialized saved searches in onOffice.
