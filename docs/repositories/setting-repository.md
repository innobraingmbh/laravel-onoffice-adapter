# Setting Repository

Query configuration resources.

```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
```

## Users

```php
$users = SettingRepository::users()->get();
$user = SettingRepository::users()->find(1);
$users = SettingRepository::users()->where('active', 1)->select(['Name', 'email'])->get();
$count = SettingRepository::users()->count();
```

## Regions

```php
$regions = SettingRepository::regions()->get();
$region = SettingRepository::regions()->first();
```

## Imprint

```php
$imprint = SettingRepository::imprint()->get();
```

## Actions

```php
$actions = SettingRepository::actions()->get();
```

::: warning
`Aktionsart` and `Aktionstyp` must be queried here, not via Field Repository.
:::

## Capabilities

| Builder | `get` | `first` | `find` | `each` | `count` | Filtering |
|---------|-------|---------|--------|--------|---------|-----------|
| `users()` | Yes | Yes | Yes | Yes | Yes | Yes |
| `regions()` | Yes | Yes | No | Yes | No | No |
| `imprint()` | Yes | Yes | Yes | No | No | No |
| `actions()` | Yes | No | No | No | No | No |
