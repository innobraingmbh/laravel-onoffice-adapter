# Setting Repository

The **SettingRepository** provides multiple sub-builders for different resource sets.

## Users
```php
$users = SettingRepository::users()->get();
$user = SettingRepository::users()->find(1);
```
::: tip
You can chain typical builder methods like `where()`, `each()`, `count()`, etc.
:::

## Regions
```php
$regions = SettingRepository::regions()->get();
```

## Imprint
```php
$imprint = SettingRepository::imprint()->get();
```

## Actions
```php
$actions = SettingRepository::actions()->get();
```

Each method returns a builder tailored to that feature. Refer to onOffice docs for fields and filter support.