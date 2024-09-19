# Setting Repository

## Users
```php
use Katalam\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::users()
    ->get();

$setting = SettingRepository::users()
    ->first();

$setting = SettingRepository::users()
    ->find(1);

SettingRepository::users()
    ->each(function (array $settings) {
        // First page
    });
```

## Regions
```php
use Katalam\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::regions()
    ->get();
```

## Imprint
```php
use Katalam\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::imprint()
    ->get();
```

## Actions
```php
use Katalam\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::actions()
    ->get();
```

