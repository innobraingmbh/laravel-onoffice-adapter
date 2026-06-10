# Action Repository

Read the action types configured in onOffice (used for activities / agents log entries). The resource type is `actiontypes`.

## Querying Action Types

```php
use Innobrain\OnOfficeAdapter\Facades\ActionRepository;

$actionTypes = ActionRepository::query()->get();
```

The endpoint does not support filtering, ordering, or fetching single records — `get()` is the only terminal method.

::: tip
`SettingRepository::actions()` returns the same builder — both entry points are equivalent.
:::
