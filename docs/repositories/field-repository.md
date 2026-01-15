# Field Repository

Query available fields. The resource type is `fields`.

## Basic Usage

```php
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

$fields = FieldRepository::query()->withModules(['estate', 'address'])->get();
$fields = FieldRepository::query()->withModules('estate')->get();
```

## Modules

`address`, `estate`, `agentsLog`, `calendar`, `email`, `file`, `task`, `user`

## Options

```php
$fields = FieldRepository::query()
    ->withModules('estate')
    ->parameters([
        'labels' => true,
        'language' => 'DEU',
        'showfieldfilters' => true,
        'showfielddependencies' => true,
        'showFieldMeasureFormat' => true,
        'fieldList' => ['kaufpreis', 'wohnflaeche'],
    ])
    ->get();
```

## Response Fields

| Property | Description |
|----------|-------------|
| `type` | `singleselect`, `multiselect`, `freetext`, `float`, etc. |
| `permittedvalues` | Allowed values for select fields |
| `label` | GUI label |
| `fieldMeasureFormat` | Data type (`DATA_TYPE_MONETARY`, `DATA_TYPE_AREA`, etc.) |

::: tip
Cache field responses - they can take several seconds.
:::
