# Field Repository

Query available fields for different modules. The resource type is `fields`. This helps you discover which fields exist in your onOffice client, as these can vary by configuration.

::: tip
Cache the field configuration response, as the call may take several seconds depending on the language selected.
:::

## Basic Usage

```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

// Query fields for multiple modules
$fields = FieldRepository::query()
    ->withModules(['estate', 'address'])
    ->get();

// Single module
$estateFields = FieldRepository::query()
    ->withModules('estate')
    ->get();

// All fields (no module filter)
$allFields = FieldRepository::query()
    ->get();
```

## Available Modules

The main module IDs are:
- `address` - Address fields
- `estate` - Estate/property fields
- `agentsLog` - Agents log/activity fields
- `calendar` - Appointment fields
- `email` - Email fields
- `file` - File fields
- `news` - News fields
- `intranet` - Intranet fields
- `project` - Project fields
- `task` - Task fields
- `user` - User fields

## Including Labels

```php
$fields = FieldRepository::query()
    ->withModules('estate')
    ->parameters([
        'labels' => true,
        'language' => 'DEU', // ISO 3166-1 alpha-3 (DEU, ENG, etc.)
    ])
    ->get();
```

## Field Filters and Dependencies

```php
$fields = FieldRepository::query()
    ->withModules('estate')
    ->parameters([
        'labels' => true,
        'language' => 'DEU',
        'showfieldfilters' => true, // Show field filters from Administration
        'showfielddependencies' => true, // Show field dependencies
    ])
    ->get();
```

### Filter Values

When `showfieldfilters` is true, the response includes filter information:

| Filter | Description |
|--------|-------------|
| `status` | Status2 value (e.g., `status2obj_aktiv`) |
| `nutzungsarten` | Type of use (e.g., `wohnen`, `gewerbe`) |
| `vermarktungsarten` | Type of commercialization (e.g., `kauf`, `miete`) |
| `immobilienart` | Type of property (e.g., `haus`, `wohnung`) |
| `stammobjekte` | Master property or unit |

## Field Measure Format

```php
$fields = FieldRepository::query()
    ->withModules('estate')
    ->parameters([
        'showFieldMeasureFormat' => true, // Include data type/formatting info
    ])
    ->get();
```

### Measure Format Values

| Format | Description |
|--------|-------------|
| `DATA_TYPE_DATE` | Date (e.g., 10.01.2022) |
| `DATA_TYPE_DATETIME` | Date with time |
| `DATA_TYPE_TIME` | Time only |
| `DATA_TYPE_AREA` | Area (e.g., approx. 100 m²) |
| `DATA_TYPE_DISTANCE` | Distance (e.g., 111.11 km) |
| `DATA_TYPE_LENGTH` | Length, rounded (e.g., 111 m) |
| `DATA_TYPE_DECIMAL_LENGTH` | Length with decimals (e.g., 111.11 m) |
| `DATA_TYPE_VOLUME` | Volume (e.g., 111 m³) |
| `DATA_TYPE_MONETARY` | Price with currency (e.g., 111.11 €) |
| `DATA_TYPE_MONETARY_WITHOUT_CURRENCY` | Price without currency |
| `DATA_TYPE_PERCENTAGE` | Percent (e.g., 111 %) |
| `DATA_TYPE_YEAR` | Year (e.g., 2022) |
| `DATA_TYPE_NUMERIC` | Quantity (e.g., 111.11) |
| `DATA_TYPE_BYTESIZE` | Bytes/file size |
| `DATA_TYPE_ENERGY_REQUIRED` | Energy demand (kWh/(m²*a)) |
| `DATA_TYPE_DURATION` | Duration in minutes |
| `DATA_TYPE_USER` | User name |
| `DATA_TYPE_BOOLEAN` | Boolean (true/false) |
| `DATA_TYPE_NONE` | No formatting |

## Query Specific Fields

```php
$fields = FieldRepository::query()
    ->parameters([
        'fieldList' => ['kaufpreis', 'wohnflaeche', 'objektart'],
        'labels' => true,
        'language' => 'ENG',
    ])
    ->get();
```

## Additional Options

```php
$fields = FieldRepository::query()
    ->withModules('estate')
    ->parameters([
        'showOnlyInactive' => true, // Only inactive fields
        'realDataTypes' => true, // Correct data types for special fields
    ])
    ->get();
```

## Response Structure

Each field in the response contains:

| Property | Description |
|----------|-------------|
| `type` | Field type (`singleselect`, `multiselect`, `freetext`, `float`, etc.) |
| `permittedvalues` | Allowed values for select fields |
| `default` | Default value |
| `filters` | Field filters (if `showfieldfilters` is true) |
| `dependencies` | Field dependencies (if `showfielddependencies` is true) |
| `compoundFields` | Fields that make up a compound field |
| `label` | GUI label in selected language |
| `fieldMeasureFormat` | Data type/formatting (if `showFieldMeasureFormat` is true) |

## Chunks and Single Retrieval

```php
// First field only
$field = FieldRepository::query()
    ->withModules('estate')
    ->first();

// Process in chunks
FieldRepository::query()
    ->withModules(['estate', 'address'])
    ->each(function (array $fields) {
        // Handle chunk of fields
    });
```

::: warning
The fields `Aktionsart` (kind of action) and `Aktionstyp` (action type) require a separate API call via the Settings Repository.
:::
