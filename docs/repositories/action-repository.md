# Action Repository

Read the action types configured in onOffice (used for activities / agents log entries). The resource type is `actionkindtypes`.

## Querying Action Types

```php
use Innobrain\OnOfficeAdapter\Facades\ActionRepository;

$actionTypes = ActionRepository::query()->get();
```

The endpoint does not support filtering, ordering, or single records. `get()` is the only terminal method.

Each record's `elements` contains `key`, `label`, a `types` map (key => label), and the `default` type.

## Automatic Action Types

The API omits automatic action types by default, and some kinds return an empty `types` map. The full list requires every kind key in the `allowAutomaticTypesForActionKind` parameter, so a complete listing takes two calls:

```php
$kinds = ActionRepository::query()->get();

$complete = ActionRepository::query()
    ->parameters([
        'allowAutomaticTypesForActionKind' => $kinds->pluck('elements.key')->all(),
    ])
    ->get();
```

::: tip
`SettingRepository::actions()` returns the same builder.
:::
