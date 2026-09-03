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
        'realDataTypes' => true,
        'showfieldfilters' => true,
        'showfielddependencies' => true,
        'showFieldMeasureFormat' => true,
        'fieldList' => ['kaufpreis', 'wohnflaeche'],
    ])
    ->get();
```

All options default to off. Without `realDataTypes`, field types are flattened to `text`; a `user` field reports as `text`.

## Response Fields

| Property | Description |
|----------|-------------|
| `type` | `singleselect`, `multiselect`, `freetext`, `float`, etc. |
| `permittedvalues` | Allowed values for select fields |
| `label` | GUI label |
| `fieldMeasureFormat` | Data type (`DATA_TYPE_MONETARY`, `DATA_TYPE_AREA`, etc.) |

::: tip
Records always return internal keys (`objektart: "haus"`, `status2: "status2obj_aktiv"`), never display labels. To show labels, resolve them via a fields call with `'labels' => true` for the module.
:::

::: warning
The metadata is not always complete: mandatory select fields can report empty `permittedvalues`, and some writable fields are missing entirely (e.g. `erledigt` on tasks).
:::

::: tip
Cache field responses. They can take several seconds.
:::
