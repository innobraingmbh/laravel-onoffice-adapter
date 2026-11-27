# Setting Repository

Query configuration resources.

```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
```

## Users

```php
$users = SettingRepository::users()->select(['Vorname', 'Nachname'])->get();
$user = SettingRepository::users()->select(['Vorname', 'Nachname'])->find(1);
$users = SettingRepository::users()->where('active', 1)->select(['Name', 'email'])->get();
$count = SettingRepository::users()->count();
```

`SettingRepository::users()` and [`UserRepository::query()`](/repositories/user-repository#user-records) return the same builder. It reads the user records and needs a `select()`; for the ready-made list of users see [`UserRepository::list()`](/repositories/user-repository#user-list).

## Regions

```php
$regions = SettingRepository::regions()->get();
$region = SettingRepository::regions()->first();
$regions = SettingRepository::regions()->parameter('language', 'ENG')->get();

SettingRepository::regions()->each(function (array $regions) {
    // Process chunk
});
```

## Imprint

The imprint is a single record. Read it with `first()`:

```php
$imprint = SettingRepository::imprint()->get();
$imprint = SettingRepository::imprint()->first();
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
| `imprint()` | Yes | Yes | No | No | No | No |
| `actions()` | Yes | No | No | No | No | No |
